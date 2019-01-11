<?php

namespace app\commands\claim;

use app\commands\BaseController;
use app\commonapi\ApiSign;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\Exchange;
use app\models\news\Payaccount;
use app\models\news\User_loan;
use app\models\news\User_remit_list;

/**
 * 还款。到期结束债权加入yi_claim_notify表
 */

/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */
class EndloanoverController extends BaseController {

    // 到期刚兑
    public function actionIndex() {
        $start_date = date('Y-m-d H:i:00', strtotime('-10 minutes'));
        $end_date = date('Y-m-d 00:00:00', strtotime("+1 days"));
        $where = [
            'AND',
            [User_loan::tableName() . '.status' => 9],
            [User_loan::tableName() . '.end_date' => $end_date],
            [Exchange::tableName() . '.type' => 1],
            [Exchange::tableName() . '.exchange' => 0],
        ];
        echo $total = User_loan::find()->joinWith('exchange', 'TRUE', 'LEFT JOIN')->where($where)->count();
        $limit = 100;
        $pages = ceil($total / $limit);

        $this->log("\n" . date('Y-m-d H:i:s') . "......................");
        $this->log("共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
        for ($i = 0; $i < $pages; $i++) {
            $loan_list = User_loan::find()->joinWith('exchange', 'TRUE', 'LEFT JOIN')->where($where)->offset($i * $limit)->limit($limit)->all();
            if (empty($loan_list)) {
                break;
            }
            $this->log("处理范围" . ($i * $limit) . ' -- ' . ($i * $limit + $limit));
            $data = [];
            foreach ($loan_list as $key => $value) {
                $result = $this->doLoan($value->loan_id);
            }
        }
//        $this->log("\n金额:总额{$total_amount}元,失败{$total_fail}元");
    }

    private function doLoan($loan_id) {
        $loan = User_loan::findOne($loan_id);
        $data = [
            'type' => 2,
            'loan_id' => $loan_id,
            'over_time' => date('Y-m-d H:i:s'),
            'withdraw_fee' => $loan->is_calculation == 1 ? $loan->interest_fee : $loan->withdraw_fee + $loan->interest_fee,
        ];
        $signData = (new ApiSign)->signData($data);
        $signData['_sign'] = base64_encode($signData['_sign']);
        if (SYSTEM_ENV == 'prod') {
            $url = "http://eros.yaoyuefu.com/api/loan/loanoverreally";
        } elseif (SYSTEM_ENV == 'dev') {
            $url = "http://testeros.yaoyuefu.com/api/loan/loanoverreally";
        } else {
            $url = "http://testeros.yaoyuefu.com/api/loan/loanoverreally";
        }
        $result = Http::interface_post($url, $signData);
        Logger::dayLog('testClaim/sendloanover', $result);
        $res = json_decode($result, TRUE);
        if ($res) {
            $data_msg = json_decode($res['data'], TRUE);
            print_r($data_msg);
            if ($data_msg['rsp_code'] == '0000') {
                $exchange = Exchange::find()->where(['loan_id' => $loan_id])->one();
                if (empty($exchange)) {
                    return TRUE;
                }
                $result = $exchange->update_list(['exchange' => 1]);
                if (!$result) {
                    Logger::dayLog('claim/inputnotify', $loan_id . '到期刚兑失败');
                }
            }
        }
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
