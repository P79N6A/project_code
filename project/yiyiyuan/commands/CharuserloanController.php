<?php

namespace app\commands;

use app\models\dev\User;
use app\models\news\GoodsBill;
use app\models\news\OverdueLoan;
use app\models\news\User_loan;
use app\commonapi\Logger;
use Yii;
use yii\console\Controller;

/**
 * 同步借款表逾期数据到逾期账单表  每天一次0点执行
 * Class CharuserloanController
 * @package app\commands
 * 测试  D:\phpStudy\php\php-7.0.12-nts\php.exe D:\work\yiyiyuanOnline\yii charuserloan
 */
class CharuserloanController extends BaseController {

    public function actionIndex() {
        $success   = 0;
        $fail      = 0;
        $limit     = 1000;
        $time      = time();
        $startTime = date('Y-m-d 00:00:00');
        $endTime   = date('Y-m-d 23:59:59');
        $where     = [
            'and',
            ['>=', 'end_date', $startTime],
            ['<=', 'end_date', $endTime],
            ['in', 'status', [9, 11]],
        ];
        
        $minId = User_loan::find()->select('min(loan_id) as loan_id')->where($where)->asArray()->one();
        
        $userModel = User_loan::find()->where($where);
        $total     = $userModel->count();
        $pages     = ceil($total / $limit);
        $id        = isset($minId['loan_id']) && $minId['loan_id'] - 1  > 0 ? $minId['loan_id']-1 : 0;
        for ($i = 0; $i < $pages; $i ++) {
            $where[]      = ['>', 'loan_id', $id];
            $userloaninfo = User_loan::find()->where($where)->indexBy('loan_id')->orderBy('loan_id')->limit($limit)->all();
            if (empty($userloaninfo)) {
                exit();
            }
            $id  = max(array_keys($userloaninfo));
            $res = $this->addOverdue($userloaninfo);
            if ($res) {
                $this->log("\n共{$total}条数据:每次处理{$limit},成功{$success}次,失败{$fail}次\n");
            } else {
                continue;
            }
        }
    }

    private function addOverdue($userloaninfo, $success = 0, $fail = 0) {
        if (empty($userloaninfo)) {
            exit();
        }
        foreach ($userloaninfo as $key => $val) {
            $loanModel  = (new User_loan())->getById($val['loan_id']);
            if(!empty($loanModel) && $loanModel->status == 9){
                $loanstatus = $loanModel->changeStatus(12);
            }
            $overdue = OverdueLoan::find()->where(['loan_id' => $val['loan_id']])->all();
            if (!empty($overdue) || in_array($val->business_type,[5,6])) {
                continue;
            }
            $interest_fee = $loanModel->getInterestFee();
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
            $data['loan_status']    = $val['status'] ==11 ? 11 : 12;
            $data['interest_fee']   = !empty($interest_fee) ? $interest_fee : '';
            $data['contract']       = isset($val['contract']) ? $val['contract'] : '';
            $data['contract_url']   = isset($val['contract_url']) ? $val['contract_url'] : '';
            $data['late_fee']       = 0;
            $data['withdraw_fee']   = isset($val['withdraw_fee']) ? $val['withdraw_fee'] : '';
            $data['chase_amount']   = 0;
            $data['is_push']        = 0;
            $data['business_type']  = isset($val['business_type']) ? $val['business_type'] : '';
            $data['source']         = isset($val['source']) ? $val['source'] : '';
            $data['is_calculation'] = isset($val['is_calculation']) ? $val['is_calculation'] : '';
            $data['version']        = 0;
            $data['create_time']    = date('Y-m-d H:i:s');
            $res                    = (new OverdueLoan)->saveOverdue($data);
            if (!$res) {
                $fail++;
                Logger::errorLog(print_r(array(" $val->loan_id 逾期账单同步失败"), true), 'addoverdue', 'addoverdueloan');
                continue;
            } else {
                $success++;
            }
        }
        return true;
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
