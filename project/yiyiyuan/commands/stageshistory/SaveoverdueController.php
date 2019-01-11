<?php

namespace app\commands\stageshistory;

use app\commonapi\Logger;
use app\models\news\OverdueLoan;
use app\models\news\User_loan;
use yii\console\Controller;

/**
 * 同步借款表逾期数据到逾期账单表 只执行一次
 */
set_time_limit(0);
ini_set('memory_limit', '-1');

class SaveoverdueController extends Controller {

    public $error   = 0;
    public $success = 0;

    // 命令行入口文件
    public function actionIndex() {
        $month = 40;
        for ($j = 0; $j < 40; $j++) {
            $startDate = date('Y-m-d H:i:00', strtotime("-$month month"));
            $month --;
            $endDate   = date('Y-m-d H:i:00', strtotime("-$month month"));
            $where     = [
                'AND',
                ['>=', 'end_date', $startDate],
                ['<', 'end_date', $endDate],
                ['status' => ['11', '12', '13']],
                ['business_type' => ['1', '4']],
//                ['is_push' => ['0', '-1']]
            ];
            $total     = User_loan::find()->where($where)->count();
            //获取总条数
            $limit     = 1000;
            $pages     = ceil($total / $limit);

            $this->log("\n" . date('Y-m-d H:i:s') . "......................");
            $this->log("共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
            for ($i = 0; $i < $pages; $i++) {
                $loanlist = User_loan::find()->where($where)->offset($i * $limit)->limit($limit)->all();
                // 没有数据时结束
                if (empty($loanlist)) {
                    break;
                }
                $res = $this->addOverdue($loanlist);
            }
        }
       echo '成功'.$this -> success .'失败'.$this -> error;
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

    private function addOverdue($userloaninfo) {
        if (empty($userloaninfo)) {
            exit();
        }
        foreach ($userloaninfo as $key => $val) {
            $overdue = OverdueLoan::find()->where(['loan_id' => $val['loan_id']])->all();
            if (!empty($overdue)) {
                continue;
            }
            $latefee =  $val['chase_amount'] - $val['interest_fee'] - $val['amount'];
            if($val['is_calculation'] != 1) {
                $latefee = $latefee - $val['withdraw_fee'];
            }
            $latefee  = $latefee > 0 ? $latefee: 0 ;
            $data                   = [];
            $data['loan_id']        = isset($val['loan_id']) ? $val['loan_id'] : '';
            $data['user_id']        = isset($val['user_id']) ? $val['user_id'] : '';
            $data['bill_id']        = '';
            $data['bank_id']        = isset($val['bank_id']) ? $val['bank_id'] : '';
            $data['loan_no']        = isset($val['loan_no']) ? $val['loan_no'] : '';
            $data['amount']         = isset($val['amount']) ? $val['amount'] : '';
            $data['days']           = isset($val['days']) ? $val['days'] : '';
            $data['desc']           = isset($val['desc']) ? $val['desc'] : '';
            $data['start_date']     = isset($val['start_date']) ? $val['start_date'] : '';
            $data['end_date']       = isset($val['end_date']) ? $val['end_date'] : '';
            $data['loan_status']    = $val['status'] == 11 ? 11 : 12;
            $data['interest_fee']   = isset($val['interest_fee']) ? $val['interest_fee'] : '';
            $data['contract']       = isset($val['contract']) ? $val['contract'] : '';
            $data['contract_url']   = isset($val['contract_url']) ? $val['contract_url'] : '';
            $data['late_fee']       = $latefee;
            $data['withdraw_fee']   = isset($val['withdraw_fee']) ? $val['withdraw_fee'] : '';
            $data['chase_amount']   = $val['chase_amount'];
            $data['is_push']        = $val['is_push'];
            $data['business_type']  = isset($val['business_type']) ? $val['business_type'] : '';
            $data['source']         = isset($val['source']) ? $val['source'] : '';
            $data['is_calculation'] = isset($val['is_calculation']) ? $val['is_calculation'] : '';
            $data['version']        = 0;
            $data['create_time']    = date('Y-m-d H:i:s');
            $res                    = (new OverdueLoan)->saveOverdue($data);
            if (!$res) {
                $this->error += 1;
                continue;
            } else {
                $this->success += 1;
            }
        }
    }

}
