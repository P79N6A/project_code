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
 * 2个小时，通道转换通知 TODO 删除
 */

/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */
class SendloanoverController extends BaseController {

    // 命令行入口文件
    public function actionIndex() {
        $start_date = date('Y-m-d H:i:00', strtotime('-10 minutes'));
        $end_date = date('Y-m-d H:i:00', strtotime('-5 minutes'));
        $where = [
            'AND',
            ['type' => 2],
            ['BETWEEN', 'last_modify_time', $start_date, $end_date],
        ];
        echo $total = Exchange::find()->where($where)->count();
        $limit = 100;
        $pages = ceil($total / $limit);

        $this->log("\n" . date('Y-m-d H:i:s') . "......................");
        $this->log("共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
        for ($i = 0; $i < $pages; $i++) {
            $loan_list = Exchange::find()->where($where)->offset($i * $limit)->limit($limit)->all();
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

    private function doLoan($loan_id, $type = 3) {
        $loan = User_loan::findOne($loan_id);
        $payaccount = Payaccount::find()->where(['type' => 2, 'step' => 2, 'user_id' => $loan->user_id])->one();
        $password = !empty($payaccount) && $payaccount->activate_result == 1 ? 1 : 0;
        $remit = User_remit_list::find()->where(['loan_id' => $loan_id])->one();
        $data = [
            'type' => $type,
            'loan_id' => $loan_id,
            'over_time' => date('Y-m-d H:i:s'),
            'withdraw_fee' => 0,
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
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
