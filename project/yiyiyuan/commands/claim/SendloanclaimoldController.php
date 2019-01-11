<?php

namespace app\commands\claim;

use app\commands\BaseController;
use app\commonapi\ApiSign;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\Difference_amount;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;

/**
 * 债权推送
 */

/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */
class SendloanclaimController extends BaseController {

    // 命令行入口文件
    public function actionIndex() {
        $start_date = date('Y-m-d H:i:00', strtotime('-10 minutes'));
        $end_date = date('Y-m-d H:i:00', strtotime('-5 minutes'));
        $where = [
            'AND',
            [User_loan::tableName() . '.status' => 9],
            ['!=', User_loan_extend::tableName() . ".fund", 10],
            ['BETWEEN', User_loan::tableName() . '.last_modify_time', $start_date, $end_date],
        ];
        $total = User_loan::find()->joinWith('loanextend', 'TRUE', 'LEFT JOIN')->where($where)->count();
        $limit = 100;
        $pages = ceil($total / $limit);

        $this->log("\n" . date('Y-m-d H:i:s') . "......................");
        $this->log("共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
        for ($i = 0; $i < $pages; $i++) {
            $loan_list = User_loan::find()->joinWith('loanextend', 'TRUE', 'LEFT JOIN')->where($where)->offset($i * $limit)->limit($limit)->all();
            if (empty($loan_list)) {
                break;
            }
            $this->log("处理范围" . ($i * $limit) . ' -- ' . ($i * $limit + $limit));
            foreach ($loan_list as $key => $value) {
                $userModel = new User_loan();
                $claim_result = $userModel->sendClaim($value->loan_id);
                if (!$claim_result) {
                    Logger::errorLog(print_r(array($value->loan_id . '债权推送失败')), $type);
                    continue;
                }
            }
        }
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
