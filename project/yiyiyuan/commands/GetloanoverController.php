<?php

namespace app\commands;

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
    public function actionStages() {
        $limit = 1000;
        $error_num = $id = 0;
        $overdue_loan_model = new OverdueLoan();
        $overdue_where = [
            "AND",
            ['loan_status' => [11, 12, 13]],
            ['is_push' => [0, -1, -2]],
        ];
        $total = $overdue_loan_model->getTotalNum($overdue_where);   //获取所有逾期订单
        $pages = ceil($total / $limit);
        //分页获取逾期账单计算逾期金额
        for ($i = 1; $i <= $pages; $i++) {
            //找出每一页的订单
            $overdue_where[] = ['>', 'id', $id];
            $overdue_loans = $overdue_loan_model->getOverdueLoans($overdue_where, $limit);
            if (empty($overdue_loans)) {          //没有数据退出
                $this->log('没有数据！');
                die;
            }
            $ids = array_keys($overdue_loans);
            $id = max($ids);
            OverdueLoan::updateAll(['is_push' => -1], ['id' => $ids]);
            foreach ($overdue_loans as $loan) {
                $loan_id = $loan['loan_id'];
                $bill_id = $loan['bill_id'];
                if (in_array($loan['business_type'], [1, 4, 9,10])) {            //未分期订单
                    $chase_amount = $this->getNotRes($loan, $bill_id);
                    if (!$chase_amount) {
                        continue;
                    }
                } elseif (in_array($loan['business_type'], [5, 6])) {       //分期订单
                    $chase_amount = $this->getRes($loan_id, $bill_id, $loan);
                    if (!$chase_amount) {
                        continue;
                    }
                }
                //将信息记录到overdue_loan_flows
                $overdue_loan_flows = (new OverdueLoanFlows())->addOverdueLoanData($loan, $chase_amount);
                if (!$overdue_loan_flows) {
                    Logger::errorLog($loan_id . '-' . $bill_id . "罚息流水记录失败", 'getLoanOver', 'crontab');
                }
            }
            OverdueLoan::updateAll(['is_push' => 1], ['is_push' => -2]);
            OverdueLoan::updateAll(['is_push' => 0], ['is_push' => -1]);
        }
    }

    public function getNotRes($loan_this, $bill_id) {
//        $loan_this = User_loan::findOne($loan_id);
        if (!$loan_this) {
            Logger::errorLog($loan_this['loan_id'] . '-' . $bill_id . "无订单信息", 'getLoanOver', 'crontab');
            return FALSE;
        }
        $chase_amount = $loan_this->getUserLoanChaseAmount();
        if ($chase_amount / 3 >= $loan_this->amount) {         //判断最新一次计算后的金额是否要计息
            $chase_amount = $loan_this->amount * 3;
            $is_push_loan = OverdueLoan::find()->where(['loan_id' => $loan_this['loan_id']])->one();
            $is_push_loan->update_userLoan(['is_push' => -2]);
        }
        if ($loan_this['is_calculation'] == 1) {
            $late_fee = $chase_amount - $loan_this['amount'] - $loan_this['interest_fee']; //计算滞纳金费用
        } else {
            $late_fee = $chase_amount - $loan_this['amount'] - $loan_this['interest_fee'] - $loan_this['withdraw_fee']; //计算滞纳金费用
        }
        $result = (new OverdueLoan())->saveChaseAmount($loan_this['loan_id'], $bill_id, $chase_amount, $late_fee);
        if (!$result) {
            $error_num++;
            Logger::errorLog($loan_this['loan_id'] . '-' . $bill_id . "罚息失败", 'getLoanOver', 'crontab');
            return FALSE;
        }
        return $chase_amount;
    }

    public function getRes($loan_id, $bill_id, $loan) {
        $bill_info = $loan->goodsbill;
        if ($bill_info['bill_status'] != 12) {      //bill_status = 12 为逾期状态
            Logger::errorLog($loan_id . '-' . $bill_id . "数据异常！", 'getLoanOver', 'crontab');
            return FALSE;
        }
        //分期订单逾期金额
        $charseAmount = $loan['chase_amount'] > 0 ? $loan['chase_amount'] : $loan['current_amount'];
        $chase_amount = (new GoodsBill())->getOverdueAmount($bill_info, $charseAmount);
        $is_push = '';
        if ($chase_amount / 3 > $loan->amount) {
            $chase_amount = $loan->amount * 3;
            $is_push = -2;
        }
        $late_fee = $chase_amount - $bill_info['principal'] - $bill_info['interest'];
        $result = (new OverdueLoan())->saveChaseAmount($loan_id, $bill_id, $chase_amount, $late_fee, $is_push);
        if (!$result) {
            $error_num++;
            Logger::errorLog($loan_id . '-' . $bill_id . "罚息失败", 'getLoanOver', 'crontab');
            return FALSE;
        }
        return $chase_amount;
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

    // 命令行入口文件
    public function actionIndex() {
        $error_num = 0;
        $month = 13;
        for ($j = 0; $j < 13; $j++) {
            $startDate = date('Y-m-d H:i:00', strtotime("-$month month"));
            $month --;
            $endDate = date('Y-m-d H:i:00', strtotime("-$month month"));
            $where = [
                'AND',
                ['>=', 'end_date', $startDate],
                ['<', 'end_date', $endDate],
                ['status' => ['11', '12', '13']],
                ['business_type' => ['1', '4']],
                ['is_push' => ['0', '-1']]
            ];
            $total = User_loan::find()->where($where)->count();
            //获取总条数
            $limit = 1000;
            $pages = ceil($total / $limit);

            $this->log("\n" . date('Y-m-d H:i:s') . "......................");
            $this->log("共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
            for ($i = 0; $i < $pages; $i++) {
                $select = array('loan_id', 'user_id', 'end_date', 'interest_fee', 'withdraw_fee', 'current_amount', 'last_modify_time', 'chase_amount', 'amount', 'business_type', 'status', 'is_calculation');
                $loanlist = User_loan::find()->select($select)->where($where)->offset($i * $limit)->limit($limit)->all();
                // 没有数据时结束
                if (empty($loanlist)) {
                    break;
                }

                $this->log("处理范围" . ($i * $limit) . ' -- ' . ($i * $limit + $limit));

                foreach ($loanlist as $key => $value) {
                    if ($value->chase_amount / 10 >= $value->amount) {
                        $value->update_userLoan(array('is_push' => -1));
                    } else {
                        $transaction = Yii::$app->db->beginTransaction();
                        $chase_amount = $value->chaseAmount();
                        $is_push = 0;
                        if ($chase_amount / 10 >= $value->amount) {
                            $chase_amount = $value->amount * 10;
                            $is_push = -1;
                        }
                        $loan_this = User_loan::findOne($value->loan_id);
                        $result = $loan_this->saveChaseAmount($chase_amount, $is_push);

                        if (!$result) {
                            $transaction->rollBack();
                            $error_num++;
                            Logger::errorLog(print_r(array("$value->loan_id 罚息更新失败-- $chase_amount"), true), 'getLoanOver', 'crontab');
                            continue;
                        }
                        $transaction->commit();
                    }
                }
            }
        }
        if ($error_num > 0) {
            Logger::errorLog(print_r(array("$error_num 条借款罚息更新失败"), true), 'getLoanOverNum', 'crontab');
        }
        User_loan::updateAll(['is_push' => 1], ['is_push' => -1]);
    }

}
