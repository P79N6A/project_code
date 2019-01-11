<?php

namespace app\commands\stageshistory;

use app\commonapi\Logger;
use app\models\news\GoodsBill;
use app\models\news\OverdueLoan;
use app\models\news\OverdueLoanFlows;
use app\models\news\User_loan;
use Yii;
use yii\console\Controller;

/**
 * 逾期罚息
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */
class GetloanoverController extends Controller {
    /**
     * 计算逾期罚息（包括分期）
     * @copyright (c) 2017 11 17
     * @author zhangtian <zhangtian@xianhuahua.com>
     */
    public function actionIndex() {
        $limit              = 1000;
        $error_num          = $id                 = 0;
        $overdue_loan_model = new OverdueLoan();
        $overdue_where      = [
            "AND",
            ['loan_status' => [11, 12, 13]],
            ['is_push' => [0, -1, -2 ,1]],
        ];
        $total              = $overdue_loan_model->getTotalNum($overdue_where);   //获取所有逾期订单
        $pages              = ceil($total / $limit);
        //分页获取逾期账单计算逾期金额
        for ($i = 1; $i <= $pages; $i++) {
            //找出每一页的订单
            $overdue_where[] = ['>', 'id', $id];
            $overdue_loans   = $overdue_loan_model->getOverdueLoans($overdue_where, $limit);
            if (empty($overdue_loans)) {          //没有数据退出
                $this->log('没有数据！');
                die;
            }
            $ids = array_keys($overdue_loans);
            $id  = max($ids);
            OverdueLoan::updateAll(['is_push' => -1], ['id' => $ids]);
            foreach ($overdue_loans as $loan) {
                $loan_id = $loan['loan_id'];
                if (in_array($loan['business_type'], [1, 4])) {            //未分期订单
                    $chase_amount = $this->getNotRes($loan);
                    if (!$chase_amount) {
                        continue;
                    }
                }else{
                    continue;
                }
                //将信息记录到overdue_loan_flows
                $overdue_loan_flows = (new OverdueLoanFlows())->addOverdueLoanData($loan, $chase_amount);
                if (!$overdue_loan_flows) {
                    Logger::errorLog($loan_id . '-'  . "罚息流水记录失败", 'getLoanOver', 'crontab');
                }
            }
            OverdueLoan::updateAll(['is_push' => 1], ['is_push' => -2]);
            OverdueLoan::updateAll(['is_push' => 0], ['is_push' => -1]);
        }
    }

    public function getNotRes($loan_this) {
        if (!$loan_this) {
            Logger::errorLog($loan_this['loan_id'] . '-'  . "无订单信息", 'getLoanOver', 'crontab');
            return FALSE;
        }
        $chase_amount = $loan_this->getUserLoanChaseAmount();
        if ($chase_amount / 10 >= $loan_this->amount) {         //判断最新一次计算后的金额是否要计息
            $chase_amount = $loan_this->amount * 10;
            $is_push_loan = OverdueLoan::find()->where(['loan_id' => $loan_this['loan_id']]) -> one();
            $is_push_loan->update_userLoan(['is_push' => -2]);
        }
        if ($loan_this['is_calculation'] == 1) {
            $late_fee = $chase_amount - $loan_this['amount'] - $loan_this['interest_fee']; //计算滞纳金费用
        } else {
            $late_fee = $chase_amount - $loan_this['amount'] - $loan_this['interest_fee'] - $loan_this['withdraw_fee']; //计算滞纳金费用
        }
        $late_fee = $late_fee > 0 ? $late_fee : 0 ;
        $result = (new OverdueLoan())->saveChaseAmount($loan_this['loan_id'] , '', $chase_amount, $late_fee);
        if (!$result) {
            $error_num++;
            Logger::errorLog($loan_this['loan_id']  . '-'  . "罚息失败", 'getLoanOver', 'crontab');
            return FALSE;
        }
        return $chase_amount;
    }
    
     // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }
}
