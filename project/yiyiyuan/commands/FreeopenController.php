<?php

/**
 * 存管提现
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii freeopen > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii freeopen
 */

namespace app\commands;

use app\commonapi\Apidepository;
use app\commonapi\Logger;
use app\models\news\Open_temp;
use app\models\news\Payaccount;
use app\models\news\User;
use app\models\news\User_bank;
use Yii;
use yii\console\Controller;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class FreeopenController extends Controller {

    public function actionIndex($start_time, $end_time) {
        $succsee = 0;
        $error = 0;
        $hasopen = 0;
        $limit = 500;
        if (empty($start_time) || empty($end_time)) {
            exit;
        }
//        $start_time = '2017-11-01 00:00:00';
//        $end_time = '2017-11-01 23:59:59';
        $where = [
            'AND',
            [User::tableName() . ".status" => 3],
            ['BETWEEN', User::tableName() . '.create_time', $start_time, $end_time],
            //存在用户储蓄卡绑卡信息
            [User_bank::tableName() . ".status" => 1],
            [User_bank::tableName() . ".type" => 0],
        ];
        $total = User::find()->select(User::tableName() . '.user_id')->distinct()->joinWith('userbank', true, 'LEFT JOIN')->where($where)->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $userInfo = User::find()->select(User::tableName() . '.user_id')->distinct()->joinWith('userbank', true, 'LEFT JOIN')->where($where)->offset($i * $limit)->limit($limit)->all();
            if (empty($userInfo)) {
                $this->log('NO DATA');
                exit;
            }
            foreach ($userInfo as $key => $value) {
                $result = $this->freeopen($value);
                if ($result === true) {
                    $succsee++;
                } elseif ($result === false) {
                    $error++;
                } elseif ($result == '0000') {
                    $hasopen++;
                }
            }
        }
        $msg = $start_time . ' to ' . $end_time . ' total: ' . $total . ' success: ' . $succsee . ' error: ' . $error . ' hasopen: ' . $hasopen;
        Logger::dayLog('freeopenRes', 'freeopenRes', $msg);
        $this->log($msg);
    }

    private function freeopen($v) {
        $value = User::findOne($v->user_id);
        //获取用户最新绑定的储蓄卡
        $bankInfo = User_bank::find()->where(['user_id' => $value->user_id, 'type' => 0, 'status' => 1])->orderBy("create_time desc")->one();
        if (!$bankInfo) {
            Logger::dayLog('freeopen', 'freeopen', $value->user_id, '用户储蓄卡信息为空');
            return false;
        }
        $idNo = $value->identity;
        $name = $value->realname;
        $mobile = $value->mobile;
        $cardNo = $bankInfo->card;
        if (!$idNo || !$name || !$mobile || !$cardNo) {
            Logger::dayLog('freeopen', 'freeopen', $value->user_id, '用户开户必须的信息不全');
            return false;
        }

        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002', //交易渠道
            'idType' => '01', //01-身份证
            'idNo' => $idNo, //证件号码
            'name' => $name,
            'mobile' => $mobile,
            'cardNo' => $cardNo,
            'acctUse' => '00000',
            'from' => '1',
        ];

        $isOpen = (new Payaccount())->getPaystatusByUserId($value->user_id, 2, 1);
        if ($isOpen) {
            //用户有过开户行为
            if ($isOpen->activate_result != 1) {
                //开户未成功
                $ret_open = $apiDep->freeopen($params);
                if (!$ret_open) {
                    (new Open_temp())->addList(['user_id' => $value->user_id, 'type' => 2, 'user_create_time' => $value->create_time]);
                    Logger::dayLog('freeopen', 'freeopen', $value->user_id . '->用户开户失败');
                    return false;
                }
                $condition['activate_result'] = 1;
                $condition['accountId'] = $ret_open["accountId"];
                $condition['card'] = (string) $bankInfo->id;
                $upRes = $isOpen->update_list($condition);
                (new Open_temp())->addList(['user_id' => $value->user_id, 'type' => 1, 'user_create_time' => $value->create_time]);
                if (!$upRes) {
                    Logger::dayLog('freeopen', 'freeopen', $value->user_id . '->更新用户状态为成功：失败');
                    return false;
                }
                $userBankModel = new User_bank();
                $userBankModel->updateDefaultBank($value->user_id, $bankInfo->id);
                return true;
            } else {
                return '0000';
            }
        } else {
            //用户没有开过户
            $payAccount = new Payaccount();
            $ret_open = $apiDep->freeopen($params);
            $condition = [
                "user_id" => $value->user_id,
                'type' => 2,
                'step' => 1,
            ];
            if (!$ret_open) {
                Logger::dayLog('freeopen', 'freeopen', $value->user_id . '->用户开户失败');
                $condition['activate_result'] = 0;
                $addRes = $payAccount->add_list($condition);
                (new Open_temp())->addList(['user_id' => $value->user_id, 'type' => 2, 'user_create_time' => $value->create_time]);
                if (!$addRes) {
                    Logger::dayLog('freeopen', 'freeopen', $value->user_id . '->添加用户开户失败数据：失败');
                }
                return false;
            }
            $condition['activate_result'] = 1;
            $condition['accountId'] = $ret_open["accountId"];
            $condition['card'] = (string) $bankInfo->id;
            $addRes = $payAccount->add_list($condition);
            (new Open_temp())->addList(['user_id' => $value->user_id, 'type' => 1, 'user_create_time' => $value->create_time]);
            if (!$addRes) {
                Logger::dayLog('freeopen', 'freeopen', $value->user_id . '->添加用户开户成功数据：失败');
                return false;
            }
            $userBankModel = new User_bank();
            $userBankModel->updateDefaultBank($value->user_id, $bankInfo->id);
            return true;
        }
    }

    // 输出信息
    private function log($message) {
        echo $message . "\n";
    }

}
