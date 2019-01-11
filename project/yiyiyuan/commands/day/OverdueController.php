<?php

namespace app\commands\day;

/**
 * 
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii remit/remit runByChannel
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii remit/remit runByChannel 1 #1新浪; 2:
 */
use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\day\Overdue_loan_flows_guide;
use app\models\day\Overdue_loan_guide;
use app\models\day\Sms_guide;
use app\models\day\User_loan_guide;

class OverdueController extends BaseController {

    public $limit = 500;
    public $success = 0;
    public $error = 0;

    public function actionIndex() {
        $month = 40;
        $startDate = date('Y-m-d 00:00:00'); //, strtotime("-$month month"));

        $where = [
            'AND',
            ['=', 'end_date', $startDate],
//            ['<', 'end_date', $endDate],
            ['status' => ['9', '11']],
            ['business_type' => [7]],
//                ['is_push' => ['0', '-1']]
        ];
        $total = User_loan_guide::find()->where($where)->count();
        //获取总条数
        $limit = 1000;
        $pages = ceil($total / $limit);

        $this->log("\n" . date('Y-m-d H:i:s') . "......................");
        $this->log("共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
        for ($i = 0; $i < $pages; $i++) {
            $loanlist = User_loan_guide::find()->where($where)->offset($i * $limit)->limit($limit)->all();
            // 没有数据时结束
            if (empty($loanlist)) {
                break;
            }
            $res = $this->addOverdue($loanlist);
            echo $this->error;
        }
        echo 'success' . $this->success . 'fail' . $this->error;
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
            $overdue = Overdue_loan_guide::find()->where(['loan_id' => $val['loan_id']])->all();
            if (!empty($overdue)) {
                continue;
            }
            $latefee = $val['chase_amount'] - $val['interest_fee'] - $val['amount'];
            if ($val['is_calculation'] != 1) {
                $latefee = $latefee - $val['withdraw_fee'];
            }
            $latefee = $latefee > 0 ? $latefee : 0;
            $data = [];
            $data['loan_id'] = isset($val['loan_id']) ? $val['loan_id'] : '';
            $data['user_id'] = isset($val['user_id']) ? $val['user_id'] : '';
            $data['bill_id'] = '';
            $data['bank_id'] = isset($val['bank_id']) ? $val['bank_id'] : '';
            $data['loan_no'] = isset($val['loan_no']) ? $val['loan_no'] : '';
            $data['amount'] = isset($val['amount']) ? $val['amount'] : '';
            $data['days'] = isset($val['days']) ? $val['days'] : '';
            $data['desc'] = isset($val['desc']) ? $val['desc'] : '';
            $data['start_date'] = isset($val['start_date']) ? $val['start_date'] : '';
            $data['end_date'] = isset($val['end_date']) ? $val['end_date'] : '';
            $data['loan_status'] = $val['status'] == 11 ? 11 : 12;
            $data['interest_fee'] = isset($val['interest_fee']) ? $val['interest_fee'] : '';
            $data['contract'] = isset($val['contract']) ? $val['contract'] : 'kong';
            $data['contract_url'] = isset($val['contract_url']) ? $val['contract_url'] : '';
            $data['late_fee'] = $latefee;
            $data['withdraw_fee'] = isset($val['withdraw_fee']) ? $val['withdraw_fee'] : '';
            $data['chase_amount'] = $val['chase_amount'];
            $data['is_push'] = 0;
            $data['business_type'] = isset($val['business_type']) ? $val['business_type'] : '';
            $data['source'] = isset($val['source']) ? $val['source'] : '';
            $data['is_calculation'] = isset($val['is_calculation']) ? $val['is_calculation'] : '';
            $data['repay_time'] = isset($val['repay_time']) ? $val['repay_time'] : $val['end_date'];
            $data['version'] = 0;
            $data['create_time'] = date('Y-m-d H:i:s');
            $res = (new Overdue_loan_guide())->saveOverdue($data);
            if (!$res) {
                $this->error += 1;
                continue;
            } else {
                $this->success += 1;
                $val->changeStatus(12);
            }
        }
    }

    public function actionSetchaseamount() {
        $limit = 1000;
        $error_num = $id = 0;
        $overdue_loan_model = new Overdue_loan_guide();
        $overdue_where = [
            "AND",
            ['loan_status' => [11, 12]],
            ['is_push' => [0, -1, -2, 1]],
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
            Overdue_loan_guide::updateAll(['is_push' => -1], ['id' => $ids]);
            foreach ($overdue_loans as $loan) {
                $loan_id = $loan['loan_id'];
                if (in_array($loan['business_type'], [7])) {            //未分期订单
                    $chase_amount = $this->getNotRes($loan);
                    if (!$chase_amount) {
                        continue;
                    }
                } else {
                    continue;
                }
                //将信息记录到overdue_loan_flows
                $overdue_loan_flows = (new Overdue_loan_flows_guide())->addOverdueLoanData($loan, $chase_amount);
                if (!$overdue_loan_flows) {
                    Logger::errorLog($loan_id . '-' . "罚息流水记录失败", 'getLoanOver', 'crontab');
                }
            }
            Overdue_loan_guide::updateAll(['is_push' => 1], ['is_push' => -2]);
            Overdue_loan_guide::updateAll(['is_push' => 0], ['is_push' => -1]);
        }
    }

    public function getNotRes($loan_this) {
//        $loan_this = User_loan::findOne($loan_id);
        if (!$loan_this) {
            Logger::errorLog($loan_this['loan_id'] . '-' . "无订单信息", 'getLoanOver', 'crontab');
            return FALSE;
        }
        $chase_amount = $loan_this->getUserLoanChaseAmount();
        if ($chase_amount / 3 >= $loan_this->amount) {         //判断最新一次计算后的金额是否要计息
            $chase_amount = $loan_this->amount * 3;
            $is_push_loan = Overdue_loan_guide::find()->where(['loan_id' => $loan_this['loan_id']])->one();
            $is_push_loan->update_userLoan(['is_push' => -2]);
        }
        if ($loan_this['is_calculation'] == 1) {
            $late_fee = $chase_amount - $loan_this['amount'] - $loan_this['interest_fee']; //计算滞纳金费用
        } else {
            $late_fee = $chase_amount - $loan_this['amount'] - $loan_this['interest_fee'] - $loan_this['withdraw_fee']; //计算滞纳金费用
        }
        $result = (new Overdue_loan_guide())->saveChaseAmount($loan_this['loan_id'], $chase_amount, $late_fee);
        if (!$result) {
            $error_num++;
            Logger::errorLog($loan_this['loan_id'] . '-' . "罚息失败", 'getLoanOver', 'crontab');
            return FALSE;
        }
        return $chase_amount;
    }

    /**
     * 到期前两天提醒
     */
    public function actionSendrepay() {
        $now_date = date('Y-m-d H:i:s');
        $end_date = date("Y-m-d H:i:s", strtotime("+2 day"));
        $where = [
            'AND',
            ['>', User_loan_guide::tableName() . '.end_date', $now_date],
            ['<=', User_loan_guide::tableName() . '.end_date', $end_date],
            [User_loan_guide::tableName() . '.status' => 9]
        ];
        $total = User_loan_guide::find()->where($where)->count();

        //获取总条数
        $limit = 1000;
        $pages = ceil($total / $limit);

        $this->log("\n" . date('Y-m-d H:i:s') . "......................");
        $this->log("total:{$total}:limit:{$limit},need{$pages}times\n");
        for ($i = 0; $i < $pages; $i++) {
            $oLoanList = User_loan_guide::find()->joinWith('user', true, 'LEFT JOIN')->where($where)->offset($i * $limit)->limit($limit)->all();
            if (empty($oLoanList)) {
                continue;
            }
            foreach ($oLoanList as $key => $val) {
                $sendResult = $this->sendSms($val);
                if ($sendResult) {
                    $this->success++;
                } else {
                    $this->error++;
                    Logger::errorLog($val['loan_id'], 'getLoanOver', 'crontab');
                }
            }
        }
        echo 'success' . $this->success . 'fail' . $this->error;
    }

    private function sendSms($loan) {
        $repayAmount = (new User_loan_guide())->getRepayment($loan, 2); //应还金额
        return $sendResponse = (new Sms_guide())->sendRepay($loan->user->mobile, $loan->user->realname, $repayAmount, $loan->create_time, $loan->end_date);
    }

}
