<?php

namespace app\commands;

use app\models\dev\User;
use app\models\news\GoodsBill;
use app\models\news\OverdueLoan;
use app\commonapi\Logger;
use app\models\news\User_loan;
use Yii;
use yii\console\Controller;

/**
 * 同步分期账单数据到逾期账单表  每天一次0点执行
 * Class SycharoverdueController
 * @package app\commands
 * 测试  D:\phpStudy\php\php-7.0.12-nts\php.exe D:\work\yiyiyuanOnline\yii sycharoverdue
 */
class SycharoverdueController extends BaseController {

    public function actionIndex() {
        $success   = 0;
        $fail      = 0;
        $limit     = 1000;
        $time      = time();
        $startTime = date('Y-m-d 00:00:00');
        $endTime   = date('Y-m-d 23:59:59');
        $where     = [
            'and',
            ['>=', 'end_time', $startTime],
            ['<=', 'end_time', $endTime],
            ['=', 'bill_status', 9],
        ];
        $overdue   = GoodsBill::find()->where($where);
        $total     = $overdue->count();
        $pages     = ceil($total / $limit);
        $id        = 0;
        for ($i = 0; $i < $pages; $i++) {
            $where[]     = ['>', 'id', $id];
            $overdueInfo = GoodsBill::find()->where($where)->indexBy('id')->orderBy('id')->limit($limit)->all();
            if (empty($overdueInfo)) {
                exit();
            }
            $id  = max(array_keys($overdueInfo));
            $res = $this->addOverdue($overdueInfo);
            if ($res) {
                $this->log("\n共{$total}条数据:每次处理{$limit},成功{$success}次,失败{$fail}次\n");
            }
        }
    }

    private function addOverdue($overdue, $success = 0, $fail = 0) {
        if (empty($overdue)) {
            exit();
        }
        foreach ($overdue as $key => $val) {
            $status = 12;
            $goodsbill = GoodsBill::find()->where(['bill_id' => $val['bill_id']])->one();
            if(!empty($goodsbill) && $goodsbill['bill_status'] == 9){
                $goodsstatus = $val->saveGoodsBill(['bill_status' => $status]);
            }
            $overdue = OverdueLoan::find()->where(['bill_id' => $val['bill_id']])->all();
            if (!empty($overdue)) {
                continue;
            }
            $data                   = [];
            $data['loan_id']        = isset($val['loan_id']) ? $val['loan_id'] : '';
            $data['user_id']        = isset($val['user_id']) ? $val['user_id'] : '';
            $data['bill_id']        = isset($val['bill_id']) ? $val['bill_id'] : '';
            $data['bank_id']        = isset($val->userloan['bank_id']) ? $val->userloan['bank_id'] : 0;
            $data['loan_no']        = isset($val->userloan['loan_no']) ? $val->userloan['loan_no'] : '';
            $data['amount']         = isset($val['goods_amount']) ? $val['goods_amount'] : ''; //总金额
            $data['current_amount'] = isset($val['current_amount']) ? $val['current_amount'] : ''; //当期
            $data['days']           = isset($val['days']) ? $val['days'] : '';
            $data['desc']           = isset($val->userloan['desc']) ? $val->userloan['desc'] : '';
            $data['start_date']     = isset($val['start_time']) ? $val['start_time'] : '';
            $data['end_date']       = isset($val['end_time']) ? $val['end_time'] : '';
            $data['loan_status']    = $status;
            $data['interest_fee']   = isset($val['interest']) ? $val['interest'] : ''; //TODO interest_fee
            $data['contract']       = isset($val->userloan['contract']) ? $val->userloan['contract'] : '';
            $data['contract_url']   = isset($val->userloan['contract_url']) ? $val->userloan['contract_url'] : '';
            $data['late_fee']       = 0;
            $data['withdraw_fee']   = isset($val->userloan['withdraw_fee']) ? $val->userloan['withdraw_fee'] : '';
            $data['chase_amount']   = 0;
            $data['is_push']        = 0;
            $data['business_type']  = isset($val->userloan['business_type']) ? $val->userloan['business_type'] : '';
            $data['source']         = isset($val->userloan['source']) ? $val->userloan['source'] : '';
            $data['is_calculation'] = isset($val->userloan['is_calculation']) ? $val->userloan['is_calculation'] : '';
            $data['version']        = 0;
            $data['create_time']    = date('Y-m-d H:i:s');
            $res                    = (new OverdueLoan)->saveOverdue($data);
            if (!$res) {
                $fail++;
                Logger::errorLog(print_r(array(" $val->loan_id 逾期账单同步失败"), true), 'addoverdue', 'addoverdueloan');
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
