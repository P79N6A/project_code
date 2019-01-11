<?php

namespace app\modules\balance\models\yyy;

use app\common\ApiSign;
use app\common\Curl;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yi_user_loan".
 *
 * @property string $loan_id
 * @property string $parent_loan_id
 * @property integer $number
 * @property integer $settle_type
 * @property string $user_id
 * @property string $loan_no
 * @property string $real_amount
 * @property string $amount
 * @property string $recharge_amount
 * @property string $credit_amount
 * @property string $current_amount
 * @property integer $days
 * @property string $start_date
 * @property string $end_date
 * @property string $open_start_date
 * @property string $open_end_date
 * @property integer $type
 * @property integer $status
 * @property integer $prome_status
 * @property string $interest_fee
 * @property string $desc
 * @property string $contract
 * @property string $contract_url
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 * @property string $repay_time
 * @property string $withdraw_fee
 * @property string $chase_amount
 * @property string $like_amount
 * @property string $collection_amount
 * @property string $coupon_amount
 * @property integer $is_push
 * @property integer $final_score
 * @property integer $repay_type
 * @property integer $business_type
 * @property string $withdraw_time
 * @property string $bank_id
 * @property integer $source
 * @property integer $is_calculation
 */
class QjUserLoan extends YyyBase {

    public $shareurl;
    public $huankuan_amount;
    public $fee = 0.0005;
    public $with_fee = 0.1;
    public $all_interest_fee;
    public $all_chase_amount;
    public $all_amount;
    public $all_actual_money;
    public $total;
    public $actual_money;
    public $mobile;
    public $fund;
    public $total_loan_id;
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'qj_user_loan';
    }

    public function getCouponUse() {
        return $this->hasOne(Coupon_use::className(), ['loan_id' => 'loan_id']);
    }

    public function getRenewamount() {
        return $this->hasOne(Renew_amount::className(), ['loan_id' => 'loan_id']);
    }

    public function getExchange() {
        return $this->hasOne(Exchange::className(), ['loan_id' => 'loan_id']);
    }

    public function getPromes() {
        return $this->hasOne(Promes::className(), ['loan_id' => 'loan_id']);
    }

    public function getRemit() {
        return $this->hasOne(User_remit_list::className(), ['loan_id' => 'loan_id']);
    }

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['id' => 'bank_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getRepay() {
        return $this->hasOne(Loan_repay::className(), ['loan_id' => 'loan_id'])->orderBy('id desc');
    }

    public function getLoanextend() {
        return $this->hasOne(User_loan_extend::className(), ['loan_id' => 'loan_id']);
    }

    public function getGoodsOrder() {
        return $this->hasOne(GoodsOrder::className(), ['loan_id' => 'loan_id']);
    }

    public function getGoodsbills() {
        return $this->hasMany(GoodsBill::className(), ['loan_id' => 'loan_id']);
    }


    public function getCgRemit() {
        return $this->hasOne(Cg_remit::className(), ['loan_id' => 'loan_id']);
    }

    public function getRepayTime() {
        return $this->hasMany(RepayTime::className(), ['loan_id' => 'loan_id']);
    }

    public function getCmloans() {
        return $this->hasMany(Cm_loans::className(), ['loan_id' => 'loan_id']);
    }

    public function getInsure() {
        return $this->hasMany(Insure::className(), ['loan_id' => 'loan_id']);
    }

    public function getOverdueLoan() {

        return $this->hasMany(OverdueLoan::className(), ['loan_id' => 'loan_id']);
    }
    public function getPayaccount() {

        return $this->hasMany(PayAccount::className(), ['user_id' => 'user_id']);
    }

    public function getChaseamount($loan_id) {
        if (empty($loan_id) || !is_numeric($loan_id)) {
            return NULL;
        }
        $loan = self::findOne($loan_id);
        if (empty($loan)) {
            return NULL;
        }
        if (in_array($loan->business_type, [1, 4])) {
            if (time() <= strtotime($loan->end_date) || !in_array($loan->status, [8, 11, 12, 13])) {
                return NULL;
            }
            $overDue = OverdueLoan::find()->where(['loan_id' => $loan->loan_id])->one();
            if (empty($overDue) || empty($overDue->chase_amount)) {
                if ($loan->status == 8) {
                    return $loan->chase_amount;
                }
                return $loan->getMoneyByCalculation();
            }
            return $overDue->chase_amount;
        } else {
            $overDue = OverdueLoan::find()->where(['loan_id' => $loan->loan_id])->all();
            if (empty($overDue)) {
                return NULL;
            }
            $chase_amount = 0;
            foreach ($overDue as $val) {
                if (empty($val->chase_amount) || $val->loan_status == 8) {
                    $tmpAmount = $val->getMoneyByCalculation();
                    $chase_amount += $tmpAmount;
                } else {
                    $chase_amount += $val->chase_amount;
                }
            }
            return $chase_amount;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['parent_loan_id', 'number', 'settle_type', 'user_id', 'days', 'type', 'status', 'prome_status', 'version', 'is_push', 'final_score', 'repay_type', 'business_type', 'bank_id', 'source', 'is_calculation'], 'integer'],
            [['user_id', 'amount', 'current_amount', 'bank_id'], 'required'],
            [['real_amount', 'amount', 'recharge_amount', 'credit_amount', 'current_amount', 'interest_fee', 'withdraw_fee', 'chase_amount', 'like_amount', 'collection_amount', 'coupon_amount'], 'number'],
            [['start_date', 'end_date', 'open_start_date', 'open_end_date', 'last_modify_time', 'create_time', 'repay_time', 'withdraw_time'], 'safe'],
            [['loan_no', 'contract'], 'string', 'max' => 64],
            [['desc'], 'string', 'max' => 1024],
            [['contract_url'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'loan_id' => 'Loan ID',
            'parent_loan_id' => 'Parent Loan ID',
            'number' => 'Number',
            'settle_type' => 'Settle Type',
            'user_id' => 'User ID',
            'loan_no' => 'Loan No',
            'real_amount' => 'Real Amount',
            'amount' => 'Amount',
            'recharge_amount' => 'Recharge Amount',
            'credit_amount' => 'Credit Amount',
            'current_amount' => 'Current Amount',
            'days' => 'Days',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'open_start_date' => 'Open Start Date',
            'open_end_date' => 'Open End Date',
            'type' => 'Type',
            'status' => 'Status',
            'prome_status' => 'Prome Status',
            'interest_fee' => 'Interest Fee',
            'desc' => 'Desc',
            'contract' => 'Contract',
            'contract_url' => 'Contract Url',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
            'repay_time' => 'Repay Time',
            'withdraw_fee' => 'Withdraw Fee',
            'chase_amount' => 'Chase Amount',
            'like_amount' => 'Like Amount',
            'collection_amount' => 'Collection Amount',
            'coupon_amount' => 'Coupon Amount',
            'is_push' => 'Is Push',
            'final_score' => 'Final Score',
            'repay_type' => 'Repay Type',
            'business_type' => 'Business Type',
            'withdraw_time' => 'Withdraw Time',
            'bank_id' => 'Bank ID',
            'source' => 'Source',
            'is_calculation' => 'Is Calculation',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * 获取用户进行中的借款
     * @param int $userid
     * @param array $business_type
     * @return int
     */
    public function getHaveinLoan($userid, $business_type = [1, 4, 5, 6]) {
        if (empty($userid)) {
            return false;
        }
        $status = array('5', '6', '9', '11', '12', '13');
        $where = [
            'OR',
            [
                "AND",
                ['user_id' => $userid],
                ['status' => $status],
                ['business_type' => [1, 4, 5, 6]],
            ],
            [
                "AND",
                ['user_id' => $userid],
                ['prome_status' => 1],
            ],
        ];
        $user_loan = self::find()->where($where)->one();
        return !empty($user_loan) ? $user_loan->loan_id : 0;
    }

    /**
     * 查询符合条件的用户借款信息
     * @param arr $where_arr 查询的where条件
     * @return NULL || obj
     */
    public function checkUserLoan($where_arr, $limit = 1000) {
        if (empty($where_arr) || !is_array($where_arr)) {
            return NULL;
        }
        $user_loan_info = static::find()->where($where_arr)->limit($limit)->all();

        return $user_loan_info;
    }

    /**
     * 获取逾期天数
     * @param $status
     * @param $end_date
     * @return float|int
     */
    public function getOverdueDays($userLoanObj) {
        if (in_array($userLoanObj->business_type, [1, 4])) {
            if (time() > strtotime($userLoanObj->end_date)) {
                $overdue_days = ceil((time() - strtotime($userLoanObj->end_date)) / 24 / 3600);
            } else {
                $overdue_days = 0;
            }
        } else {
            $overdueLoanObj = (new OverdueLoan())->listOverdueByLoanId($userLoanObj->loan_id);
            if (!empty($overdueLoanObj)) {
                $overdueLoan = $overdueLoanObj[0];
                $overdue_days = ceil((time() - strtotime($overdueLoan->end_date)) / 24 / 3600);
            } else {
                $overdue_days = 0;
            }
        }
        return $overdue_days;
    }

    public function addUserLoan($condition, $business_type = 1) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }

        $data = $condition;
        $data['number'] = 0;
        $data['settle_type'] = 0;
        $data['open_start_date'] = date('Y-m-d H:i:s');
        $data['open_end_date'] = $this->getOpenEndTime();
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['version'] = 1;
        $data['business_type'] = $business_type;

        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        $result = $this->save();
        if (!$result) {
            return false;
        }
        $loan_id = Yii::$app->db->getLastInsertID();
        $loan_info = self::findOne($loan_id);
        $loan_info->parent_loan_id = $loan_id;
        $loan_info->save();
        //借款记录的添加，同时要添加flows记录
        $flow_condition = [
            'loan_id' => $loan_id,
            'loan_status' => $loan_info->status,
        ];
        $flowModel = new User_loan_flows();
        $flowModel->add_Record($flow_condition, -1);
        //添加loan_rate记录
        $user_rate = (new User_rate())->getrateone($condition['user_id'], $condition['days']);
        if (empty($user_rate)) {
            $rate = $condition['withdraw_fee'] / $condition['amount'] * 100;
            $interest = $condition['interest_fee'] / $condition['days'] / $condition['amount'] * 100;
        } else {
            $rate = $user_rate['rate'] * 100;
            $interest = $user_rate['interest'] * 100;
        }
        $loan_rate_condition = [
            'loan_id' => $loan_id,
            'user_id' => $condition['user_id'],
            'days' => $condition['days'],
            'rate' => $rate,
            'interest' => $interest,
        ];
        $loan_rate = new Loan_rate();
        $loan_rate->addloanrate($loan_rate_condition);
        return $loan_id;
    }

    /**
     * 获取结束时间
     * @param string $open_start_date 开始时间
     * @return string $open_end_date
     */
    private function getOpenEndTime($open_start_date = '') {
        if (empty($open_start_date)) {
            $hour = date('H');
        } else {
            $hour = date('H', strtotime($open_start_date));
        }
        if ($hour >= 0 && $hour < 9) {
            $open_end_date = date('Y-m-d 15:00:00');
        } else if ($hour < 18) {
            $open_end_date = date('Y-m-d H:i:s', strtotime('+6 hour'));
        } else {
            $open_end_date = date('Y-m-d H:i:s', strtotime('+15 hour'));
        }
        return $open_end_date;
    }

    /**
     *
     * @param type $user
     * @param type $from
     * @param type $amount
     * @param type $days
     * @param type $desc
     * @param type $loan_no
     * @return int  0:没有触犯规则 1：添加驳回借款  2：拉黑用户了，直接跳转
     */
    public function getRule($user, $from, $amount, $days, $desc, $loan_no, $business_type = 1) {
        if (empty($user) || empty($from) || empty($amount) || empty($days) || empty($desc) || empty($loan_no)) {
            $loan_no_keys = $user->user_id . "_loan_no";
            Yii::$app->redis->del($loan_no_keys);
            exit;
        }
        $api = new XhhApi();
        $limit = $api->runDecisions($user, $from, 'loan', $amount, $days, $desc);
        if (!empty($limit)) {
            $limit_new = [];
            foreach ($limit as $key => $val) {
                if (in_array($key, ['loan_time_start', 'loan_time_end', 'age_value', 'more_loan_value', 'one_more_loan_value', 'seven_more_loan_value', 'one_number_account_value', 'is_black'])) {
                    if (!empty($val)) {
                        $limit_new[$key] = $val;
                    }
                }
            }
            if (!empty($limit_new)) {
                $condition = $limit_new;
                $condition['loan_no'] = $loan_no;
                $event = (new Loan_event())->add_Record($user->user_id, $condition);
            }
            Logger::dayLog('loan_limit', $user->user_id, $limit);
        }

        //借款决策收集数据
        if (!empty($user->user_id)) {
            //判断是否存在借款
            $total = (new User())->isRepeatUser($user->user_id);
            $type = 1;
            if($total != 0){
                $type = 2;
            }
            $rsa = new ApiSign();
            $data = [
                'user_id' => (string) $user->user_id, //用户ID
                'loan_no' => (string) $loan_no,
                'amount' => $amount,
                'business_type' => $business_type,
                'days' => $days,
                'type' => $type,
                'source' => (string) $from,
                'query_time' => date("Y-m-d H:i:s", time()),
            ];
            // 签名的使用
            $sign = $rsa->signData($data);
            $curl = new Curl();
            Logger::dayLog('loan_limit_send', print_r(array($sign), true));
            if (SYSTEM_PROD) {
                $url = "http://10.139.52.241:8088/api/loan/loanone";
            } else {
                $url = "http://182.92.80.211:8122/api/loan/loanone";
            }
            $ret = $curl->post($url, $sign);
            Logger::dayLog('loan_limit_return', print_r(array($user->user_id => $ret), true));
            $result = json_decode($ret, true);
            $isVerify = (new ApiSign)->verifyData($result['data'], $result['_sign']);
            if (!$isVerify) {
                return 0;
            }
            if (!empty($result)) {
                $result_data = json_decode($result['data'], true);
                if ($result_data['res_code'] == 0) {
                    return 0;
                }
            }
        }
        return 1;
    }

    /**
     * 发起借款的时候被驳回使用的方法
     * @param type $user
     * @param type $loan_no
     * @param type $amount
     * @param type $days
     * @param type $desc
     * @param type $status
     * @param type $final_score
     * @param type $coupon_id
     * @param type $coupon_amount
     * @param type $source
     * @param type $final_result
     * @return boolean
     */
    public function addRejectLoan($user, $loan_no, $amount, $days, $desc, $status, $final_score, $coupon_id, $coupon_amount, $source = 2, $final_result = 'Reject', $business_type = 1, $dayratestr = 0.0005, $with_fee = 0.1) {
        $interest_fee = round($amount * $dayratestr * $days, 2);
        $withdraw_fee = (round($amount * $with_fee, 2) > 5) ? round($amount * $with_fee, 2) : 5;

        //借款驳回
        $condition = array(
            'user_id' => $user->user_id,
            'loan_no' => $loan_no,
            'real_amount' => $amount,
            'amount' => $amount,
            'credit_amount' => 0,
            'recharge_amount' => 0,
            'current_amount' => 0,
            'days' => $days,
            'status' => $status,
            'prome_status' => 1,
            'interest_fee' => $interest_fee,
            'desc' => $desc,
            'withdraw_fee' => $withdraw_fee,
            'final_score' => $final_score,
            'bank_id' => -1,
            'source' => !empty($source) ? $source : 2,
            'is_calculation' => 1,
        );
        if (!empty($coupon_id)) {
            $condition['coupon_amount'] = $interest_fee > $coupon_amount ? $coupon_amount : $interest_fee;
        }
        $loans = $this->addUserLoan($condition, $business_type);
        if (!empty($coupon_id)) {
            $couponUseModel = new Coupon_use();
            $couponUseModel->addCouponUse($user, $coupon_id, $loans, 2);
        }
        Logger::errorLog(print_r(array($loans), TRUE), 'create_loan');
        $loan = self::findOne($loans);
        $frauModel = new Fraudmetrix_return_info();
        $result = $frauModel->setLoanId($loans, $loan_no);

        if ($loans) {
            if ($final_result == 'Reject') {
                $reason = '请30天后再次尝试借款';
            } else if ($final_score >= 60) {
                $reason = '请一周后再次尝试发起借款';
            } else {
                $reason = '暂不符合借款要求';
            }
            $flowsModel = new User_loan_flows();
            $flowsModel->addRecord(['loan_id' => $loans, 'loan_status' => $status, 'reason' => $reason], -1);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 重构发起借款的时候被驳回使用的方法
     * @param type $user
     * @param type $loan_no
     * @param type $amount
     * @param type $days
     * @param type $desc
     * @param type $status
     * @param type $final_score
     * @param type $coupon_id
     * @param type $coupon_amount
     * @param type $source
     * @param type $final_result
     * @return boolean
     */
    public function _addRejectLoan($user, $loan_no, $amount, $days, $desc, $status, $final_score, $coupon_id, $coupon_amount, $source = 2, $final_result = 'Reject', $business_type = 1, $dayratestr = 0.0005, $with_fee = 0.1) {
        $userloan = new UserLoan();
        $interest_fee = round($amount * $dayratestr * $days, 2);
        $withdraw_fee = (round($amount * $with_fee, 2) > 5) ? round($amount * $with_fee, 2) : 5;
        //是否为系统指定后置用户
        $charge = (new User_label())->isChargeUser($user->mobile);
        if ($charge == false) {
            $is_calculation = 1;
        } else {
            $is_calculation = 0;
        }
        //借款驳回
        $condition = array(
            'user_id' => $user->user_id,
            'loan_no' => $loan_no,
            'real_amount' => $amount,
            'amount' => $amount,
            'credit_amount' => 0,
            'recharge_amount' => 0,
            'current_amount' => 0,
            'days' => $days,
            'status' => $status,
            'prome_status' => 1,
            'interest_fee' => $interest_fee,
            'desc' => $desc,
            'withdraw_fee' => $withdraw_fee,
            'final_score' => $final_score,
            'bank_id' => -1,
            'source' => !empty($source) ? $source : 2,
            'is_calculation' => $is_calculation,
        );
        $error = $this->chkAttributes($condition);
        if ($error) {

            return false;
        }
        if (!empty($coupon_id)) {
            $condition['coupon_amount'] = $interest_fee > $coupon_amount ? $coupon_amount : $interest_fee;
        }
        $loans = $this->addUserLoan($condition, $business_type);
        if (!empty($coupon_id)) {
            $couponUseModel = new Coupon_use();
            $couponUseModel->addCouponUse($user, $coupon_id, $loans, 2);
        }
        Logger::errorLog(print_r(array($loans), TRUE), 'create_loan');
        $loan = self::findOne($loans);
        $frauModel = new Fraudmetrix_return_info();
        $result = $frauModel->setLoanId($loans, $loan_no);
        if ($loans) {
            if ($final_result == 'Reject') {
                $reason = '请30天后再次尝试借款';
            } else if ($final_score >= 60) {
                $reason = '请一周后再次尝试发起借款';
            } else {
                $reason = '暂不符合借款要求';
            }
            $flowsModel = (new User_loan_flows())->find()->where(['loan_id' => $loans])->one();
            $flowsModel->add_Record(['loan_id' => $loans, 'loan_status' => $status, 'reason' => $reason], -1);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 更改借款状态
     * @param $status
     * @param int $type
     * @return bool
     */
    public function changeStatus($status, $type = -1) {
        if (!$status) {
            return false;
        }
        $condition = [
            'status' => $status,
        ];
        if ($status == 5) {
            $condition['withdraw_time'] = date('Y-m-d H:i:s');
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        if (!$this->save()) {
            return false;
        }

        $flowdata = array('loan_id' => $this->loan_id, 'loan_status' => $status);
        $flow = new User_loan_flows();
        $flow->add_Record($flowdata, $type);
        if ($status == 3 || $status == 4 || $status == 7) {
            if (!$this->reject()) {
                return false;
            }
        }
        return TRUE;
    }

    /**
     * 借款驳回，失效返回投资者额度
     */
    public function reject() {
        $create_time = date('Y-m-d H:i:s');
        //查询该笔借款是否有使用优惠券，如果有使用优惠券，则判断优惠券是否过期，没有过期则返还给用户
        $loan_coupon = Coupon_list::find()->joinWith('couponuse', true, 'LEFT JOIN')->where([Coupon_use::tableName() . '.loan_id' => $this->loan_id])->one();
        if (!empty($loan_coupon)) {
            if ($create_time < $loan_coupon['end_date']) {
                $loan_coupon->status = 1;
            } else {
                $loan_coupon->status = 3;
            }
            if (!$loan_coupon->save()) {
                return false;
            }
        }
        return true;
    }

    /*
     * 获取用户借款和还款信息
     */

    public function getUserLoanByUserId($userId) {
        $sql = "SELECT SUM(b.`actual_money`) as repay_amount, a.*  FROM yi_user_loan AS a LEFT JOIN yi_loan_repay as b ON a.loan_id=b.loan_id WHERE a.user_id = $userId GROUP BY a.loan_id";
        return self::findBySql($sql)->all();
    }

    /*
     * 获取逾期数量
     */

    public function getYuqiCounts($startTime = null, $endTime = null) {
        $where = [
            'and',
            ['in', 'status', [8, 11, 12, 13]],
//            ['business_type'=> 1],
            ['in', 'business_type', [1, 4]],
            ['>', 'chase_amount', 0],
            ['in', 'is_push', [0, 1]],
        ];

        if ($startTime) {
            $where[] = ['>=', 'end_date', $startTime];
        }
        if ($endTime) {
            $where[] = ['<', 'end_date', $endTime];
        }
        $count = self::find()->where($where)->count();
        return $count > 0 ? $count : 0;
    }

    /*
     * 获取逾期数据信息
     */

    public function getYuqiinfos($startTime = null, $endTime = null, $offset = 0, $limit = 0) {
        $where = [
            'and',
//            ['business_type'=> 1],
            ['in', 'business_type', [1, 4]],
            ['in', 'status', [11, 12, 13]],
            ['in', 'is_push', [0, 1]],
        ];

        if ($startTime) {
            $where[] = ['>=', 'end_date', $startTime];
        }
        if ($startTime) {
            $where[] = ['<', 'end_date', $endTime];
        }
        $query = self::find()->where($where);
        if ($offset > 0) {
            $query->offset($offset);
        }
        if ($limit > 0) {
            $query->limit($limit);
        }
        $res = $query->all();
        return $res;
    }

    /*
     * 获取逾期数据
     */

    public function getYuqiCount($startTime = null, $endTime = null) {
        $where = [
            'and',
            ['in', 'status', [12, 13]],
        ];
        if ($startTime) {
            $where[] = ['>=', 'end_date', $startTime];
        }
        if ($startTime) {
            $where[] = ['<', 'end_date', $endTime];
        }
        return self::find()->where($where)->count();
    }

    /*
     * 获取逾期数据
     */

    public function getYuqiInfo($startTime = null, $endTime = null, $offset = 0, $limit = 0) {
        $where = [
            'and',
            ['in', 'status', [12, 13]],
        ];
        if (!empty($startTime)) {
            $where[] = ['>=', 'end_date', $startTime];
        }
        if (!empty($startTime)) {
            $where[] = ['<', 'end_date', $endTime];
        }
        $query = self::find()->where($where);
        if ($offset > 0) {
            $query->offset($offset);
        }
        if ($limit > 0) {
            $query->limit($limit);
        }
        $res = $query->all();
        return $res;
    }

    /*
     * 获取逾期数据
     */

    public function getYuqiBylastmodifytime($startTime = null, $endTime = null) {
        $where = [
            'and',
            ['in', 'status', [12, 13]],
        ];
        if (!empty($startTime)) {
            $where[] = ['>=', 'last_modify_time', $startTime];
        }
        if (!empty($endTime)) {
            $where[] = ['<', 'last_modify_time', $endTime];
        }
        if (!empty($startTime)) {
            $where[] = ['<', 'end_date', $startTime];
        }
        $query = self::find()->where($where);
        $res = $query->all();
        return $res;
    }

    /**
     * 获取借款优惠金额
     * @param $loan_id
     * @return int|mixed|string
     */
//    public function getCouponAmount($loan_id) {
//        $loan = User_loan::findOne($loan_id);
//        $use_coupon = Coupon_use::find()->where(['loan_id' => $loan_id])->one();
//        if (!empty($use_coupon)) {
//            $coupon_list = Coupon_list::find()->where(['id' => $use_coupon->discount_id])->one();
//            $coupon_amount = $coupon_list->val == 0 ? $loan->interest_fee : ($loan->interest_fee > $coupon_list->val ? $coupon_list->val : $loan->interest_fee);
//        } else {
//            $coupon_amount = 0;
//        }
//        return $coupon_amount;
//    }

    /*
     * 根据借款前后置获取金额
     */
    public function getMoneyByCalculation() {
        if ($this->is_calculation == 1) {
            $moneys = $this->amount + $this->interest_fee;
        } else {
            $moneys = $this->amount + $this->interest_fee + $this->withdraw_fee;
        }
        return $moneys;
    }

    /*
     * 格式化金额
     */

    public function getFormatAmount($total_amount) {
        if ($total_amount * 10000 % 100 != 0) {
            return ceil($total_amount * 100) / 100;
        }
        return $total_amount;
    }

    /**
     * 获取借款原始应还款金额
     * @param int loan_id 借款ID
     * @param int type 1:计算借款总应还款金额 2：计算借款 本金+利息+手续费 或者 本金+利息
     * @return float
     */
    public function getAllMoney($loan_id, $type = 1) {
        $loan = self::findOne($loan_id);
        $moneys = $loan->getMoneyByCalculation();
        if ($type == 2) {
            return $moneys;
        }

        $loan->chase_amount = $loan->getChaseamount($loan_id);  //分期后 重置逾期金额
        //逾期返回逾期金额
        if (!empty($loan->chase_amount) && $loan->chase_amount != '0.0000') {
            return $this->getFormatAmount($loan->chase_amount);
        }

        //未逾期
        $coupon_amount = $this->getCouponAmount($loan_id);
        //status=7模型驳回  3同盾驳回
        if ($loan->status == 7 || ($loan->prome_status == 1 && $loan->status == 3)) {
            $total_amount = $moneys - $coupon_amount;
            return $this->getFormatAmount($total_amount);
        }
        //借款正常状态
        $total_amount = $moneys - $loan->like_amount - $coupon_amount;
        if ($loan->is_calculation == 1) {
            $total_amount = $total_amount >= $loan->amount ? $total_amount : $loan->amount;
        } else {
            $total_amount = $total_amount >= ($loan->amount + $loan->withdraw_fee) ? $total_amount : ($loan->amount + $loan->withdraw_fee);
        }
        return $this->getFormatAmount($total_amount);
    }

    /**
     * 获取应还款的金额
     * @param $loanInfo
     * @param $repay_mark   1总应还款金额 2未还总应还款金额
     * @return float|int|string
     */
    public function getRepaymentAmount($loanInfo, $repay_mark = 1) {
        if (in_array($loanInfo->business_type, [5, 6])) {  //获取分期的应还款金额
            return $loanInfo->getStagesRepayAmount();
        } else {
            $total_amount = $this->getAllMoney($loanInfo->loan_id);
            if ($loanInfo->status != 8 || $repay_mark != 1) {
                $already_money = $loanInfo->getRepayAmount(2);
                if ($already_money != 0) {
                    $total_amount = bcsub($total_amount, $already_money, 2);
                }
            }
            if ($total_amount * 10000 % 100 != 0) {
                return ceil($total_amount * 100) / 100;
            } else {
                return $total_amount;
            }
        }
    }

    /**
     * 获取借款优惠金额
     * @param $loan_id
     * @return int|mixed|string
     */
    public function getCouponAmount($loan_id) {
        $loan = self::findOne($loan_id);
        $use_coupon = Coupon_use::find()->where(['loan_id' => $loan_id])->one();
        if (!empty($use_coupon)) {
            $coupon_list = Coupon_list::find()->where(['id' => $use_coupon->discount_id])->one();
            $coupon_amount = 0;
            if (!empty($coupon_list)) {
                $coupon_amount = $coupon_list->val == 0 ? $loan->interest_fee : ($loan->interest_fee > $coupon_list->val ? $coupon_list->val : $loan->interest_fee);
            }
        } else {
            $coupon_amount = 0;
        }
        return $coupon_amount;
    }

    /**
     * 获取借款续期费用
     * @param $loan_id
     * @return float|int
     */
    public function getRenewalMoney($loan_id) {
        $loan = self::findOne($loan_id);
        if (empty($loan)) {
            return 0;
        }
        $renewModel = new Renew_amount();
        $renew = $renewModel->isCanrenew($loan);
        if ($renew) {
            $money = $renewModel->getRenewFee($loan);
            if ($money) {
                return round($money, 2);
            }
        }
        $money = $loan->withdraw_fee + $loan->interest_fee + 30;
        return round($money, 2);
    }

    /**
     * 还款金额
     * @param int $type 1:原有获取方式  2：续期状态已还款金额
     * @return type
     */
    public function getRepayAmount($type = 1) {
        $parent_id = $this->parent_loan_id;
        if (empty($parent_id)) {
            $type = 1;
        }
        if ($type == 1) {
            $loan_id = $this->loan_id;
            $amount = Loan_repay::find()->where(['loan_id' => $loan_id, 'status' => 1])->sum('actual_money');
        } else {//续期
            $loan_id = self::find()->select(['loan_id'])->where(['parent_loan_id' => $parent_id])->asArray()->all();
            $loan_ids = ArrayHelper::getColumn($loan_id, 'loan_id');
            $amount = Loan_repay::find()->where(['loan_id' => $loan_ids, 'status' => 1])->sum('actual_money');
        }
        return $amount;
    }

    /*
     * 获取指定用户的借款次数
     */

    public function getUserLoanCount($userid) {
        $count = self::find()->where(['user_id' => $userid])->count();
        return $count > 0 ? $count : 0;
    }

    /**
     * 实际出款金额
     * @param type $is_calculation
     * @param type $amount
     */
    public function getActualAmount($is_calculation, $amount, $withdraw_fee = 0) {
        $settle_amount = ($is_calculation == 1) ? $amount - $withdraw_fee : $amount;
        return $settle_amount;
    }

    /**
     * 账单总额
     * @param $is_calculation
     * @param $amount
     * @param $withdraw_fee
     * @param $interest_fee
     * @return mixed
     */
    public function getOrderAmount($is_calculation, $amount, $withdraw_fee, $interest_fee) {
        if ($is_calculation == 1) {
            $orderAmount = $amount + $interest_fee;
        } else {
            $orderAmount = $amount + $interest_fee + $withdraw_fee;
        }
        return $orderAmount;
    }

    /**
     * 计算总还款本金
     * 前置：本金    后置：本金+服务费
     * @param $is_calculation
     * @param $amount
     * @param $withdraw_fee
     * @return mixed
     */
    public function getPrincipal($is_calculation, $amount, $withdraw_fee) {
        if ($is_calculation == 1) {
            $orderAmount = $amount;
        } else {
            $orderAmount = $amount + $withdraw_fee;
        }
        return $orderAmount;
    }

    /**
     * 修改计息开始时间和结束时间
     */
    public function saveEndtime($days) {
        $now_time = date('Y-m-d');
        $end_time = date('Y-m-d', (time() + ($days + 1) * 24 * 3600));
        $this->start_date = $now_time;
        $this->end_date = $end_time;
        $this->last_modify_time = date('Y-m-d H:i:s');

        $result = $this->save();
        return $result;
    }

    /**
     *
     * 对担保借款（有卡）进行限时限量入场
     * @param $checkout_limit  1限时， 2限量， 3限时限量
     * @return int 1达到上限， 0 未达到上限
     */
    public function limitLoan($checkout_limit) {
        return 0;
        $limit_start_limit = "2017-05-18 00:00:00"; //限制可借款开始时间
        $limit_end_limit = "2017-05-20 00:00:00"; //限制可借款结束时间
        $day_loan = 500;  //限量：每天通过普罗米后进入人工信审达到500单则关闭申请入口
        $cur_time = time();
        if ($checkout_limit & 1) {
            //限时：某个区间可进行借款申请，过时则关闭申请入口
            $limit_start_limit = strtotime($limit_start_limit); //限制开始时间
            $limit_end_limit = strtotime($limit_end_limit); //限制结束时间

            if ($cur_time > $limit_end_limit || $cur_time < $limit_start_limit) {
                return 1;
            }
        }
        if ($checkout_limit & 2) {
            //限量：每天通过普罗米后进入人工信审达到500单则关闭申请入口
            //status=5 prome_status=5 business_type=4
            $user_loan = self::find()
                ->where([
                    'AND',
                    ['status' => 5],
                    ['prome_status' => 5],
                    ['business_type' => 4],
                    [">=", 'last_modify_time', date("Y-m-d 00:00:00", $cur_time)],
                    ["<", 'last_modify_time', date("Y-m-d 00:00:00", strtotime("1 days"))]
                ])->count();

            if ($day_loan <= $user_loan) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * 获取逾期罚息
     */
    public function getOverdueAmount($loanId) {
        $overdue_amount = (new OverdueLoan())->getLateFeeByLoanId($loanId);
        if ($overdue_amount * 10000 % 100 != 0) {
            return ceil($overdue_amount * 100) / 100;
        } else {
            return $overdue_amount;
        }
    }

    /**
     * 存储借款的逾期罚息
     * @param type $chase_amount 罚息金额
     * @param type $is_push 是否还需要进行罚息计算  1 不需要；0 需要； -1 过渡，必须改为1
     * @return boolean
     */
    public function saveChaseAmount($chase_amount, $is_push = 0) {
        $data['chase_amount'] = $chase_amount;
        if ($is_push != 0) {
            $data['is_push'] = $is_push;
        }
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            print_r($error);
//            Logger::errorLog(print_r(array("$this->loan_id 罚息更新失败-- $chase_amount"), true), 'getLoanOver', 'crontab');
            return FALSE;
        }
        $result = $this->save();
        return $result;
    }

    /**
     * 借款生成之后，更改同盾记录表的loan_id和loan表的final_score
     * @return boolean
     */
    public function saveFinalScore($loan) {
        $frauModel = new Fraudmetrix_return_info();
        $frau = $frauModel->find()->where(['loan_id' => $loan->loan_no])->one();
        if (empty($frau)) {
            return TRUE; //如果此处没有同盾信息，不能影响借款流程的进行
        }
        $score = $frau->final_score;
        $result = $frau->updateRecord(['loan_id' => $loan->loan_no]);
        $error = $loan->chkAttributes(['final_score' => $score]);
//        print_r($error);die;
        if ($error) {
            return FALSE;
        }
        return $loan->save();
    }

    /**
     *
     * @param type $userid
     * @param type $status
     * @param type $business_type  -1 表示查询所有类型借款
     * @return type
     */
    public function getUserLoan($userid, $status = '', $business_type = '') {
        if (empty($userid)) {
            return null;
        }
        $nowtime = date('Y-m-d H:i:s');
        $loan = self::find()->where(['user_id' => $userid]);
        if (!empty($status)) {
            $loan = $loan->andWhere(['status' => $status]);
        }
        if (empty($business_type)) {
            $loan = $loan->andWhere(['business_type' => 1]);
        } else {
            $loan = $loan->andWhere(['business_type' => $business_type]);
        }
        $result = $loan->orderBy('open_end_date desc')->all();
        if (!empty($result)) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * 借款驳回判断方法
     * 规则：限制用户：初贷（未成功借款）用户
    限制条件：被人工审核驳回（有驳回原因）后未进行修改借款资料的用户
    借款资料：单位信息（名称，电话，地址），联系人信息（姓名，关系，手机号），邮箱，通讯录（是否增加）
    限制时间：本次借款人工信审驳回后未修改资料两天（可调阈值）内发起借款即被系统延迟驳回
     * @param $user_id  用户user_id
     * @return bool
     */
    public function LoanJudgment($user_id) {
        if (empty($user_id))
            return false;
        //初贷（未成功借款）用户
        $first_loan_user = $this->firstLoanUser($user_id);
        if (!empty($first_loan_user))
            return true;
        $loan_info = $this->rejectLoanInfo($user_id);
        //不存在返回true
        if (empty($loan_info))
            return true;
        if ((new White_list())->isWhiteList($user_id)) {//用户是白名单用户，限制时间为3小时
            $one_day_time = 60 * 60 * 3;
        }else{//限制时间为2天内
            $one_day_time = 60 * 60 * 24 * 2;
        }
        $time = time();
        //取出最近修改时间
        $last_modify_time = strtotime($loan_info['last_modify_time']);
        $limit_time = $time - $last_modify_time;
        //驳回超过2天返回true
        if ($limit_time > $one_day_time)
            return true;
        //取出联系人最后修改的时间
        $favorite_contacts = new Favorite_contacts();
        $favorite_info = $favorite_contacts->getFavoriteByUserId($user_id);
        //判断联系人最后修改时间
        if (!empty($favorite_info)) {
            $favorite_last_time = strtotime($favorite_info['last_modify_time']);
            if ($last_modify_time < $favorite_last_time)
                return true;
        }
        //判断历时记录时间
        $User_history_info = new User_history_info();
        $history_info = $User_history_info->newestHistory($user_id);
        if (!empty($history_info)) {
            $history_last_time = strtotime($history_info->create_time);
            if ($last_modify_time < $history_last_time)
                return true;
        }
        return false;
    }

    /**
     * 用户借款成功
     * @return array|bool|null|ActiveRecord
     */
    private function firstLoanUser($user_id) {
        if (empty($user_id))
            return false;
        $loan_info = self::find()->where(['user_id' => $user_id])->andWhere(['in', 'status', array(8, 9, 11, 12, 13)])->one();
        if (!empty($loan_info)) {
            return $loan_info;
        }
        return array();
    }

    /**
     * 最近一笔驳回借款
     * @param $user_id
     * @return array|bool|null|ActiveRecord
     */
    public function rejectLoanInfo($user_id) {
        if (empty($user_id))
            return false;
        $where = [
            'and',
            ['=', 'user_id', $user_id],
            [
                'or',
                ['=', 'status', '7'],
                [
                    'and',
                    ['=', 'status', '3'],
                    ['=', 'prome_status', '5'],
                ]
            ]
        ];
        $loan_info = self::find()->where($where)->orderBy(['last_modify_time' => SORT_DESC])->one();
        if (!empty($loan_info)) {
            return $loan_info;
        }
        return array();
    }

    /**
     * 计算借款服务费
     * @param $amount 借款金额
     * @param $with_fee 服务费比例，如果不传使用user_loan对象的属性$with_fee,如果有特殊的就用特殊的
     * @return mixed
     */
    public function getServiceAmount($amount, $with_fee = 0) {
        if ($with_fee == 0) {
            return $amount * $this->with_fee;
        }
        return $amount * $with_fee;
    }

    /**
     * 进行中的借款状态整理，页面输出
     * @param type $loan
     * @return Array status借款状态，view显示页面view  1：审核界面  2：待还款界面  3：逾期界面 4：提现页面 5：投保页面
     */
    public function getLoanStatusView($loan) {
        if (empty($loan)) {
            return FALSE;
        }
        $status = $loan->status;
        switch ($status) {
            case 3:
                if ($loan->prome_status == 1) {
                    $status = 5;
                }
                break;
            case 9:
                $user_extend = $loan->loanextend;
                if (isset($user_extend) && $user_extend->status != 'SUCCESS') {
                    $status = 22;
                }
                if (in_array($loan->business_type, [5, 6])) {
                    $goodsBillCount = (new GoodsBill())->getCountByLoanId($loan->loan_id);
                    if ($goodsBillCount == 0) {
                        $status = 6;
                    }
                    $chaseAmount = (new OverdueLoan())->getLateFeeByLoanId($loan->loan_id);
                    if ($chaseAmount > 0) {
                        $status = 12;
                    }
                }
                //5分钟内是否有还款并且未成功页面限制
                $whereconfig = [
                    'AND',
                    ['loan_id' => $loan->loan_id],
                    ['in', 'status', [-1]],
                ];
                $repay_info = Loan_repay::find()->where($whereconfig)->orderBy('createtime desc')->one();
                if (!empty($repay_info)) {
                    $status = 11;
                }
                if (in_array($loan->business_type, [5, 6])) {
                    $billRepayWhere = [
                        'AND',
                        ['loan_id' => $loan->loan_id],
                        ['status' => -1]
                    ];
                    $billRepayInfo = BillRepay::find()->where($billRepayWhere)->orderBy('createtime desc')->one();
                    if (!empty($billRepayInfo)) {
                        $status = 11;
                    }
                }
                $cgRemitObj = $loan->cgRemit;
                if (!empty($cgRemitObj)) {
                    if($cgRemitObj->remit_status == 'WILLREMIT'){
                        $status = 18;
                    }elseif ($cgRemitObj->remit_status == 'DOREMIT'){
                        $status = 19;
                    }
                }
                break;
            case 6:
                //20：待发起提现 21：购买中 22：提现审核中
                $user_extend = $loan->loanextend;
                $insurance = Insurance::find()->where(['loan_id'=>$loan->loan_id])->one();//投保表
                if($user_extend->status == 'TB-SUCCESS'){
                    $status = 20;
                    $time_in = date("Y-m-d H:i:s", strtotime("-23 hours"));
                    if($insurance->status == 2 || $insurance->is_chk == 2 || $insurance->create_time<$time_in){
                        $status = 22;
                    }
                    $insureWhere = [
                        'AND',
                        ['loan_id' => $loan->loan_id],
                        ['status' => [0,-1]]
                    ];
                    $insureInfo = Insure::find()->where($insureWhere)->orderBy('create_time desc')->one();
                    if (!empty($insureInfo)) {
                        $status = 21;
                    }
                }elseif (in_array($user_extend->status,['AUTHED','PRE-REMIT'])){
                    $status = 22;
                }else{
                    $status = 5;
                }
                break;
        }
        $views = [
            '1' => [5, 6],
            '2' => [9, 11],
            '3' => [12, 13],
            '4' => [18, 19],
            '5' => [20, 21, 22],
        ];

        $view = 1;
        foreach ($views as $key => $val) {
            if (in_array($status, $val)) {
                $view = $key;
                break;
            }
        }
        return [
            'status' => $status,
            'view' => $view,
        ];
    }

    public function loan_Fee($amount, $days) {
        $loan = new self();
        $interest_fee = ceil($amount * $loan->fee * $days * 100) / 100;
        $withdraw_fee = ceil($amount * $loan->with_fee * 100) / 100;
        return array(
            'interest_fee' => $interest_fee,
            'withdraw_fee' => $withdraw_fee,
        );
    }

    /**
     * 后台借款状态展示
     * @param $loan
     * @return bool|int
     */
    public function getLoanStatus($loan){
        if (empty($loan)) {
            return FALSE;
        }
        $status = $loan->status;
        switch ($status) {
            case 3:
                if ($loan->prome_status == 1) {
                    $status = 5;
                }
                break;
            case 6:
                $user_extend = $loan->loanextend;
                $insurance = Insurance::find()->where(['loan_id'=>$loan->loan_id])->one();//投保表
                if($user_extend->status == 'TB-SUCCESS'){
                    $status = 18;
                    $time_in = date("Y-m-d H:i:s", strtotime("-23 hours"));
                    if($insurance->status == 2 || $insurance->is_chk == 2 || $insurance->create_time<$time_in){
                        $status = 23;
                    }
                    $insureWhere = [
                        'AND',
                        ['loan_id' => $loan->loan_id],
                        ['status' => [0,-1]]
                    ];
                    $insureInfo = Insure::find()->where($insureWhere)->orderBy('create_time desc')->one();
                    if (!empty($insureInfo)) {
                        $status = 19;
                    }
                }elseif (in_array($user_extend->status,['AUTHED','PRE-REMIT'])){
                    $status = 20;
                }else{
                    $status = 5;
                }
                break;
            case 7:
                $status = 26;
                $user_extend = $loan->loanextend;
                if($user_extend->status == 'REJECT'){
                    $status = 7;
                }
                break;
            case 8:
                if($loan->settle_type == 2){
                    $status = 27;
                }
                break;
            case 9:
                $user_extend = $loan->loanextend;
                if (isset($user_extend) && $user_extend->status != 'SUCCESS') {
                    $status = 23;
                }else{
                    $status = 25;
                }
                $cgRemitObj = $loan->cgRemit;
                if (!empty($cgRemitObj)) {
                    if($cgRemitObj->remit_status == 'WAITREMIT'){
                        $status = 21;
                    }elseif ($cgRemitObj->remit_status == 'WILLREMIT'){
                        $status = 22;
                    }elseif ($cgRemitObj->remit_status == 'DOREMIT'){
                        $status = 29;
                    }elseif ($cgRemitObj->remit_status == 'FAIL'){
                        $status = 24;
                    }
                }
                break;
        }
        return $status;
    }

    /**
     * 获取还款时间
     * @param $status
     * @param $end_date
     * @return false|string
     */
    public function getHuankuanTime($status, $end_date) {
        if ($status == 12 || $status == 13 || $status == 9 || $status == 11) {
            $huankuantime = date('n' . '月' . 'j' . '日', (strtotime($end_date) - 24 * 3600));
        } else {
            $huankuantime = '以短信推送时间为准';
        }
        return $huankuantime;
    }

    /**
     * 计算逾期费用
     * @return floor 逾期应还款金额
     */
    public function chaseAmount($r_time = '') {
        $loan_id = $this->loan_id;
        $totalamount = $this->getAllMoney($loan_id, 2);
        if (empty($r_time)) {
            $r_time = time();
        } else {
            $r_time = strtotime($r_time);
        }
        $days = floor(($r_time - strtotime($this->end_date)) / 24 / 3600) + 1;
        if ($days <= 90) {
            $chase_amount = $totalamount * pow((1 + 0.01), $days);
        } else {
            $num = $totalamount * pow((1 + 0.01), 90);
            $chase_amount = $num * pow((1 + 0.005), $days - 90);
            if ($chase_amount < $this->chase_amount) {
                $chase_amount = $this->chase_amount * (1 + 0.005);
            }
        }
        return floor($chase_amount * 100) / 100;
    }

    public function update_userLoan($condition) {
        if (empty($condition)) {
            return false;
        }
        $create_time = date('Y-m-d H:i:s');

        if (isset($condition['open_end_date'])) {
            $condition['open_end_date'] = $this->getOpenEndTime($this->open_start_date);
        }
        $condition['last_modify_time'] = $create_time;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    private function saveRenewLoan($parent_loan, $end_date, $number, $parent_loan_id) {
        $date = date('Y-m-d H:i:s');
        $start_date = date('Y-m-d 00:00:00');
        foreach ($parent_loan as $key => $value) {
            $renewloan[$key] = $value;
        }
        $renewloan['settle_type'] = 3;
        $renewloan['like_amount'] = 0;
        $renewloan['chase_amount'] = NULL;
        $renewloan['coupon_amount'] = 0;
        $renewloan['status'] = 9;
        $renewloan['number'] = $number;
        $renewloan['end_date'] = $end_date;
        $renewloan['parent_loan_id'] = $parent_loan_id;
        $renewloan['create_time'] = $date;
        $renewloan['last_modify_time'] = $date;
        $renewloan['start_date'] = $start_date;
        $renewloan['repay_time'] = NULL;
        unset($renewloan['loan_id']);
        $error = $this->chkAttributes($renewloan);
        if ($error) {
            return false;
        }
        $res = $this->save();
        if (!$res) {
            return null;
        }
        return $this->loan_id;
    }

    public function createRenewLoan($renew_pay_time, $renewalPaymentRecordId) {
        $renewModel = new Renew_amount();
        $renew = $renewModel->getRenew($this->loan_id, $renew_pay_time);
        if (empty($renew)) {
            return FALSE;
        }
        $number = $this->number + 1;
        $parent_loan_id = $this->parent_loan_id;
        $parent_loan = self::findOne($parent_loan_id);
        $days = $this->days + 1;
        $end_date = date('Y-m-d 00:00:00', strtotime("+$days days"));

        $new_loan_id = (new self())->saveRenewLoan($parent_loan, $end_date, $number, $parent_loan_id);
        if (!empty($new_loan_id)) {
            $condition = [
                'settle_type' => 2,
                'repay_time' => date('Y-m-d H:i:s'),
            ];
            $up = $this->update_userLoan($condition);
            if ($up) {
                //修改逾期表订单状态
                $over_due = OverdueLoan::find()->where(['loan_id' => $this->loan_id])->one();
                if (!empty($over_due)) {
                    $over_due->clearOverdueLoan();
                }
                $res = $this->changeStatus(8);
                //向可续期表添加记录
                //$renewModel->addExtension($parent_loan, $renew, $end_date, $new_loan_id);
                //更新续期支付记录
                $renewalPaymentRecordObj = Renewal_payment_record::findOne($renewalPaymentRecordId);
                $result = $renewalPaymentRecordObj -> update_batch(['new_loan_id'=>$new_loan_id]);
                if(!$result){
                    return FALSE;
                }

                return $res;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    //计算手续费、利息
    public function loan_Fee_new($amount, $days, $user_id, $term = 1) {
        $rate = User_rate::find()->where(['user_id' => $user_id, 'type' => 1])->one();
        $interest = 0.00098;
        $withdraw = 0.0;
        if (!empty($rate)) {
            $where = [
                'rate_id' => $rate->user_rate_id,
                'day' => $days,
                'type' => 1
            ];
            $rate_setting = Rate_setting::find()->where($where)->one();
            if (!empty($rate_setting)) {
                $interest = $rate_setting->interest / 100;
                $withdraw = $rate_setting->rate / 100;
            }
        }
        if ($term == 1) {
            $interest_fee = round($amount * $interest * $days * 100) / 100;
        } else {
            $goodsService = new GoodsService();
            $interest_fee = $goodsService->getInstallmentInterestFee($amount, $days, $term, $interest);
        }
        $withdraw_fee = round($amount * $withdraw * 100) / 100;
        return array(
            'interest_fee' => $interest_fee,
            'withdraw_fee' => $withdraw_fee,
            'fee' => $withdraw
        );
    }

    /**
     * 获取借款续期费用
     * @param $loan_id
     * @return float|int
     */
    public function getRenewalMoneyNew($loan_id) {
        $loan = self::findOne($loan_id);
        if (empty($loan)) {
            return 0;
        }
        $renewModel = new Renew_amount();
        $renewal = $renewModel->getRenew($loan->loan_id);
        if (empty($renewal)) {
            $renew_amount = $loan->withdraw_fee + $loan->amount * 0.2;
        } else {
            //重新计算续期金额
//            $rate_setting = (new User_rate())->getrateone($loan->user_id, $loan->days);
//            $with_fee = $rate_setting['rate'];
//            $renew_fee = $loan->amount * $renewal->renew + $loan->amount * $with_fee;
//            $renew_amount = $renew_fee;
            $renew_amount = $renewal->renew_fee;
        }
        return round($renew_amount, 2);
    }

    /**
     * 债权推送
     * @param type $loan_id
     */
    public function sendClaim($loanids) {
        if (empty($loanids) || !is_array($loanids)) {
            return FALSE;
        }
        $data = [];
        foreach ($loanids as $k => $loan_id) {
            $loan = self::findOne($loan_id);
            if (empty($loan)) {
                return FALSE;
            }
            $parent_id = $loan->parent_loan_id;
            $loan_extend = User_loan_extend::find()->where(['loan_id' => $parent_id])->one();
            $loan_fund = $loan_extend->fund;

            if ($loan_fund == 10) {
                $payModel = new Payaccount();
                $payaccount = $payModel->getPaysuccessByUserId($loan->user_id, 2, 1);
                $isAuth = $payModel->getPaysuccessByUserId($loan->user_id, 2, 3);
                if (!empty($payaccount)) {
                    $account_id = $payaccount->accountId;
                    $card_no = $payaccount->bank->card;
                } else {
                    $account_id = '';
                    $card_no = '';
                }
                if(!empty($isAuth)){
                    $orderId = $isAuth->orderId;
                }else{
                    $orderId = '';
                }
            } else {
                $account_id = '';
                $card_no = '';
                $orderId = '';
            }
            $data[$k] = [
                'loan_id' => $loan->loan_id,
                'user_id' => $loan->user_id,
                'amount' => $loan->amount,
                'days' => $loan->days,
                'fee_day' => !empty($loan->start_date) ? $loan->start_date : date("Y-m-d 00:00:00"),
                'fee' => $loan->is_calculation == 1 ? $loan->interest_fee : $loan->withdraw_fee + $loan->interest_fee,
                'repay_day' => !empty($loan->end_date) ? $loan->end_date : date("Y-m-d 00:00:00", strtotime("+$loan->days days")),
                'repay_type' => 1,
                'username' => $loan->user->realname,
                'mobile' => $loan->user->mobile,
                'identity' => $loan->user->identity,
                'company' => $loan->user->extend->company,
                'desc' => $loan->desc,
                'yield' => '0.0005',
                'tag_type' => $loan_fund == 10 ? 2 : 1,
                'accountid' => $account_id,
                'from' => 1,
//                'card_no' => $card_no,//提现银行卡号
//                'withdraw_money' => $loan_extend->userRemit->settle_amount,//提现金额
//                'cont_order_no' => $orderId,//预约提现签约订单号
//                'callback_url' => Yii::$app->params['getmoneynotify'],
                'total_callback_url' => Yii::$app->params['outmoneynotify'],
            ];
//            if($orderId == ''){
//                $data[$k]['total_callback_url'] = Yii::$app->params['outmoneynotify'];
//            }else{
//                $data[$k]['card_no'] = $card_no;//提现银行卡号
//                $data[$k]['withdraw_money'] = $loan_extend->userRemit->settle_amount;//提现金额
//                $data[$k]['cont_order_no'] = $orderId;//预约提现签约订单号
//                $data[$k]['callback_url'] = Yii::$app->params['getmoneynotify'];
//            }
        }
        $signData = (new \app\commonapi\ApiSign)->signData($data);
        $signData['_sign'] = base64_encode($signData['_sign']);
        if (SYSTEM_PROD) {
            //线上开放平台
            $url = "http://eros.yaoyuefu.com/api/loan";
        } else {
            //测试债匹平台
            $url = "http://testeros.yaoyuefu.com/api/loan";
        }
        $result = Http::interface_post($url, $signData);
        Logger::dayLog('testClaim/sendloanclaim', $signData, $result);
        $res = json_decode($result, TRUE);
        if (!empty($res) && isset($res['data'])) {
            $data_msg = json_decode($res['data'], TRUE);
            return $data_msg;
//            if ($data_msg[0]['rsp_code'] == '0000') {
//                return TRUE;
//            }
        }
        return [];
    }

    /**
     * 根据loan_id查询借款
     * @param type $loan_id
     */
    public function getLoanById($loan_id) {
        if (empty($loan_id) || !is_numeric($loan_id)) {
            return NULL;
        }
        return self::findOne($loan_id);
    }

    /**
     * 起息日计算（+days天）
     * @param int $days
     * @return bool
     */
    public function saveStarttime($days = 1) {
        try {
            $start_date = date('Y-m-d 00:00:00', (strtotime($this->start_date) + $days * 24 * 3600));
            $this->start_date = $start_date;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 计算应还款金额
     */
    public function getRepayment() {
        //区分是否是分期
        $business_type = [5, 6];
        if (in_array($this['business_type'], $business_type)) {
            $amount = $this->getStagesRepayAmount();
        } else {
            $amount = $this->getRepaymentAmount($this);
        }
        return $amount;
    }

    /**
     * 获取分期应还款金额
     * $returnarray false 返回总应还款金额
     *              true  返回每一期的应还款金额 和总还款金额 [10(该账单主键ID) => 100 ,20=>200,'total_amount' => 300]
     */
    public function getStagesRepayAmount($returnarray = false, $actual = false) {
        $loanId = $this->loan_id;
        $googsBill = GoodsBill::find()->where(['loan_id' => $loanId])->all();
//        $googsBill = $this->goodsbills;
        if (empty($googsBill)) {
            if ($returnarray == FALSE) {
                return $this->is_calculation == 1 ? $this->amount + $this->interest_fee : $this->amount + $this->withdraw_fee + $this->interest_fee;
            }
            return false;
        }

        $totalRepayAmount = 0;
        $result = [];

        foreach ($googsBill as $val) {
            $status = $val['bill_status'];
            if ($status == 8) {
                if ($actual) {
                    $result[$val['id']] = $val->actual_amount;
                } else {

                    $result[$val['id']] = 0;
                }
            } elseif ($status == 12) {
                $overdueloan = $val->overdueloan;
                if (!empty($overdueloan) && $overdueloan->chase_amount > 0) {
                    $result[$val['id']] = bcsub($overdueloan->chase_amount, $val->repay_amount, 2);
                } else {
                    $result[$val['id']] = bcsub($val->current_amount, $val->repay_amount, 2);
                }
            } else {
                $amount = bcsub($val->current_amount, $val->repay_amount, 2);
                $result[$val['id']] = $amount;
            }

            $totalRepayAmount += $result[$val['id']];
        }
        $result['total_amount'] = $this->getRealAmount($totalRepayAmount);
        if ($returnarray) {
            return $result;
        } else {
            return $result['total_amount'];
        }
    }

    public function getLoanStagesRepay($returnArray = true, $actual = false) {
        return $this->getStagesRepayAmount($returnArray, $actual);
    }

    /**
     * 获取分期应还款金额 本金 利息 滞纳金
     * $returnarray false 返回总应还款金额
     *              true  返回每一期的应还款金额 和总还款金额 [10(该账单主键ID) => 100 ,20=>200,'total_amount' => 300]
     */
    public function getStagesAllRepayAmount() {
//        $googsBill = $this->goodsbills;
        $loanId = $this->loan_id;
        $googsBill = GoodsBill::find()->where(['loan_id' => $loanId])->all();
        if (empty($googsBill)) {
            return false;
        }

        $totalRepayAmount = 0;
        $result = [];

        foreach ($googsBill as $val) {
            $status = $val['bill_status'];
            if ($status == 8) {
                continue;
//                $result[$val['id']] = [
//                    'principal' => 0,
//                    'interest'  => 0,
//                    'late_fee'  => 0,
//                    'total'     => 0,
//                    'status'    => 8,
//                ];
            } elseif ($status == 12) {
                $overdueloan = $val->overdueloan;
                if (!empty($overdueloan) && $overdueloan->chase_amount > 0) {
                    $overAmount = $overdueloan->chase_amount;
                    $total = bcsub($overdueloan->chase_amount, $val->repay_amount, 2);
                } else {
                    $overAmount = $val->current_amount;
                    $total = bcsub($val->current_amount, $val->repay_amount, 2);
                }
                $result[$val['id']] = [
                    'principal' => bcsub($val->principal, $val->over_principal, 2),
                    'interest' => bcsub($val->interest, $val->over_interest, 2),
                    'late_fee' => sprintf("%.2f", $overAmount - $val->principal - $val->interest - $val->over_late_fee),
                    'pleasetotal' => $total,
                    'total' => $total,
                    'status' => 12,
                ];
            } else {
                $amount = bcsub($val->current_amount, $val->repay_amount, 2);
                $result[$val['id']] = [
                    'principal' => bcsub($val->principal, $val->over_principal, 2),
                    'interest' => bcsub($val->interest, $val->over_interest, 2),
                    'late_fee' => 0,
                    'total' => $amount,
                    'pleasetotal' => $amount,
                    'status' => 9,
                ];
            }

            $totalRepayAmount += $result[$val['id']]['total'];
        }
        $result['total_amount'] = $this->getRealAmount($totalRepayAmount);
        return $result;
    }

    /**
     * 分期还款时  获取应分给每期本金，利息，滞纳金
     * $amount = 还款金额
     */
    public function getAssignRepayAmount($data, $amount) {
        $result = [];
        //给结果集赋默认值
        foreach ($data as $k => $v) {
            if ($k == 'total_amount') {
                continue;
            }
            $result[$k] = [
                'principal' => 0,
                'interest' => 0,
                'late_fee' => 0,
                'total' => 0,
                'pleasetotal' => $v['total'],
                'status' => $v['status'],
            ];

            if ($v['status'] == 12) {
                $res1[$k] = $v;
            }
            if ($v['status'] == 9) {
                $res2[$k] = $v;
            }
        }

        //优先计算逾期
        if (!empty($res1)) {
            foreach ($res1 as $latek => $latev) {
                //计算每期应扣滞纳金
                if ($amount <= 0) {
                    return $result;
                }
                if ($latev['late_fee'] > 0) {  //滞纳金未还完
                    if ($amount >= $latev['late_fee']) {  //还款金额 大于 滞纳金
                        $result[$latek]['late_fee'] = $latev['late_fee'];
                    } else { //还款金额小于滞纳金 ，滞纳金=还款金额
                        $result[$latek]['late_fee'] = $amount;
                    }
                    $amount = $amount - $result[$latek]['late_fee'];
                } else {  //未逾期
                    $result[$latek]['late_fee'] = 0;
                }
                $result[$latek]['total'] += $result[$latek]['late_fee'];
            }

            //计算每期应扣息
            foreach ($res1 as $intk => $intv) {
                if ($amount <= 0) {
                    return $result;
                }
                if ($intv['interest'] > 0) {  //已经逾期并且利息没结清
                    if ($amount >= $intv['interest']) {  //还款金额大于利息
                        $result[$intk]['interest'] = $intv['interest'];
                    } else { //还款金额小于利息 ，利息=还款金额
                        $result[$intk]['interest'] = $amount;
                    }
                    $amount = $amount - $result[$intk]['interest'];
                } else {  //利息已结清
                    $result[$intk]['interest'] = 0;
                }
                $result[$intk]['total'] += $result[$intk]['interest'];
            }

            //计算每期应扣本金
            foreach ($res1 as $prik => $priv) {
                if ($amount <= 0) {
                    return $result;
                }
                if ($priv['principal'] > 0) {  //本金未结清
                    if ($amount >= $priv['principal']) {  //还款金额大于本金
                        $result[$prik]['principal'] = $priv['principal'];
                    } else { //还款金额小于本金 ，本金=还款金额
                        $result[$prik]['principal'] = $amount;
                    }
                    $amount = $amount - $result[$prik]['principal'];
                } else {  //利息已结清
                    $result[$prik]['principal'] = 0;
                }
                $result[$prik]['total'] += $result[$prik]['principal'];
            }
        }
        //计算未逾期应扣本金，利息
        if (!empty($res2)) {
            foreach ($res2 as $loank => $loanv) {
                if ($amount <= 0) {
                    return $result;
                }
                if ($amount > $loanv['interest']) {
                    $result[$loank]['interest'] = $loanv['interest'];
                } else {
                    $result[$loank]['interest'] = $amount;
                }
                $amount = $amount - $result[$loank]['interest'];
                $result[$loank]['total'] += $result[$loank]['interest'];
                if ($amount > $loanv['principal']) {
                    $result[$loank]['principal'] = $loanv['principal'];
                } else {
                    $result[$loank]['principal'] = $amount;
                }
                $amount = $amount - $result[$loank]['principal'];
                $result[$loank]['total'] += $result[$loank]['principal'];
            }
        }

        if (!empty($result)) {
            //用户还多钱
            $maxKey = max(array_keys($result));
            if ($amount > 0) {
                $result[$maxKey]['principal'] += $amount;
                $result[$maxKey]['total'] += $amount;
            }
        }
        return $result;
    }

    private function getRealAmount($amount) {
        if ($amount * 10000 % 100 != 0) {
            return ceil($amount * 100) / 100;
        } else {
            return $amount;
        }
    }

    /**
     * 分期结清
     * @return bool
     */
    public function saveInstallmentRepay()
    {
        $time = date('Y-m-d H:i:s');
        $condition['status'] = 8;
        $condition['settle_type'] = 4;
        $condition['repay_time'] = $time;
        $condition['last_modify_time'] = $time;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 添加新记录（初始化last_modify_time、create_time）
     * @param $condition
     * @return bool|string
     */
    public function addUserLoanByData($condition)
    {
        if(empty($condition) || !is_array($condition)){
            return false;
        }
        $date = date('Y-m-d H:i:s');
        $condition['last_modify_time'] = $date;
        $condition['create_time'] = $date;
        $condition['version'] = 1;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        $res = $this->save();
        if (!$res) {
            return false;
        }
        $loan_id = Yii::$app->db->getLastInsertID();
        $loan_info = self::findOne($loan_id);
        //借款记录的添加，同时要添加flows记录
        $flow_condition = [
            'loan_id' => $loan_id,
            'loan_status' => $loan_info->status,
        ];
        $flowModel = new User_loan_flows();
        $flowModel->add_Record($flow_condition, -1);
        //添加loan_rate记录
        $user_rate = (new User_rate())->getrateone($condition['user_id'], $condition['days']);
        if (empty($user_rate)) {
            $rate = $condition['withdraw_fee'] / $condition['amount'] * 100;
            $interest = $condition['interest_fee'] / $condition['days'] / $condition['amount'] * 100;
        } else {
            $rate = $user_rate['rate'] * 100;
            $interest = $user_rate['interest'] * 100;
        }
        $loan_rate_condition = [
            'loan_id' => $loan_id,
            'user_id' => $condition['user_id'],
            'days' => $condition['days'],
            'rate' => $rate,
            'interest' => $interest,
        ];
        $loan_rate = new Loan_rate();
        $loan_rate->addloanrate($loan_rate_condition);
        return $loan_id;
    }

    /**
     * 在贷分期借款，根据loan_id
     * @param $loanId
     * @return array|bool|null|ActiveRecord
     */
    public function getInInstallmentByLoanId($loanId)
    {
        if(empty($loanId) || !is_numeric($loanId)){
            return false;
        }
        $status = ['5', '6', '9', '11', '12', '13'];
        $where = [
            'parent_loan_id' => $loanId,
            'status' => $status,
            'business_type' => [5,6]
        ];
        return self::find()->where($where)->one();
    }

    /*
     * 贷后 逾前提醒列表
     */
    public function getLoanBeforeList($loanIds){
        if(empty($loanIds) || !is_array($loanIds)) {
            return false;
        }
        return self::find()->where(['loan_id' => $loanIds])->all();
    }


    /**
     * 初始条件
     * @param $filter_where
     * @return int|\yii\db\ActiveQuery
     */
    private function repayWhere($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = self::find()->select(['DATE(repay_time) AS datetimes,count(1) AS nums', 'sum( amount ) AS money', 'sum( interest_fee ) AS fee', 'sum(coupon_amount ) AS coupon', 'sum( like_amount ) AS likes, days, loan_id']);

        if (!empty($filter_where['days'])){
            $result->andWhere(['days' => $filter_where['days']]);
        }
        if (!empty($filter_where['start_time'])){
            $result->andWhere(['>=', 'repay_time', $filter_where['start_time']. ' 00:00:00']);
        }
        if (!empty($filter_where['end_time'])){
            $result->andWhere(['<=', 'repay_time', $filter_where['end_time']. ' 23:59:59']);
        }
        $result->andWhere('repay_time < end_date');
        $result->andWhere(['>=', 'create_time', '2018-01-01']);
        $result->andWhere(['status'=>'8']);
        return $result;
    }
    /**
     * 逾期条数
     * @param $condition
     * @return bool
     */
    private function overdueWhere($condition)
    {
        if (empty($condition)){
            return false;
        }
        $result = self::find()
            ->from("yi_user_loan")
            ->select([
                'total'             => "count(DISTINCT(yi_user_loan.loan_id))",
                'loan_id'           => "yi_user_loan.loan_id",
                'days'              => "yi_user_loan.days",
                'end_date'          => "yi_user_loan.end_date",
                'create_time'       => "yi_user_loan.create_time",
                'all_interest_fee'  => "sum(yi_user_loan.interest_fee)",//利息
                'all_chase_amount'  => "sum(yi_user_loan.chase_amount)",//逾期费用
                'all_amount'        => "sum(yi_user_loan.amount)",//借款金额
                'all_actual_money'  => "sum(yi_loan_repay.actual_money)",//实际还款金额
                'amount'            => 'yi_user_loan.amount',
                'actual_money'      => 'yi_loan_repay.actual_money',
                'interest_fee'      => 'yi_user_loan.interest_fee',
                'chase_amount'      => 'yi_user_loan.chase_amount',
            ])
            ->leftJoin("yi_loan_repay","yi_loan_repay.loan_id=yi_user_loan.loan_id");
        $result->andWhere(['=', "yi_loan_repay.status", 1]);
        $result->andWhere(['in', 'yi_user_loan.status', [8,12, 13]]);
        //手机号
        if (!empty($condition['days'])){
            $result->andWhere(['=', 'yi_user_loan.days', $condition['days']]);
        }
        if (!empty($condition['start_time'])){
            $result->andWhere(['>=', 'yi_user_loan.end_date', $condition['start_time']. ' 00:00:00']);
        }
        if (!empty($condition['end_time'])){
            $result->andWhere(['<=', 'yi_user_loan.end_date', $condition['end_time']. ' 23:59:59']);
        }
        $result->groupBy("days, end_date");
        return $result;
    }

    /**
     * 计算时间区间条数
     * @param $filter_where
     * @return int
     */
    public function countRepayData($filter_where)
    {
        if (empty($filter_where)){
            return 0;
        }
        $result = $this->repayWhere($filter_where);
        return $result->groupBy('datetimes,days')->count();
    }

    /**
     * 获取时间区间的数据
     * @param $pages
     * @param $filter_where
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getAllData($pages, $filter_where)
    {
        if (empty($pages)){
            return false;
        }
        $result = $this->repayWhere($filter_where);

        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('datetimes desc')
            ->groupBy('datetimes, days')
            ->asArray()
            ->all();
    }

    /**
     * 获取时间区间的数据总数
     * @param $pages
     * @param $filter_where
     */
    public function getRepayTotal($filter_where){
        if (empty($filter_where)){
            return false;
        }
        $result = $this->repayWhere($filter_where);

        return $result->asArray()->one();
    }
    /**
     * 获取下载数据
     * @param $pages
     * @param $filter_where
     */
    public function getDownData($filter_where){
        if (empty($filter_where)){
            return 0;
        }
        $result = self::find();

        if (!empty($filter_where['days'])){
            $result->andWhere(['days' => $filter_where['days']]);
        }
        if (!empty($filter_where['times'])){
            $result->andWhere(['>=', 'repay_time', $filter_where['times']. ' 00:00:00']);
            $result->andWhere(['<=', 'repay_time', $filter_where['times']. ' 23:59:59']);
        }
        $result->andWhere('repay_time < end_date');
        $result->andWhere(['status'=>'8']);
        
        return $result->orderBy('repay_time desc')
            ->asArray()
            ->all();
    }

    /*获取逾期的数据
     * @param $pages
     * @param $condition
     * @return bool
     */
    public function getOverdueData($pages, $condition)
    {
        if (empty($pages) || empty($condition)){
            return false;
        }
        $resutl = $this->overdueWhere($condition);
        $resutl = $resutl->offset($pages->offset)
                        ->limit($pages->limit)
                        ->orderBy("yi_user_loan.end_date desc")
                        ->asArray()
                        ->all();
        return $resutl;
    }

    /*获取逾期的数据
     * @param $pages
     * @param $condition
     * @return bool
     */
    public function getOverdueDatas($pages, $condition)
    {
        if (empty($pages) || empty($condition)){
            return false;
        }
        $resutl = $this->overdueWhere($condition);
        $resutl = $resutl->orderBy("yi_user_loan.end_date desc")
            ->asArray()
            ->all();
        return $resutl;
    }

    /**
     * 获取逾期条数
     * @param $condition
     * @return int
     */
    public function getOverdueCount($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $resutl = $this->overdueWhere($condition);
        $total = $resutl->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 逾期已收统计（2017年）-- 实际还款
     * @param $condition
     * @return int
     */
    public function getReceivedRepay($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $resutl = $this->overdueWhere($condition);
        $total = $resutl->sum("actual_money");
        return empty($total) ? 0 : $total;
    }

    /**
     * 逾期已收统计（2017年）-- 应收利息
     * @param $condition
     * @return int
     */
    public function getReceivedInterest($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $resutl = $this->overdueWhere($condition);
        $total = $resutl->sum("interest_fee");
        return empty($total) ? 0 : $total;
    }

    /**
 * 逾期已收统计（2017年）-- 应收滞纳金
 * @param $condition
 * @return int
 */
    public function getReceivedOverdue($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $resutl = $this->overdueWhere($condition);
        $total = $resutl->sum("chase_amount");
        return empty($total) ? 0 : $total;
    }

    /**
     * 逾期已收统计（2017年）-- 应收本金
     * @param $condition
     * @return int
     */
    public function getReceivedMoney($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $resutl = $this->overdueWhere($condition);
        $total = $resutl->sum("amount");
        return empty($total) ? 0 : $total;
    }

    /**
     * 获得展期天数
     * @param $condition
     * @return int
     */
    public function getRenewalNum($loanId)
    {
        if (empty($loanId)){
            return 0;
        }
        $result = self::find()->select(['loan_id','parent_loan_id','number'])
                ->where(['parent_loan_id'=> $loanId])
                ->orderBy("loan_id desc")->asArray()->one();
        return $result;
    }
    
    public function getDownOverData($condition)
    {
        if (empty($condition)){
            return false;
        }
        $where_config = [
            'AND',
            ['=', 'yi_user_loan.days', ArrayHelper::getValue($condition, 'days')],
            ['>=', 'yi_user_loan.end_date', ArrayHelper::getValue($condition, 'start_time')],
            ['<=', 'yi_user_loan.end_date', ArrayHelper::getValue($condition, 'end_time')],
            ['in', 'yi_user_loan.status', [8,12, 13]],
            ['=', 'yi_loan_repay.status', 1]
        ];
        $data_set = self::find()
            ->from("yi_user_loan")
            ->select([
                'loan_id'       => 'yi_user_loan.loan_id', //订单号
                'mobile'        => 'yi_user.mobile', //手机号
                'fun'           => 'yi_user_remit_list.fund',//对应资方
                'start_date'    => 'yi_user_loan.start_date',
                'end_date'      => 'yi_user_loan.end_date',
                'amount'        => 'yi_user_loan.amount',
                'actual_money'  => 'group_concat(yi_loan_repay.actual_money)',
                'interest_fee'  => 'yi_user_loan.interest_fee',
                'chase_amount'  => 'yi_user_loan.chase_amount',
                'repay_time'    => 'yi_user_loan.repay_time',
                'days'          => 'yi_user_loan.days',
                'status'        => 'yi_user_loan.status',
            ])
            ->leftJoin("yi_user", 'yi_user.user_id=yi_user_loan.user_id')
            ->leftJoin("yi_user_remit_list", 'yi_user_remit_list.loan_id=yi_user_loan.loan_id')
            ->leftJoin("yi_loan_repay", 'yi_loan_repay.loan_id=yi_user_loan.loan_id')
            ->where($where_config)->groupBy("yi_user_loan.loan_id")->all();
        return $data_set;
    }



    #--------------------------------以下为对账 split----------------------------------------------------
    /*
     *      通过还款表 loan_id  获取 借款表
     * */
    public function getOneByData($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
//        return self::find()->select([self::tableName().'.*',Repay_coupon_use::tableName().'.*'])
             return self::find()->select([self::tableName().'.*'])
//            ->leftJoin(Repay_coupon_use::tableName(),Repay_coupon_use::tableName().'.loan_id='.self::tableName().'.loan_id')
            ->where([self::tableName().'.loan_id' => $loan_id])->one();
    }

    /*
     *  获取 parent_loan_id  的所有 数据
     * */
    public function getAllByData($parent_loan_id)
    {
        if (empty($parent_loan_id)){
            return false;
        }
        return self::find()->where(['parent_loan_id' => $parent_loan_id])->all();
    }

    /**
     *
     *  获取所有未到期所有总数
     *
     **/
    public function getAllcount($condition){

        if (empty($condition)){
            return 0;
        }
        $resutl = $this->beforeWhere($condition);
        $total = $resutl->count();
        return empty($total) ? 0 : $total;

    }

    /**
     * 未到期条数
     * @param $condition
     * @return bool
     */
    private function beforeWhere($condition)
    {
        if (empty($condition)){
            return false;
        }
        $user_tablename = User::tableName();
        $account_table = PayAccount::tableName();
        $time = date('Y-m-d');
        $result = self::find()
            ->from("yi_user_loan")
            ->select([
                //'total'             => "count(DISTINCT(yi_user_loan.loan_id))",
                'loan_id'           => "yi_user_loan.loan_id",
                'days'              => "yi_user_loan.days",
                'end_date'          => "yi_user_loan.end_date",
                'create_time'       => "yi_user_loan.create_time",
                'withdraw_fee'      => "yi_user_loan.withdraw_fee",
                'real_amount'       => 'yi_user_loan.real_amount',
                'mobile'            => 'yi_user.mobile',
                'accountId'         => 'yi_pay_account.accountId',

            ])
            ->leftJoin("yi_cg_remit","yi_cg_remit.loan_id=yi_user_loan.loan_id")
            ->leftJoin("yi_pay_account","yi_pay_account.user_id=yi_user_loan.user_id")
            ->leftJoin("yi_user","yi_user.user_id=yi_user_loan.user_id");

        //$result->andWhere(['>=', "yi_user_loan.end_date", $time]);
        $result->andWhere(['=', "yi_user_loan.status", 9]);
        $result->andWhere(['=', "yi_cg_remit.remit_status", "SUCCESS"]);
        $result->andWhere(['=', "yi_pay_account.type", 2]);
        $result->andWhere(['=', "yi_pay_account.step", 1]);
        $result->andWhere(['=', 'yi_pay_account.activate_result',1]);
        //业务类型
        if (!empty($condition['days'])){
            $result->andWhere(['=', 'yi_user_loan.days', $condition['days']]);
        }
        if (!empty($condition['loan_id'])){
            $result->andWhere(['=', 'yi_user_loan.loan_id', $condition['loan_id']]);
        }
        if (!empty($condition['mobile'])){
            $result->andWhere(['=', 'yi_user.mobile', $condition['mobile']]);
        }
        if (!empty($condition['start_time'])){
            $result->andWhere(['>=', 'yi_user_loan.end_date', $condition['start_time']. ' 00:00:00']);
        }
        if (!empty($condition['end_time'])){
            $result->andWhere(['<=', 'yi_user_loan.end_date', $condition['end_time']. ' 23:59:59']);
        }
        //$result->groupBy("days, end_date");
        return $result;
    }

    /**
     * 未到期统计数据
     * @param $pages
     * @param $condition
     * @return array|bool
     */
    public function getCollectData($pages, $condition)
    {
        if (empty($condition) || empty($pages)){
            return false;
        }
        $result = $this->beforeWhere($condition);
        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
    }

    /**
     * 未到期导出数据
     * @param $pages
     * @param $condition
     * @return array|bool
     */
    public function getCollectDatas($condition)
    {
        if (empty($condition)){
            return false;
        }
        $result = $this->beforeWhere($condition);
        return $result->asArray()
            ->all();
    }

}
