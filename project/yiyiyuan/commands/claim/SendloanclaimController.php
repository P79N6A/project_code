<?php

/**
 * 指定指定时间范围内的债匹
 *   linux : /data/wwwroot/yiyiyuan/yii sendloanclaim
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii sendloanclaim
 */

namespace app\commands\claim;

use app\commonapi\Logger;
use app\models\news\Cm_loans;
use app\models\news\User_loan;
use yii\helpers\ArrayHelper;
use app\commands\BaseController;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SendloanclaimController extends BaseController {

    private $limit = 500;

    public function actionIndex() {
        $where = [
            'status' => 0,
        ];
        $total = Cm_loans::find()->where($where)->count();
        $pages = ceil($total / $this->limit);
        $succ = 0;
        $err = 0;
        $lock_error = 0;
        $loanModel = new User_loan();
        for ($i = 0; $i < $pages; $i++) {
            $data = Cm_loans::find()->where($where)->limit($this->limit)->all();
            if (empty($data)) {
                break;
            }
            $ids = ArrayHelper::getColumn($data, 'id');
            $loan_ids = ArrayHelper::getColumn($data, 'loan_id');
            Cm_loans::updateAll(['status' => 1], ['id' => $ids]);
            $res = $loanModel->sendClaim($loan_ids);
            if (empty($res)) {
//                Cm_loans::updateAll(['status' => 3], ['id' => $ids]);
                $lock_error = $lock_error + count($ids);
                Logger::dayLog('dep/sendloanclaim_error', '批量债匹失败:loan_id', $loan_ids);
                continue;
            }
            foreach ($res as $item) {
                $cm_loan = Cm_loans::find()->where(['loan_id' => $item['loan_id']])->one();
                if ($item['rsp_code'] == '0000' || $item['rsp_code'] == '1014') {//1014是订单重复，代表也推送成功了
                    if (isset($item['loan_id']) && !empty($item['loan_id'])) {
                        $res = $cm_loan->saveSucc();
                        if (!$res) {
                            Logger::dayLog('dep/cm_loans', "loan_id：" . $item['loan_id'] . '---更新成功状态失败');
                            continue;
                        }
                        $succ++;
                    }
                } elseif ($this->getFails($item['rsp_code'])) {//明确债匹的错误码
                    $res = $cm_loan->saveFail();
                    if (!$res) {
                        Logger::dayLog('dep/cm_loans', "loan_id：" . $item['loan_id'] . '---更新失败状态失败');
                        continue;
                    }
                    Logger::dayLog('dep/sendloanclaim_error', "loan_id：" . $item['loan_id'] . '---债匹失败');
                    $err++;
                    continue;
                } else {
                    $lock_error++;
                    continue;
                }
            }
        }
        Logger::dayLog('dep', '总数：' . $total . '；成功：' . $succ . '；失败：' . $err);
        exit('总数：' . $total . '；成功：' . $succ . '；失败：' . $err);
    }

    /**
     * 明确错误的错误码
     * @param type $code
     */
    private function getFails($code) {
        $codes = [
            '1001', //'数据无法解析'
            '1002', //'loan_id 不能为空
            '1003', //'money 不能为空
            '1004', //'fee 不能为空
            '1005', //'over_time 不能为空
            '1006', //'withdraw_fee 参数必填
            '1007', //'fee 不能为空
            '1008', //'repay_day 不能为空
            '1009', //'username 不能为空
            '1010', //'mobile 不能为空
            '1011', //'identity 不能为空
            '1012', //'tag_type 不能为空
            '9991', //'数据参数错误'
            '9992', //'数据不合法!'
            '9993', //'数据格式不正确'
            '1013', //'数据保存失败'
        ];
        return in_array($code, $codes);
    }

}
