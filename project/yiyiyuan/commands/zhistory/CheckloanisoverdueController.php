<?php

/**
 * 判断借款是否逾期，如果逾期则直接改为逾期状态
 */

namespace app\commands;

use app\models\dev\User_loan;
use Yii;
use yii\console\Controller;

set_time_limit(0);
ini_set('memory_limit', '-1');

class CheckloanisoverdueController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $total = 0;
        $sucess = 0;
        $date = date('Y-m-d H:i:s');
        //获取总条数
        $total = User_loan::find()->where(['status' => array('9')])->andWhere(['business_type' => [1, 4, 5, 6]])->andWhere("end_date <= '$date'")->count();
        $limit = 100;
        $pages = ceil($total / $limit);

        $this->log("\n" . date('Y-m-d H:i:s') . "......................");
        $this->log("共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");

        for ($i = 0; $i < $pages; $i++) {
            $select = array('loan_id', 'user_id', 'end_date', 'interest_fee', 'withdraw_fee', 'current_amount', 'last_modify_time', 'amount', 'business_type', 'status');
            $loanlist = User_loan::find()->select($select)->where(['status' => array('9')])->andWhere(['business_type' => [1, 4, 5, 6]])->andWhere("end_date <= '$date'")->limit($limit)->all();
            // 没有数据时结束
            if (empty($loanlist)) {
                break;
            }

            $this->log("处理范围" . ($i * $limit) . ' -- ' . ($i * $limit + $limit));

            foreach ($loanlist as $key => $value) {

                $value = $value->changeStatus(12);

                // 计算成功数
                if ($value) {
                    $sucess ++;
                }
            }
        }

        $fails = $total - $sucess;
        $this->log("\n处理结果:成功{$sucess}条, 失败{$fails}条");
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
