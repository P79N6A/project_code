<?php

namespace app\commands\renew;

/**
 * 驳回订单
 * linux : sudo -u www /data/wwwroot/yiyiyuan/yii mall/orderreject
 * windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii mall/orderreject
 */
use app\commands\BaseController;
use app\commonapi\ApiSmsShop;
use app\commonapi\Logger;
use app\commonapi\policy\policyApi;
use app\models\news\Renew_amount;
use app\models\news\User;
use app\models\news\WarnMessageList;
use app\models\news\User_loan;
use yii\helpers\ArrayHelper;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class RenewamountController extends BaseController {

    public function actionIndex() {
        $successNum = 0;
        $errNum = 0;
        $start_time = date('Y-m-d 00:00:00', strtotime("+1 days"));
        $where = [
            'AND',
            [Renew_amount::tableName() . '.start_time' => $start_time],
            [Renew_amount::tableName() . '.type' => 1],
            [User_loan::tableName() . '.status' => [9, 11]],
            ['>', User_loan::tableName() . '.create_time', date('Y-m-d 00:00:00', strtotime("-3 month"))],
        ];
        $total = Renew_amount::find()->joinWith('loan', 'TRUE', 'LEFT JOIN')->where($where)->count();
        $limit = 200;
        $pages = ceil($total / $limit);
        Logger::dayLog('renewamount', $total);
        for ($i = 0; $i < $pages; $i++) {
            $datas = Renew_amount::find()->joinWith('loan', 'TRUE', 'LEFT JOIN')->where($where)->offset($i * $limit)->limit($limit)->all();
            Logger::dayLog('renewamount', $i * $limit, $limit, ArrayHelper::getColumn($datas, 'loan_id'));
            if (empty($datas)) {
                break;
            }
            foreach ($datas as $key => $value) {
                $result = $this->todo($value);
                Logger::dayLog('renewamount', $value->loan_id, $result);
                if ($result) {
                    $successNum++;
                } else {
                    $errNum++;
                }
            }
            Logger::dayLog('renewamount', 'success' . $successNum, 'error' . $errNum);
        }
        echo date('Y-m-d H:i:s') . ' all: ' . $total . ' success: ' . $successNum . ' err: ' . $errNum;
    }

    private function todo($renewVal) {
        if (!$renewVal || !$renewVal->user_id || !$renewVal->loan_id) {
            return false;
        }
        $policyApi = new policyApi();
        $postdata = [
            'aid' => 1,
            'user_id' => $renewVal->user_id,
            'loan_id' => $renewVal->loan_id,
            'query_time' => date('Y-m-d H:i:s'),
        ];
        $ret = $policyApi->overbefore($postdata);
        $result = json_decode($ret, true);

        if (!empty($result) && $result['rsp_code'] == '0000' && !empty($result['user_id']) && !empty($result['loan_id']) && !empty($result['rollover_money']) || !empty($result['rollover_fee'])) {
            $condition = [
                'renew' => $result['rollover_fee'],
                'renew_fee' => $result['rollover_money'],
            ];
            if ($result['result'] == 3) {
                $condition['mark'] = 3;
            }
            $upRet = $renewVal->updateRenew($condition);
            if (!$upRet) {
                Logger::dayLog('policyApiError', print_r(array($renewVal->user_id => '数据更新失败'), true));
                return false;
            }
            Logger::dayLog('policyApiSuccsee', print_r(array($renewVal->user_id => $result), true));
            //商城借款，展期待支付营销短信
            if($result['result'] != 3 && $renewVal->mark == 1){
                $o_user_loan = (new User_loan())->getById($renewVal->loan_id);
                if(!empty($o_user_loan) && $o_user_loan->business_type == 9){
                    $date = $this->getDate($renewVal->end_time);
                    $date_arr['days'] = '';
                    $date_arr['time'] = '';
                    if($date !=null){
                         $date_arr['days'] = intval(substr($date,0,strpos($date, '天'))) + 1; 
                         $date_arr['time'] = $date;
                    }
                    $o_user = (new User())->getById($renewVal->user_id);
                    if(!empty($date) && !empty($o_user)){
                        (new ApiSmsShop())->sendRenewalWait($o_user->mobile, $date_arr);
                        (new WarnMessageList())->saveWarnMessage($o_user_loan,1,13,$date_arr);
                    }
                }
            }
            return true;
        }
        //调用接口失败，记录日志
        Logger::dayLog('policyApiError', print_r(array($renewVal->user_id => $result), true));
        return false;
    }

    private function getDate($endTime){
        $endTime = strtotime($endTime);
        $nowTime = strtotime(date('Y-m-d H:i:s'));
        if ($nowTime > $endTime) {
            echo $nowTime;
            echo '<br>';
            echo $endTime;
            return NULL;
        }
        //计算天数
        $timediff = $endTime - $nowTime;
        $days = intval($timediff / 86400);
        //计算小时数
        $remain = $timediff % 86400;
        $hours = intval($remain / 3600);
        //计算分钟数
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        //计算秒数
        $secs = $remain % 60;
//        $res = str_pad($days, 2, "0", STR_PAD_LEFT).'天'.str_pad($hours, 2, "0", STR_PAD_LEFT).':'.str_pad($mins, 2, "0", STR_PAD_LEFT).':'.str_pad($secs, 2, "0", STR_PAD_LEFT);
        $res = $days.'天'.str_pad($hours, 2, "0", STR_PAD_LEFT).':'.str_pad($mins, 2, "0", STR_PAD_LEFT).':'.str_pad($secs, 2, "0", STR_PAD_LEFT);
        return $res;
    }
}
