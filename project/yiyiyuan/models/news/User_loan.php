<?php

namespace app\models\news;

use app\common\ApiSign;
use app\common\Curl;
use app\commonapi\Apihttp;
use app\commonapi\Http;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\BaseModel;
use app\models\news\Loan_rate;
use app\models\news\User_loan_flows;
use app\models\news\User_rate;
use app\models\service\GoodsService;
use app\models\service\StageService;
use app\models\yyy\XhhApi;
use app\models\news\Selection;
use app\models\news\WarnMessageList;
use app\models\news\Selection_bankflow;
use Yii;
use app\commonapi\ApiSmsShop;
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
class User_loan extends BaseModel {

    public $shareurl;
    public $huankuan_amount;
    public $fee = 0.00098;
    public $with_fee = 0;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_loan';
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
	
	public function getParentremit() {
        return $this->hasOne(User_remit_list::className(), ['loan_id' => 'parent_loan_id'])->where(['remit_status'=>'SUCCESS']);
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

    public function getOverdueLoan() {
        return $this->hasOne(OverdueLoan::className(), ['loan_id' => 'loan_id']);
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

    public function getLoangoods() {
        return $this->hasMany(Loan_goods::className(), ['loan_id' => 'loan_id']);
    }

    public function getPushuserloan() {
        return $this->hasOne(PushUserLoan::className(), ['loan_id' => 'parent_loan_id']);
    }

    public function getUsercredit() {
        return $this->hasOne(User_credit::className(), ['loan_id' => 'loan_id']);
    }

    public function getLoanrate() {
        return $this->hasOne(Loan_rate::className(), ['loan_id' => 'loan_id']);
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
    public function getHaveinLoan($userid, $business_type = [1, 4, 5, 6, 9]) {
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
                ['business_type' => $business_type],
            ],
            [
                "AND",
                ['user_id' => $userid],
                ['prome_status' => 1],
            ],
        ];
        $user_loan = User_loan::find()->where($where)->one();
        return !empty($user_loan) ? $user_loan->loan_id : 0;
    }

    /*
     * 获取单个借款信息
     * @param $user_id
     * @param array $status
     * @param array $business_type
     * @return array|null|ActiveRecord
     * @author 王新龙
     * @date 2018/10/28 上午11:07 [9, 11, 12, 13], [1, 4, 5, 6, 11]
     */
    public function getLoan($user_id, $status = [5,6,9,11,12,13], $business_type = [1, 4, 5, 6, 9, 11]){
        if(empty($user_id)){
            return null;
        }
        $where['user_id'] = $user_id;
        if(!empty($status)){
            $where['status'] = $status;
        }
        if(!empty($business_type)){
            $where['business_type'] = $business_type;
        } 
        return User_loan::find()->where($where)->orderBy('loan_id desc')->one();
    }

    /**
     * 获取多个借款详情
     * @param $user_id
     * @param array $status
     * @param array $business_type
     * @return array|null|ActiveRecord[]
     * @author 王新龙
     * @date 2018/10/28 上午11:14
     */
    public function listLoan($user_id, $status = [5,6,9,11,12,13], $business_type = [1, 4, 5, 6, 9]){
        if(empty($user_id)){
            return null;
        }
        $where['user_id'] = $user_id;
        if(!empty($status)){
            $where['status'] = $status;
        }
        if(!empty($business_type)){
            $where['business_type'] = $business_type;
        }
        return User_loan::find()->where($where)->orderBy('loan_id desc')->all();
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
        //更新parent_loan_id
        $this->parent_loan_id = $this->loan_id;
        $this->save();
        //借款记录的添加，同时要添加flows记录
        $flow_condition = [
            'loan_id' => $this->loan_id,
            'loan_status' => $this->status,
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
            'loan_id' => $this->loan_id,
            'user_id' => $condition['user_id'],
            'days' => $condition['days'],
            'rate' => $rate,
            'interest' => $interest,
        ];
        $loan_rate = new Loan_rate();
        $loan_rate->addloanrate($loan_rate_condition);
        return $this->loan_id;
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
            if ($total != 0) {
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
            $url = Yii::$app->params['strategy'] . 'loan/loanone';
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
        $loan = User_loan::findOne($loans);
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
        $userloan = new User_loan();
        $interest_fee = round($amount * $dayratestr * $days, 2);
        $withdraw_fee = round($amount * $with_fee);
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
        $loan = User_loan::findOne($loans);
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

    /**
     * @param $user_id
     * @return array|bool|null|ActiveRecord
     * 查询该用户是否借款驳回
     */
    public function loanReject($user_id)
    {
        if(!$user_id){
           return false;
        }
        return self::find()->where(['user_id'=>$user_id,'status'=>[3,7]])->one();
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
        $count = User_loan::find()->where($where)->count();
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
        $query = User_loan::find()->where($where);
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
        $query = User_loan::find()->where($where);
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
        $query = User_loan::find()->where($where);
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
        //判断借款对应的最后一笔还款成功的记录，创建时间与还款回调时间是否跨天，如果跨天，按创建时间重新计息
//        if($this->type == 3 && $this->status == 9){
//            $lastSuccRepay = (new Loan_repay)->getLastSuccRepay($this->loan_id);
//            if(!empty($lastSuccRepay) && !empty($lastSuccRepay->repay_time)){
//                $repay_time = date('Y-m-d',strtotime($lastSuccRepay->repay_time));
//                $repay_create_time = date('Y-m-d',strtotime($lastSuccRepay->createtime));
//                if($repay_time != $repay_create_time){
//                    $res = $this->updateInterestFee(strtotime($lastSuccRepay->createtime));
//                }
//            }
//        }

        if ($this->is_calculation == 1) {
            $moneys = $this->amount + $this->getInterestFee();
        } else {
            $moneys = $this->amount + $this->getInterestFee() + $this->withdraw_fee;
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
        $loan = User_loan::findOne($loan_id);
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
     * @param $repay_mark 1总应还款金额 2未还总应还款金额
     * @return float|int|string
     */
    public function getRepaymentAmount($loanInfo, $repay_mark = 1, $goodbillids=[]) {
        if (in_array($loanInfo->business_type, [5, 6, 11])) {  //获取分期的应还款金额
            return $loanInfo->getStagesRepayAmount($returnArray = false,$actual =false,$goodbillids);
        } else {
            $total_amount = $this->getAllMoney($loanInfo->loan_id);
            if ($loanInfo->status != 8 || $repay_mark != 1) {
                $already_money = $loanInfo->getRepayAmount(2);
                if ($already_money != 0) {
                    $total_amount = bcsub($total_amount, $already_money, 2);
                }
            }
            //优惠卷
            $userLoanList = $this->listRenewal($loanInfo->loan_id);
            $loanIdArr = ArrayHelper::getColumn($userLoanList, 'loan_id');
            $repayCouponUseObj = RepayCouponUse::find()->where(['loan_id' => $loanIdArr, 'repay_status' => [-1, 1]])->orderBy('id asc')->one();
            if (!empty($repayCouponUseObj)) {
                $total_amount = bcsub($total_amount, $repayCouponUseObj->coupon_amount, 2);
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
        $loan = User_loan::findOne($loan_id);
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
            $loan_id = User_loan::find()->select(['loan_id'])->where(['parent_loan_id' => $parent_id])->asArray()->all();
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
    public function getVerifyUserLoanCount($userid) {
        $count = self::find()->where(['user_id' => $userid,'status' => [6,8,9,11,12,13]])->count();
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
     * @param $checkout_limit 1限时， 2限量， 3限时限量
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
            $user_loan = User_loan::find()
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
     * 信审后台风控评分占用，loan表的final_score
     * @return boolean
     */
    public function saveXinSehngFinalScore($loan,$score) {
        if(empty($score) || empty($loan)){
            return FALSE;
        }
        $data = ['final_score' => $score];
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $loan->save();
    }

    /**
     *
     * @param type $userid
     * @param type $status
     * @param type $business_type -1 表示查询所有类型借款
     * @return type
     */
    public function getUserLoan($userid, $status = '', $business_type = '') {
        if (empty($userid)) {
            return null;
        }
        $nowtime = date('Y-m-d H:i:s');
        $loan = User_loan::find()->where(['user_id' => $userid]);
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
     * 限制条件：被人工审核驳回（有驳回原因）后未进行修改借款资料的用户
     * 借款资料：单位信息（名称，电话，地址），联系人信息（姓名，关系，手机号），邮箱，通讯录（是否增加）
     * 限制时间：本次借款人工信审驳回后未修改资料两天（可调阈值）内发起借款即被系统延迟驳回
     * @param $user_id  用户user_id
     * @return bool
     */
    //@todo if 大括号
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
        } else {//限制时间为2天内
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
        //判断选填资料
        $selection0bj = (new Selection())->getNewestHistory($user_id);
        if (!empty($selection0bj)) {
            $selection_last_time = strtotime($selection0bj->last_modify_time);
            if ($last_modify_time < $selection_last_time) {
                return true;
            }
        }
        //判断信用卡
        $userBankObj = (new User_bank())->getCreditCardInfo($user_id);
        if (!empty($userBankObj)) {
            $bank_last_time = strtotime($userBankObj->last_modify_time);
            if ($last_modify_time < $bank_last_time) {
                return true;
            }
        }
        return false;
    }

    /**
     * 用户借款成功
     * @return array|bool|null|ActiveRecord
     */
    public function firstLoanUser($user_id) {
        if (empty($user_id))
            return false;
        $loan_info = User_loan::find()->where(['user_id' => $user_id])->andWhere(['in', 'status', array(8, 9, 11, 12, 13)])->one();
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
        $loan = User_loan::find()->where(['user_id' => $user_id , 'business_type' => [1,4,5,6,9]])->orderBy('loan_id desc')->one();
        if (empty($loan)) {
            return [];
        }
        if ($loan->status == 7 || ($loan->status == 3 && $loan->prome_status == 5)) {
            return $loan;
        }
        return [];
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
                if (in_array($loan->business_type, [5, 6, 11])) {
//                    $status = 12;
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
                if (in_array($loan->business_type, [5, 6, 11])) {
                    $timeIn = date('Y-m-d H:i:s', strtotime('-2 minutes'));
//                    $billRepayWhere = [
//                        'AND',
//                        ['loan_id' => $loan->loan_id],
//                        ['status' => -1]
//                    ];
                    $billRepayWhere = [
                        'and',
                        ['=', 'loan_id',$loan->loan_id],
                        ['=','status',3],
                        ['>', 'last_modify_time',$timeIn],
                    ];
                    $billRepayInfo = BillRepay::find()->where($billRepayWhere)->orderBy('createtime desc')->one();
                    if (!empty($billRepayInfo)) {
                        $status = 11;
                    }
                }
                $cgRemitObj = $loan->cgRemit;
                if (!empty($cgRemitObj)) {
                    if ($cgRemitObj->remit_status == 'WILLREMIT') {
                        $pushNotModel = new Push_not_withdrawals();
                        $notData = $pushNotModel->getByLoanId($cgRemitObj->loan_id);
                        if (!empty($notData)) {
                            $status = 23;
                            $time = strtotime(date('Y-m-d 23:59:59', strtotime($notData->create_time))) - time();
                        } else {
                            if (date('Y-m-d', (strtotime($loan->end_date) - 24 * 3600)) == date('Y-m-d')) {
                                $status = 9;
                            } else {
                                $status = 18;
                            }
                        }
                    } elseif (in_array ($cgRemitObj->remit_status,['DOREMIT','FAIL'] )) {
                        $status = 19;
                    }
                }
                break;
            case 6:
                //20：待发起提现 21：待安全认证 22：资金匹配中
                $user_extend = $loan->loanextend;
                if (in_array($user_extend->status, ['AUTHED', 'PRE-REMIT'])) {
                    $status = 22;
                } elseif ($user_extend->status == 'TB-SUCCESS') {
                    $status = 5;
                    if (Keywords::h5Open() == 1) {
                        $push_info = (new Push_yxl())->getYxlInfo($loan->loan_id, $type = 1, $loan_status = 3);
                        if (!empty($push_info) && $push_info->notify_status == 1) {
                            $status = 21;
                        }
                    }
                } else {
                    $status = 5;
                }
                break;
        }
        $views = [
            '1' => [5, 6],
            '2' => [9, 11],
            '3' => [12, 13],
            '4' => [18, 19],
            '5' => [20, 21, 22, 23],
        ];

        $view = 1;
        foreach ($views as $key => $val) {
            if (in_array($status, $val)) {
                $view = $key;
                break;
            }
        }
        $return = [
            'status' => $status,
            'view' => $view,
        ];
        if ($status == 23) {
            $return['time'] = $time > 0 ? $time : 0;
        }
        Logger::dayLog('api/loan/userloan','借款数据weixin',$status);
        return $return;
    }

    public function loan_Fee($amount, $days) {
        $loan = new User_loan();
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
    public function getLoanStatus($loan) {
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
                if ($user_extend->status == 'TB-SUCCESS') {
                    $status = 19;
                } elseif (in_array($user_extend->status, ['AUTHED', 'PRE-REMIT'])) {
                    $status = 23;
                } else {
                    $status = 5;
                }
                break;
            case 7:
                $status = 26;
                $user_extend = isset($loan->loanextend) ? $loan->loanextend : "";
                if (!empty($user_extend) && $user_extend->status == 'REJECT') {
                    $status = 7;
                }
                break;
            case 8:
                if ($loan->settle_type == 2) {
                    $status = 27;
                }
                break;
            case 9:
                $user_extend = isset($loan->loanextend) ? $loan->loanextend : "";
                if (!empty($user_extend) && $user_extend->status != 'SUCCESS') {
                    $status = 23;
                } else {
                    $status = 25;
                }
                $cgRemitObj = $loan->cgRemit;
                if (!empty($cgRemitObj)) {
                    if ($cgRemitObj->remit_status == 'WAITREMIT') {
                        $status = 23;
                    } elseif ($cgRemitObj->remit_status == 'WILLREMIT') {
                        $status = 22;
                    } elseif ($cgRemitObj->remit_status == 'DOREMIT') {
                        $status = 29;
                    } elseif ($cgRemitObj->remit_status == 'FAIL') {
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
        $loanModel = User_loan::findOne($loan_id);
        if ($loanModel->end_date >= '2019-01-10') {//TODO 开始时间修改
            $allMoney = $loanModel->getAllMoney($loanModel->loan_id, 2);
            $chase_amount = 0.02 * $this->amount + 0.00098 * $this->amount * $days + $allMoney;
        } else {
            if ($days <= 90) {
                $chase_amount = $totalamount * pow((1 + 0.01), $days);
            } else {
                $num = $totalamount * pow((1 + 0.01), 90);
                $chase_amount = $num * pow((1 + 0.005), $days - 90);
                if ($chase_amount < $this->chase_amount) {
                    $chase_amount = $this->chase_amount * (1 + 0.005);
                }
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

    public function saveRenewLoan($parent_loan, $end_date, $number, $parent_loan_id,$days) {
        $date = date('Y-m-d H:i:s');
        $start_date = date('Y-m-d 00:00:00');
        foreach ($parent_loan as $key => $value) {
            $renewloan[$key] = $value;
        }
        $renewloan['days'] = $days-1;
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
        $parent_loan_id = $this->parent_loan_id;
        $parent_loan = User_loan::findOne($parent_loan_id);
        $oRenewreslut = Renew_record::find()->where(['loan_id' => $this->loan_id])->one();
        if(in_array($this->days, [21,28,30])){
             $days = 56 + 1;
        }else{
             $days = $this->days + 1;  
        }
        if (!empty($oRenewreslut)) {
            $new_loan_id = $oRenewreslut->loan_id_new;
            $mark = 1;
            $new_loan = User_loan::findOne($new_loan_id);
            $end_date = $new_loan->end_date;
        } else {
            $number = $this->number + 1;
            $end_date = date('Y-m-d 00:00:00', strtotime("+$days days"));
            $new_loan_id = (new User_loan())->saveRenewLoan($parent_loan, $end_date, $number, $parent_loan_id,$days);
            $mark = 2;
        }
        if (!empty($new_loan_id)) {
            if ($mark == 1) { //新流程，授权时已生new_loan_id, status由4更改为9
                $oNewloan = User_loan::find()->where(['loan_id' => $new_loan_id])->one();
                $oNewloan->changeStatus(9);
                $oNewloan->saveEndtime($oNewloan->days);
            }
            
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
                $renew_res = $renewModel->addExtension($parent_loan, $renew, $end_date, $new_loan_id);
                Logger::dayLog('payment/add', $new_loan_id, $renew_res, 'uu', $renewalPaymentRecordId);
                //更新续期支付记录
//                $insureModel = Insure::findOne($renewalPaymentRecordId);
//                if (!$insureModel) {
//                    return false;
//                }
//                $result = $insureModel->updateData(['new_loan_id' => $new_loan_id]);
//                if (!$result) {
//                    return false;
//                }
                $renewalPaymentRecordObj = Renewal_payment_record::findOne($renewalPaymentRecordId);
                $result = $renewalPaymentRecordObj->update_batch(['new_loan_id' => $new_loan_id]);
                Logger::dayLog('payment/addrecord', $new_loan_id, $result);
                if (!$result) {
                    return FALSE;
                }
                
                //先花商城续期成功短信通知 $loaninfo->business_type==9 
                $oUser = User::findOne($parent_loan->user_id);
                if(!empty($oUser) && !empty($oUser->mobile)){
                    $newLoan = User_loan::find()->where(['loan_id' => $new_loan_id])->one();
                    if(!empty($newLoan) && $newLoan->business_type==9 ){
                        $end_time = date('Y-m-d',strtotime('-1 days',strtotime($newLoan->end_date)));
                        (new ApiSmsShop())->sendXuqiSuccess($oUser->mobile, $end_time, 46); //短信
                        (new WarnMessageList())->saveWarnMessage($parent_loan,1,14,$end_time); //app push
                    }
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
        $feeOpen = Keywords::feeOpen();
        if ($feeOpen == 2) {
            $interest = 0.00098;
        } else {
            $interest = 0.00098;
        }

        $withdraw = 0.0;
        if (!empty($rate)) {
            $where = [
                'rate_id' => $rate->user_rate_id,
                'day' => $days,
                'type' => 1
            ];
            $rate_setting = Rate_setting::find()->where($where)->one();
            if (!empty($rate_setting)) {
                if ($feeOpen == 2) {
                    $interest = bcdiv($rate_setting->interest / 100, 2, 5);
                } else {
                    $interest = $rate_setting->interest / 100;
                }

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
     * 计算手续费、利息
     * @param $amount
     * @param $interest
     * @param $days
     * @param $user_id
     * @param int $term
     * @param int $loan_type    1：根据开关半期/全息    2：强制全息
     * @return array
     * @date 2018/9/13 19:30
     */
    public function loan_Fee_rate_new($amount, $interest, $days, $user_id, $term = 1,$loan_type = 1, $is_installment = FALSE) {
        if (empty($interest)) {
            $interest = 0.00098;
        }
        $feeOpen = Keywords::feeOpen();
        $withdraw = 0;
        if ($feeOpen == 2 && $loan_type == 1 && !$is_installment) {
            $ex_status=self::getcreditUserloan($user_id);
            if(!$ex_status){
                $interest = bcdiv($interest, 2, 5);
            }
        }
        if (!$is_installment) {
            $interest_fee = round($amount * $interest * $days * 100) / 100;
        } else {
            $interest_fee = (new StageService())->getInterestFee($amount,$term,$interest);
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
        $desc = [
            '个人或家庭消费' => '购买生活用品',
            '其他' => '购买生活用品',
            '其它' => '购买生活用品',
            '学习' => '培训/教育消费',
            '旅游' => '个人旅游消费',
            '消费' => '日常生活消费',
            '物流运输' => '物流运输消费',
            '租房' => '房屋租赁',
            '购买原材料' => '购买原材料消费',
            '购买家具或家电' => '购买家具/家电',
            '购买设备' => '购买设备消费',
            '资金周转' => '购买生活用品',
            '进货' => '进货消费',
            '购买服饰'=>'购买生活用品',
            '购买食品'=>'购买生活用品',
            '购买电子产品'=>'购买家具/家电',
        ];
        $data = [];
        foreach ($loanids as $k => $loan_id) {
            $loan = self::findOne($loan_id);
            if (empty($loan)) {
                return FALSE;
            }
            
            $days = 56;
            $tag_type = 3;
            $stage_type = 1;
            $period_num = 1;
            if( in_array($loan->business_type, [5, 6, 11]) ){
                $tag_type = 10;
                $stage_type = 2;
                $period_num = empty($loan->usercredit)? 1: $loan->usercredit->period;
                $days = 30*$period_num;
                $getPeriodLoanRes = $this->getPeriodLoanData($loan,$period_num);
                if(!$getPeriodLoanRes){
                    Logger::dayLog('testClaim/sendloanclaim', '借款数据与分期规则冲突');
                    return false;
                }
            }
            $parent_id = $loan->parent_loan_id;
            $loan_extend = User_loan_extend::find()->where(['loan_id' => $parent_id])->one();
            $loan_fund = $loan_extend->fund;
            $account_id = '';
            $card_no = '';
            $orderId = '';
            if ($loan_fund == 10) {
                $payModel = new Payaccount();
                $payaccount = $payModel->getPaysuccessByUserId($loan->user_id, 2, 1);
                $isAuth = $payModel->getPaysuccessByUserId($loan->user_id, 2, 3);
                if (!empty($payaccount)) {
                    $account_id = $payaccount->accountId;
                    $card_no = $payaccount->bank->card;
                }
                if (!empty($isAuth)) {
                    $orderId = $isAuth->orderId;
                }
            }
            
            $data[$k] = [
                'loan_id' => $loan->loan_id,
                'user_id' => $loan->user_id,
                'amount' => $loan->amount,
                'days' => $days,
                'true_days' => $loan->days,
                'fee_day' => !empty($loan->start_date) ? $loan->start_date : date("Y-m-d 00:00:00"),
                'fee' => $loan->is_calculation == 1 ? $loan->interest_fee : $loan->withdraw_fee + $loan->interest_fee,
                'coupon_amount' => !empty($loan->coupon_amount) ? $loan->coupon_amount : 0,
                'repay_day' => !empty($loan->end_date) ? $loan->end_date : date("Y-m-d 00:00:00", strtotime("+$loan->days days")),
                'repay_type' => 1,
                'username' => $loan->user->realname,
                'mobile' => $loan->user->mobile,
                'identity' => $loan->user->identity,
                'company' => $loan->user->extend->company,
                'desc' => isset($desc[$loan->desc]) ? $desc[$loan->desc] : '购买生活用品',
                'yield' => '0.0005',
                'tag_type' => $tag_type, //$loan_fund == 10 ? 3 : 1,  //单期传3  分期传10
                'accountid' => $account_id,
                'from' => 1,
//                'card_no' => $card_no,//提现银行卡号
//                'withdraw_money' => $loan_extend->userRemit->settle_amount,//提现金额
//                'cont_order_no' => $orderId,//预约提现签约订单号
//                'callback_url' => Yii::$app->params['getmoneynotify'],
                'total_callback_url' => Yii::$app->params['outmoneynotify'],
                'stage_type' => $stage_type,  //单期1 分期2
                'period_num' => $period_num,  //分期期数
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
        Logger::dayLog('testClaim/sendloanclaim', '推送的数据', $data);
        $signData = (new \app\commonapi\ApiSign)->signData($data);
        $signData['_sign'] = base64_encode($signData['_sign']);
        $url = Yii::$app->params['exchange_url'] . 'loan';
        $result = Http::interface_post($url, $signData);
        Logger::dayLog('testClaim/sendloanclaim','推送结果', $signData, $result);
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
     * 分期数据是否正常
     * @param type $loans
     */
    public function getPeriodLoanData($loan,$period_num){
        $amount = empty($loan->amount) ? '' : $loan->amount;
        $days = empty($loan->days) ? '' : ($loan->days)/$period_num;
        $credit_subject = [
             'AMOUNT' => $amount,
             'period' => $period_num,
             'DAYS' =>  $days,
         ];
         $period_result = (new User_credit())->getPeriodReject($credit_subject);
         if( !$period_result ){
             return false;
         }
         return true;
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
    public function saveStarttime($remit_time, $days) {
        try {
            $start_date = date('Y-m-d 00:00:00', (strtotime($remit_time)) + 24 * 3600);
            $end_time = date('Y-m-d 00:00:00', (strtotime($start_date) + $days * 24 * 3600));
            $this->start_date = $start_date;
            $this->end_date = $end_time;
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
    public function getStagesRepayAmount($returnarray = false, $actual = false, $goodbillids=[]) {
        if(empty($goodbillids)){
            $loanId = $this->loan_id;
            $googsBill = GoodsBill::find()->where(['loan_id' => $loanId])->all();
        }else{
            $googsBill = GoodsBill::find()->where(['id' => $goodbillids])->all();
        }

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
            } else {
                $amount = bcsub($val->actual_amount, $val->repay_amount, 2);
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
    public function saveInstallmentRepay() {
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
     * 正常分期结清
     * @return bool
     */
    public function saveInstallmentRepayNormal() {
        $time = date('Y-m-d H:i:s');
        $condition['status'] = 8;
        $condition['repay_time'] = $time;
        $condition['last_modify_time'] = $time;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }
    /**
     * 未提现数据结清
     * @return bool
     */
    public function saveWithRepay() {
        $time = date('Y-m-d H:i:s');
        $condition['status'] = 8;
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
    public function addUserLoanByData($condition) {
        if (empty($condition) || !is_array($condition)) {
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
        $loan_info = User_loan::findOne($loan_id);
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
    public function getInInstallmentByLoanId($loanId) {
        if (empty($loanId) || !is_numeric($loanId)) {
            return false;
        }
        $status = ['5', '6', '9', '11', '12', '13'];
        $where = [
            'parent_loan_id' => $loanId,
            'status' => $status,
            'business_type' => [5, 6]
        ];
        return self::find()->where($where)->one();
    }

    /*
     * 贷后 逾前提醒列表
     */

    public function getLoanBeforeList($loanIds) {
        if (empty($loanIds) || !is_array($loanIds)) {
            return false;
        }
        return self::find()->where(['loan_id' => $loanIds])->all();
    }

    /**
     * 修改利息
     * @param $time 重新计算并更新利息依据时间
     * @return bool
     */
    public function updateInterestFee($time) {
        $days = ceil(($time - strtotime($this->start_date)) / 24 / 3600);
        $interest_fee = ($this->days + $days) * 0.00049 * $this->amount;
        $this->interest_fee = $interest_fee;
        $this->last_modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * 计算利息
     * @param $time 重新计算利息依据时间
     * @return int|string
     */
    public function getInterestFee() {
        $interest_fee = $this->interest_fee;
        if (!in_array($this->business_type,[5, 6, 11]) && $this->type == 3 && $this->settle_type != 3) {
            if ($this->status == 6) {
                $interest_fee = $this->days * 0.00049 * $this->amount;
            } else {
                $days = ceil((time() - strtotime($this->start_date)) / 24 / 3600);
                $days = $days >= $this->days ? $this->days : $days;
                $interest_fee = ($this->days + $days) * 0.00049 * $this->amount;
            }
        }
        return $interest_fee;
    }

    /**
     * 获取展期次数
     * @param $loanId
     * @return bool|int
     */
    public function getRenewalNum($loanId) {
        if (empty($loanId)) {
            return false;
        }
        $renNew = self::find()->where(['parent_loan_id' => $loanId])->count();
        $num = $renNew - 1;
        return $num;
    }

    /**
     * 获取订单关联所有展期记录
     * @param $loanId
     * @return array|null|ActiveRecord[]
     */
    public function listRenewal($loanId) {
        if (empty($loanId)) {
            return null;
        }
        $userLoanObj = self::find()->where(['loan_id' => $loanId])->one();
        $userLoanList = null;
        if (!empty($userLoanObj)) {
            $userLoanList = self::find()->where(['parent_loan_id' => $userLoanObj->parent_loan_id])->all();
        }
        return $userLoanList;
    }

    /**
     * 修改资料后可借
     * @param $mobile
     * @param $last_time
     * @return bool
     * @author 王新龙
     * @date 2018/7/2 12:00
     */
    public function getBorrowingByTime($user_id, $last_time) {
        //初贷（未成功借款）用户
        $first_loan_user = $this->firstLoanUser($user_id);
        if (!empty($first_loan_user)) {
            return true;
        }
        if (date('Y-m-d H:i:s', strtotime($last_time . '+24 hours')) < date('Y-m-d H:i:s')) {
            return true;
        }
        $last_time = strtotime($last_time);
        //判断联系人
        $favorite_contacts = new Favorite_contacts();
        $favorite_info = $favorite_contacts->getFavoriteByUserId($user_id);
        if (!empty($favorite_info)) {
            $favorite_last_time = strtotime($favorite_info['last_modify_time']);
            if ($last_time < $favorite_last_time) {
                return true;
            }
        }
        //判断历时记录
        $User_history_info = new User_history_info();
        $history_info = $User_history_info->newestHistory($user_id);
        if (!empty($history_info)) {
            $history_last_time = strtotime($history_info->create_time);
            if ($last_time < $history_last_time) {
                return true;
            }
        }
        //判断选填资料
        $selection0bj = (new Selection())->getNewestHistory($user_id);
        if (!empty($selection0bj)) {
            $selection_last_time = strtotime($selection0bj->last_modify_time);
            if ($last_time < $selection_last_time) {
                return true;
            }
        }
        //判断信用卡
        $userBankObj = (new User_bank())->getCreditCardInfo($user_id);
        if (!empty($userBankObj)) {
            $bank_last_time = strtotime($userBankObj->last_modify_time);
            if ($last_time < $bank_last_time) {
                return true;
            }
        }
        return false;
    }

    /**
     * 修改资料后可重新评测 310
     * @param $mobile
     * @param $last_time
     * @return bool
     */
    public function getUserCreditByTime($user_id, $last_time) {
        //初贷（未成功借款）用户
//        $first_loan_user = $this->firstLoanUser($user_id);
//        if (!empty($first_loan_user)) {
//            return true;
//        }

        $last_time = strtotime($last_time);
        //超过24小时
        if (date('Y-m-d H:i:s', $last_time + 24 * 3600) < date('Y-m-d H:i:s')) {
            return true;
        }
        //判断选填资料
        $selection0bj = (new Selection())->getNewestHistory($user_id);
        if (!empty($selection0bj)) {
            $selection_last_time = strtotime($selection0bj->last_modify_time);
            if ($last_time < $selection_last_time) {
                return true;
            }
        }
        //判断信用卡
        $userBankObj = (new User_bank())->getCreditCardInfo($user_id);
        if (!empty($userBankObj)) {
            $bank_last_time = strtotime($userBankObj->last_modify_time);
            if ($last_time < $bank_last_time) {
                return true;
            }
        }
        return false;
    }

    /**
     * 修改资料后可点击立即加速 330
     * @param $mobile
     * @param $last_time
     * @return bool
     */
    public function getUserInfoByTime($user_id) {
        $key='last_time'.$user_id;
        $last_time = Yii::$app->redis->get($key);
        if(empty($last_time)){
            $res = Yii::$app->redis->setex($key, 86400, time());
            return false;
        }
        //判断选填资料
        $selection0bj = (new Selection())->getNewestHistory($user_id);
        if (!empty($selection0bj)) {
            $selection_last_time = strtotime($selection0bj->last_modify_time);
            if ($last_time < $selection_last_time) {
                $res = Yii::$app->redis->setex($key, 86400, time());
                return true;
            }
        }
        //判断选填资料-银行卡流水
//        $selectionBankflow0bj = (new Selection_bankflow())->getNewestHistory($user_id);
//        if (!empty($selectionBankflow0bj)) {
//            $selection_bankflow_last_time = strtotime($selectionBankflow0bj->last_modify_time);
//            if ($last_time < $selection_bankflow_last_time) {
//                $res = Yii::$app->redis->setex($key, 86400, time());
//                return true;
//            }
//        }
        //判断信用卡
        $userBankObj = (new User_bank())->getCreditCardInfo($user_id);
        if (!empty($userBankObj)) {
            $bank_last_time = strtotime($userBankObj->last_modify_time);
            if ($last_time < $bank_last_time) {
                $res = Yii::$app->redis->setex($key, 86400, time());
                return true;
            }
        }
        return false;
    }

    /**
     * 获取指定条件下的借款记录
     * @param type $content
     */
    public function getHaveRepayLoan($content) {
        if (empty($content)) {
            return NULL;
        }
        $oResult = static:: find()->where($content)->all();
        if (empty($oResult)) {
            return NULL;
        } else {
            return $oResult;
        }
    }

    /**
     * 查询是否复贷
     * @param type $user_id
     * @return obj
     */
    public function isRepeatUser($user_id) {
        $where = [
            'AND',
            ['user_id' => $user_id],
            ['status' => 8],
            ['IN', 'business_type', [1, 4, 5, 6]],
            ['NOT IN', 'settle_type', [2]],
        ];
        $loans = User_loan::find()->where($where)->count();
        return $loans;
    }

    public function getLastRejectLoan($user_id) {
        if (empty($user_id)) {
            return false;
        }
        return self::find()->where(['user_id' => $user_id, 'status' => [3, 7]])->orderBy('loan_id desc')->one();
    }

    public function getValid($userId, $type = 1) {
        if (empty($userId)) {
            return 1;
        }
        $selectionObj = (new Selection())->getByUserIdAndTpey($userId, $type);
        if (empty($selectionObj)) {
            return 1;
        }
        if ($selectionObj->process_code == '10002') {
            return 3;
        }
        if ($selectionObj->process_code != '10008') {
            return 1;
        }

        if ($selectionObj->process_code == '10008' && (date('Y-m-d H:i:s', strtotime('-3 month')) >= $selectionObj->last_modify_time)) { //有效期3个月
            return 1;
        }

        return 2;
    }

    /**
     * 选填资料认证状态
     * @param type $user_id
     * @return boolean  只要有一项未完成就返回true
     */
    public function getSelectionStatus($user_id) {
        $selection_status = false;
        $array['edu_valid'] = $this->getValid($user_id, 1);
        $array['social_valid'] = $this->getValid($user_id, 2);
        $array['fund_valid'] = $this->getValid($user_id, 3);
        $userbank = User_bank::find()->where(['user_id' => $user_id, 'status' => 1, 'type' => 1])->one();
        $array['bank_valid'] = empty($userbank) ? 1 : 2;

        if (in_array(1, $array)) {
            $selection_status = true;
        }
        return $selection_status;
    }

    /**
     * 选填资料认证状态 310版本
     * @param type $user_id
     * @return boolean  只要有一项未完成就返回true
     */
    public function getSelectionStatusNew($user_id) {
        $selection_status = false;
        $array['edu_valid'] = $this->getValidByType($user_id, 1);
        $array['social_valid'] = $this->getValidByType($user_id, 2);
        $array['fund_valid'] = $this->getValidByType($user_id, 3);
        $userbank = User_bank::find()->where(['user_id' => $user_id, 'status' => 1, 'type' => 1])->one();
        $array['bank_valid'] = empty($userbank) ? 1 : 2;
        $array['jd_valid'] = $this->getValidByType($user_id, 4);
//        $array['bankflow_valid'] = $this->getValidByType($user_id, '101');
        $array['bankflow_valid'] = $this->getValidByType($user_id, 7);
        $array['taobao_valid'] = $this->getValidByType($user_id, 6);
        if (in_array(1, $array) || in_array(4, $array) ) {
            $selection_status = true;
        }
        return $selection_status;
    }

    /**
     * 310版本 可借天数
     * @return type
     */
    public function getMaxLoanDays($user_id = '') {
        if (empty($user_id)) {
            return [56];
        }
        $days = 56;
        $user_credit = (new User_credit())->checkYyyUserCredit($user_id);
        if (!empty($user_credit)) {
            if (in_array($user_credit['user_credit_status'], [1, 6])) {
                $o_user = (new User())->getUserinfoByUserId($user_id);
                $credit_result = (new Apihttp())->getUserCredit(['mobile' => $o_user['mobile']]);
                if (!empty($credit_result)) {
                    if ($credit_result['user_credit_status'] == 5) {
                        $result_subject = json_decode($credit_result['result_subject'], true);
                        if (!empty($result_subject)) {
                            $days = (isset($result_subject['DAYS']) && !empty($result_subject['DAYS'])) ? ($result_subject['DAYS']) : 56;
                        }
                    }
                }
            } else {
                $days = $user_credit['days'];
            }
        }
        return [$days];
    }

    /**
     * 判断选填资料状态及社保和公积金有效期3个月  310版本
     * @param type $userId
     * @param type $type 1：学历 2：社保 3：公积金 4:京东 （101：银行流水,已弃用） 7银行流水
     * @return int 1:未认证 2：已认证  3认证中 4：已过期
     */
    public function getValidByType($userId, $type = 1) {
        if (empty($userId)) {
            return 1;
        }
        $selectionObj = (new Selection())->getByUserIdAndTpey($userId, $type);
        if (empty($selectionObj)) {
            return 1;
        }
        if ($selectionObj->process_code == '10002') {
            return 3;
        }
        if ($selectionObj->process_code != '10008') {
            return 1;
        }
        if ( in_array($type, [2,3,4,6,7]) ) { //社保 公积金 京东 淘宝 银行流水 判断3个月有效期
            if ($selectionObj->process_code == '10008' && (date('Y-m-d H:i:s', strtotime('-3 month')) >= $selectionObj->last_modify_time)) { //有效期3个月
                return 4;
            }
        }
        return 2;
    }
   
    /**
     * 获取loan_no生成方法 310版本
     * @return int
     */
    public function getLoanNo($userid) {
        $suffix = $userid . rand(100000, 999999);
        $loan_no = date("YmdHis") . $suffix;
        return $loan_no;
    }

    public function createInvalidloan() {
        $renewModel = new Renew_amount();
        $renew = $renewModel->getRenew($this->loan_id);
        if (empty($renew)) {
            return FALSE;
        }
        $number = $this->number + 1;
        $parent_loan_id = $this->parent_loan_id;
        $parent_loan = User_loan::findOne($parent_loan_id);

        $days = $this->days + 1;
        if (in_array($this->days, [14])) {
            $days = 30 + 1;
        }
        if (in_array($this->days, [21,28,30])) {
            $days = 56 + 1;
        }
        $end_date = date('Y-m-d 00:00:00', strtotime("+$days days"));
        $oNewLoan = new User_loan();
        $new_loan_id = $oNewLoan->saveRenewLoan($parent_loan, $end_date, $number, $parent_loan_id,$days);
        if (!empty($new_loan_id)) {
            if (in_array($this->days, [14])) {
                $oNewLoan->update_userLoan(['status' => 4, 'days' => 30]);
            }elseif(in_array($this->days, [21,28,30])){
                 $oNewLoan->update_userLoan(['status' => 4, 'days' => 56]);
            } else {
                $oNewLoan->update_userLoan(['status' => 4]);
            }
            $oRenewModel = new Renew_record();
            $res = $oRenewModel->saveRecord($this->loan_id, $new_loan_id);
            return $res;
        } else {
            return FALSE;
        }
    }

    /**
     * 存管相关展期数据处理
     * @param type $renew_pay_time
     * @param type $renewalPaymentRecordId
     * @return boolean
     */
    public function createRenewCunguanLoan($renew_pay_time, $renewalPaymentRecordId = 0, $renewRecord = '') {
        $renewModel = new Renew_amount();
        $renew = $renewModel->getRenew($this->loan_id, $renew_pay_time);
        if (empty($renew)) {
            return FALSE;
        }
        $new_loan_id = $renewRecord->loan_id_new;
        $loan_new = self::findOne($new_loan_id);
        $parent_loan_id = $this->parent_loan_id;
        $parent_loan = User_loan::findOne($parent_loan_id);
        $days = $this->days + 1;
        if (in_array($this->days, [14, 21, 28])) {
            $days = 30;
        }
        $end_date = date('Y-m-d 00:00:00', strtotime("+$days days"));
        $new_condition = [
            'start_date' => date("Y-m-d 00:00:00"),
            'end_date' => $end_date,
            'status' => 9,
        ];
        $result = $loan_new->update_userLoan($new_condition);
        $res = $loan_new->changeStatus(9);
        if ($result) {
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
                $renewModel->addExtension($parent_loan, $renew, $end_date, $new_loan_id);
                if ($renewalPaymentRecordId != 0) {
                    //更新续期支付记录
                    $insureModel = Insure::findOne($renewalPaymentRecordId);
                    if (!$insureModel) {
                        return false;
                    }
                    $result = $insureModel->updateData(['new_loan_id' => $new_loan_id]);
                    if (!$result) {
                        return false;
                    }
                }
                return $res;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * 获取电子账户
     */
    public function getAccountId() {
        $account_id = '';
        $payaccount = (new Payaccount())->getPaysuccessByUserId($this->user_id, 2, 1);
        if (!empty($payaccount)) {
            $account_id = $payaccount->accountId;
        }
        return $account_id;
    }

    /**
     * 借款描述转换
     */
    public function transDesc() {
        $desc = [
            '个人或家庭消费' => '购买生活用品',
            '其他' => '购买生活用品',
            '其它' => '购买生活用品',
            '学习' => '培训/教育消费',
            '旅游' => '个人旅游消费',
            '消费' => '日常生活消费',
            '物流运输' => '物流运输消费',
            '租房' => '房屋租赁',
            '购买原材料' => '购买原材料消费',
            '购买家具或家电' => '购买家具/家电',
            '购买设备' => '购买设备消费',
            '资金周转' => '购买生活用品',
            '进货' => '进货消费',
            '购买服饰'=>'购买生活用品',
            '购买食品'=>'购买生活用品',
            '购买电子产品'=>'购买家具/家电',
        ];

        return isset($desc[$this->desc]) ? $desc[$this->desc] : '购买生活用品';
    }

    /**
     * 控制兼容评测离场监管进场
     */
    public function getcreditUserloan($user_id) {
        $o_user_credit = (new User_credit())->getUserCreditByUserId($user_id);
        if(!empty($o_user_credit) && $o_user_credit->create_time < '2018-10-23 17:00:00'){
            $status = true;
        }else{
            $status = false;
        }
        return $status;
    }
    
    /**
     * 还款详情页单期应还本金金额、分期本期本金和往期本金
     */
    public function shouldPayAmount( $oUserLoan ){
        $bj_amount = 0;
        $last_day = 0;
        $interest_amount = 0;
        if(empty($oUserLoan)){
            return $this->returnArray($bj_amount,$last_day);
        }
        $business_type = $oUserLoan->business_type; //5:借款分期 11:商城分期
        //单期应还金额、最后还款日、利息
        if( !in_array($business_type, [5, 6, 11]) ){
            $last_day = date('Y/m/d',strtotime($oUserLoan->end_date)-24*3600); 
            $interest_amount = ceil(($oUserLoan->getInterestFee()) * 100) / 100;
            $interest_amount = bcsub($interest_amount,$oUserLoan->coupon_amount,2);
            $interest_amount = $interest_amount < 0 ? 0 : $interest_amount;
            return $this->returnArray($bj_amount,$last_day,$interest_amount);
        }
        
        $isorOverdueLoan = $this->isorOverdueLoan($oUserLoan,2);
        $oCurrentPeriodGoodsBill= (new GoodsBill())->getPayGoodsBill( $oUserLoan->loan_id );
        //分期第一期未还款利息
        $interest_amount = (new GoodsBill())->getFirstPeriodFee( $oUserLoan->loan_id );
        $interest_amount = bcsub($interest_amount,$oUserLoan->coupon_amount,2);
        $interest_amount = $interest_amount < 0 ? 0 : $interest_amount;
        //分期未逾期(本期)待还本金、最后还款日
        if( in_array($business_type, [5, 6, 11]) && !$isorOverdueLoan ){
            $alldata = (new GoodsBill())->find()->where(['loan_id' => $oUserLoan->loan_id])->all();
            foreach ($alldata as $key => $val){
                if( !in_array($val->bill_status, [8,12])){
                    $bj_amount = empty($val) ? 0 : $val->principal;
                    $last_day =  empty($val) ? 0 : date('Y/m/d',strtotime($val->end_time)-24*3600);
                    return $this->returnArray($bj_amount,$last_day,$interest_amount);
                }
            }
        }
        //分期已逾期(本期+往期)待还本金
        if( in_array($business_type, [5, 6, 11]) && $isorOverdueLoan ){
           $overdueAmount = (new GoodsBill())->find()->select(['principal'=>'SUM(principal)'])->where(['loan_id' => $oUserLoan->loan_id, 'bill_status' => 12])->asArray()->one();
           $overdueAmount = empty($overdueAmount) ? 0 : $overdueAmount['principal'];
           $currentamount = !empty($oCurrentPeriodGoodsBill) ? $oCurrentPeriodGoodsBill->principal : 0;
//           $amount = $overdueAmount + $currentamount;
//           $bj_amount = sprintf("%.2f",$amount); 
           $day =  (new GoodsBill())->find()->select('end_time')->where(['loan_id'=>$oUserLoan->loan_id,'bill_status' => 12])->orderBy('end_time desc')->asArray()->one();
           $last_day = date('Y/m/d',strtotime($day['end_time'])-24*3600);
           return $this->returnArray($currentamount,$last_day,$interest_amount,$overdueAmount);
        }
        return $this->returnArray($bj_amount,$last_day,$interest_amount);
    }
    
    public function returnArray($bj_amount=0,$last_day=0 ,$interest_amount=0,$overdueAmount=0){
        return ['bj_amount'=>$bj_amount,'last_day' => $last_day,'interest_amount'=>$interest_amount,'overdue_bjamount'=>$overdueAmount];
    }

    /**
     * 是否已逾期
     * @param type $oUserLoan
     * @param type $type  1:单期 2：分期
     * @return type false:未逾期 true:已逾期
     */
    public function isorOverdueLoan( $oUserLoan,$type=1 ){
        if( $type == 1 ){
           return  ( in_array($oUserLoan->status, [12,13]) ) ? true : false;
        }
        if( $type == 2 ){
            $is_yq = (new GoodsBill())->find()->where(['loan_id' => $oUserLoan->loan_id, 'bill_status' => 12])->all();
            return !empty($is_yq) ? true : false;
        }
        
    }
    
    /**
     * 借款详情页数据(兼容分期)
     * @param type $oUserLoan
     * @return int
     * 2018 12 18
     */
    public function getBillDetailData($oUserLoan){
        
        $userloanModel = new User_loan();
        $goodsBillModel = new GoodsBill();
        $waitPayData = $userloanModel->shouldPayAmount($oUserLoan); //本金、最后还款日、利息
        $management_amount = (new OverdueLoan())->getoverAmount( $oUserLoan ); //贷后管理费
        $amount= $waitPayData['bj_amount']+$waitPayData['overdue_bjamount']+ $waitPayData['interest_amount'] + $management_amount;//本期应还总金额
        if( !in_array($oUserLoan->business_type, [5, 6, 11]) ){
             $amount =  $this->getRepaymentAmount($oUserLoan,1); //总应还款金额（本金+利息+贷后管理费-已还金额）
             $waitPayData['bj_amount'] = round($oUserLoan->amount,2);//应还本金
        }
        
        //距离还款日天数 已续期天数 借款状态（逾期正常续期）
        $dayData = $this->getDayData($oUserLoan,$userloanModel,$goodsBillModel);
        
        //是否可以续期
        $renew_amout = (new Renew_amount())->entry($oUserLoan->loan_id);
        $is_renewal_able = 2;
        $is_inspect = 0;//合规展期
        if($renew_amout['type'] != 0){
            $is_renewal_able = 1;
            if($renew_amout['type'] == 3){
                $is_inspect = 1;
            }
        }
        
        //待还期数 (分期)
        $waitPeriodNum = 0;
        $loan_type = 1; //1单期 2分期
        $alreadyList = []; //必还账单goods_bill的id
        if(in_array($oUserLoan->business_type, [5, 6, 11])){
            $loan_type= 2;
            $allbilldata = $goodsBillModel->getNotYetBillList($oUserLoan->loan_id,1);
            $billdata = $goodsBillModel->getNotYetBillList($oUserLoan->loan_id,2);
            if( empty($billdata) ){
                $billdata = $allbilldata;
            }
            $alreadyList = !empty($billdata) ? ArrayHelper::getColumn($billdata, 'id') : [];
            $waitPeriodNum = count($allbilldata); 
        }  
        
        $array = [
            'amount'=>$amount,
            'principal'=> empty($waitPayData['bj_amount']) ? 0.00: sprintf('%.2f', $waitPayData['bj_amount']),//本期应还本金（兼容分期）
            'loan_status'=>empty($dayData['loan_status']) ? 0: $dayData['loan_status'],
            'day'=>$dayData['day'],
            'renewal_day'=>$dayData['renewal_day'],
            'interest_amount'=>empty($waitPayData['interest_amount']) ? 0.00: sprintf('%.2f', $waitPayData['interest_amount']),//利息（兼容分期）
            'management_amount'=>empty($management_amount) ? 0.00: sprintf('%.2f', $management_amount),
            'is_renewal_able'=>$is_renewal_able,
            'last_day'=>$waitPayData['last_day'],//最后还款日(兼容分期)
            'is_inspect'=>$is_inspect,
            'period_num'=>$waitPeriodNum,
            'loan_type'=>$loan_type,
            'overdue_bjamount'=>empty($waitPayData['overdue_bjamount']) ? 0.00: sprintf('%.2f', $waitPayData['overdue_bjamount']), //往期应还本金
            'pay_goods_bill_id'=>$alreadyList,
        ];
        
        return $array;
    }
    public function getDayData($oUserLoan,$userloanModel,$goodsBillModel){
        $loan_status=1;//1正常  2逾期
        $day=0; //距离还款日天数
        $renewal_day=0; //已续期天数
        if( in_array($oUserLoan->status,[12,13]) ){ 
            $loan_status=2;//已逾期
            $day=(new User_loan())->getOverdueDays($oUserLoan);//逾期天数
        }elseif( in_array($oUserLoan->business_type, [5, 6, 11]) && $oUserLoan->status == 9 ){
            //分期是否逾期 
            $isorOverdueLoan = $userloanModel->isorOverdueLoan($oUserLoan,2);
            if($isorOverdueLoan ){
                 $loan_status=2;//已逾期
                    $day=$goodsBillModel->getFqOverdueDays($oUserLoan);//逾期天数（兼容分期）
            }                                                                           
        }else{
            $day_diff=strtotime($oUserLoan->end_date)-(24*3600)-strtotime(date('Y-m-d'));
            if($day_diff>0){
                $day=ceil(($day_diff)/3600/24);
            }
            if($oUserLoan->settle_type==3){
                $loan_status=3;//已续期
                $renewal_day=$oUserLoan->days;//借款天数
            }
        }
        return ['loan_status'=>$loan_status,'day'=>$day,'renewal_day'=>$renewal_day];
    }

    /**
     * 驳回分期子订单数据
     * @param type $loanInfo
     * @return boolean
     */
    public function rejectGoodsBill($loanInfo){
        $all_goods_bill = GoodsBill::find()->where(['loan_id' =>$loanInfo->loan_id ])->all();
        if(empty($all_goods_bill)){
            return false;
        }
        $bill_ids = ArrayHelper::getColumn($all_goods_bill,'id');
        $result_goods_bill = (new GoodsBill())->toFail($bill_ids);
        return $result_goods_bill;
    }

    /**
     * 检测是否允许借款
     * @param $o_user
     * @return string
     */
    public function checkCanLoan($o_user){
        if(empty($o_user) || !is_object($o_user)){
            return '10001';
        }

        //用户状态判断
        if ($o_user->status == 5) {
            return '10097';
        }

        //连点
        $norepet = (new No_repeat())->norepeat($o_user->user_id, $type = 2);
        if (!$norepet) {
            return '99991';
        }

        $loan_info = new User_loan();
        //判断是否存在借款
        $loan = $loan_info->getHaveinLoan($o_user->user_id, [1, 4, 5, 6, 9, 10, 11, 12]);
        if ($loan !== 0) {
            return '10050';
        }

        //判断是否存在驳回订单
        $judgment = $loan_info->LoanJudgment($o_user->user_id);
        if (!$judgment) {
            return '10098';
        }

        //判断7-14产品中是否有进行中的借款
        if (!empty($o_user->identity)) {
            $apiHttp = new Apihttp();
            $canLoan = $apiHttp->havingLoan(['identity' => $o_user->identity]);
            if (!$canLoan) {
                return '99990';
            }
        }

        //判断先花商城中订单及借款状况
        $shop_res = (new User_credit())->getshopOrder($o_user);
        if (!$shop_res) {
            return '10246';
        }

        return '0000';
    }

    /**
     * 检测借款数据
     * 注：评测对象为已检测过的可用评测
     * @param $o_user
     * @param $o_user_credit
     * @param $o_user_bank
     * @param $o_coupon
     * @param $amount
     * @param $days
     * @return string
     */
    public function checkLoanField($o_user,$o_user_credit,$o_user_bank,$o_coupon,$amount,$days,$period){
        if(empty($o_user) || !is_object($o_user)){
            return '10001';
        }
        if(empty($o_user_credit) || !is_object($o_user_credit)){
            return '10233';
        }

        if(empty($o_user_bank) || !is_object($o_user_bank)){
            return '10043';
        }
        if(!empty($o_coupon) && !is_object($o_coupon)){
            return '99996';
        }
        //评测信息检测
        if($o_user_credit->status != 2 && $o_user_credit->res_status != 1){
            return '10233';
        }
        if($o_user_credit->pay_status != 1){
            return '10233';
        }
        if(!empty($o_user_credit->loan_id)){
            return '10233';
        }
        if($o_user_credit->invalid_time <= date('Y-m-d H:i:s')){
            return '10233';
        }
        if (!in_array($o_user_credit->installment_result, [1, 3]) && $o_user_credit->installment_result !== NULL) {
            return '10233';
        }
        //用户信息检测
        if($o_user->status != 3){
            return '10023';
        }
        if($o_user->extend->company == ''){
            return '10047';
        }
        if ($o_user->pic_identity == '' || ($o_user->pic_identity != '' && $o_user->status == '4')) {
            return '10047';
        }

        //借款信息检测
        $can_min_money = Keywords:: getMinCreditAmounts();
        $can_max_money = $o_user_credit->amount;//@todo 借款额度，商城借款使用的是商城额度，不能使用该方法
        if(intval($amount) < $can_min_money || intval($amount) > $can_max_money || intval($amount) % 500 != 0){
            return '10048';
        }
        $is_installment = $o_user_credit->installment_result == 1 ? TRUE : FALSE;
        $can_min_days = Keywords::getMinDays();
        $can_max_days = Keywords::getMaxDays();
        $can_days = $o_user_credit->days;
        if(intval($days) < $can_min_days || intval($days) > $can_max_days){
            return '10048';
        }
        if($is_installment && $days != $can_days){
            return '10048';
        }
        if(!$is_installment && intval($days) % 7 != 0){
            return '10048';
        }
        if($period < 1 || $period > 12){
            return '10048';
        }

        //银行卡信息检测
        if($o_user_bank->user_id != $o_user->user_id){
            return '10044';
        }
        $is_open = (new Payaccount())->getPaysuccessByUserId($o_user->user_id, 2, 1);
        if (empty($is_open) || empty($is_open->card)) {
            return '10210';
        }
        if ($is_open->card != $o_user_bank->id) {
            return '10211';
        }

        //优惠卷信息检测
        if(!empty($o_coupon)){
            if($is_installment){
                return '10049';
            }
            if (($o_coupon->mobile != $o_user->mobile) || $o_coupon->status != 1) {
                return '10049';
            }
        }
        return '0000';
    }

    public function addUserLoanRecord($o_user,$o_user_credit,$o_user_bank,$o_coupon,$loan_info){
        if(empty($o_user) || !is_object($o_user)){
            return ['rsp_code' => '10001'];
        }
        if(empty($o_user_credit) || !is_object($o_user_credit)){
            return ['rsp_code' => '10233'];
        }
        if($o_user_credit->pay_status != 1){
            return ['rsp_code' => '10253'];
        }
        if(empty($o_user_bank) || !is_object($o_user_bank)){
            return ['rsp_code' => '10043'];
        }
        if(!empty($o_coupon) && !is_object($o_coupon)){
            return ['rsp_code' => '99996'];
        }
        if(empty($loan_info)){
            return ['rsp_code' => '99996'];
        }
        $required = ['amount', 'days', 'period', 'desc', 'source', 'business_type', 'uuid'];
        $no_empty = ['amount', 'days', 'period', 'desc', 'source', 'business_type'];
        $code = $this->beforeVerify($required, $no_empty, $loan_info);
        if($code != '0000'){
            return ['rsp_code' => '99994'];
        }
        $is_installment = $o_user_credit->installment_result == 1 ? TRUE : FALSE;
        $calculation = (new User_label())->isChargeUser($o_user->mobile);
        $is_calculation = $calculation === FALSE ? 1 : 0;//1前置收费 0后置收费
        $ip = Common::get_client_ip();
        //利息&手续费
        $loanfee = $this->getInterestAndWithdraw($o_user, $o_user_credit,$loan_info['amount'], $loan_info['days'],$loan_info['period']);
        //优惠卷金额
        $coupon_amount = 0;
        if (!empty($o_coupon) && !$is_installment) {
            $interest_fee = empty($loanfee['interest_fee']) ? 0 : $loanfee['interest_fee'];
            if ($interest_fee > $o_coupon->val) {
                $coupon_val = $coupon_val = 0 ? $interest_fee : $o_coupon->val;
                $coupon_amount = $coupon_val;
            } else {
                $coupon_amount = $interest_fee;
            }
        }
        $loan_no = (new User_loan())->getLoanNo($o_user->user_id);
        $source = ($loan_info['source'] == 3) ? 2 : $loan_info['source'];
        //利息类型2全息 3半息
        $type = 2;
        if(!$is_installment && Keywords::feeOpen() == 2){
            $type = 3;
        }

        $condition = [
            'user_id' => $o_user->user_id,
            'real_amount' => $loan_info['amount'],
            'amount' => $loan_info['amount'],
            'current_amount' => $loan_info['amount'],
            'credit_amount' => 0,
            'recharge_amount' => 0,
            'days' => bcmul($loan_info['days'], $loan_info['period'], 0),
            'type' => $type,
            'status' => 6,
            'prome_status' => 5,
            'desc' => $loan_info['desc'],
            'bank_id' => $o_user_bank->id,
            'source' => $source,
            'interest_fee' => $loanfee['interest_fee'],
            'withdraw_fee' => $loanfee['withdraw_fee'],
            'is_calculation' => $is_calculation,
            'coupon_amount' => $coupon_amount,
            'withdraw_time' => date('Y-m-d H:i:s'),
            'final_score' => 0,
            'loan_no' => $loan_no,
        ];

        $result_loan_id = $this->addUserLoan($condition,$loan_info['business_type']);
        if($result_loan_id === FALSE){
            return ['rsp_code' => '10051'];
        }
        //借款副属表
        $success_num = (new User())->isRepeatUser($o_user->user_id);
        $loanextendModel = new User_loan_extend();
        $extend = array(
            'user_id' => $o_user->user_id,
            'loan_id' => $this->loan_id,
            'outmoney' => 0,
            'payment_channel' => 0,
            'userIp' => $ip,
            'extend_type' => '1',
            'success_num' => $success_num,
            'uuid' => $loan_info['uuid'],
            'status' => 'AUTHED'
        );
        $extendId = $loanextendModel->addList($extend);
        if (empty($extendId)) {
            Logger::dayLog('weixin/loan/addLoan', '添加userloanextend失败', 'loan_id：' . $this->loan_id, $extend);
            return ['rsp_code' => '10051'];
        }

        //评测记录
        $credit_array = [
            'loan_id' => $this->loan_id,
            'invalid_time' => date('Y-m-d H:i:s')
        ];
        $credit_result = $o_user_credit->updateUserCredit($credit_array);
        if(empty($credit_result)){
            Logger::dayLog('models/user_loan/addUserLoanRecord', '更新评测失效失败', 'loan_id：' . $this->loan_id, $credit_array);
            return ['rsp_code' => '10051'];
        }
        (new UserCreditList())->synchro($o_user_credit->req_id);

        //记录优惠券使用情况
        if (!empty($o_coupon) && !$is_installment) {
            (new Coupon_use())->addCouponUse($o_user, $o_coupon->id, $this->loan_id);
        }

        //分期记录
        if ($is_installment) {
            $stage_a = (new StageService())->addStageBill($this);
            if (!$stage_a) {
                return ['rsp_code' => '10202'];
            }
        }
        return ['rsp_code' => '0000'];
    }

    /**
     * 检查必传参数
     * @param array $required
     * @param array $httpParams
     * @return string
     */
    private function beforeVerify($required = [], $no_empty = [], $params = [])
    {
        if (empty($required) || empty($params) || empty($no_empty) || !is_array($params) || !is_array($required) || !is_array($no_empty)) {
            return '99994';
        }
        foreach ($required as $val) {
            if(!isset($params[$val])){
                Logger::dayLog('models/user_loan/beforeVerify', $val.'为必传参数');
                return '99994';
            }
        }
        foreach ($no_empty as $val){
            if(!isset($params[$val]) || $params[$val] == '' || $params[$val] == NULL){
                Logger::dayLog('models/user_loan/beforeVerify', $val.'不能为空');
                return '99994';
            }
        }
        return '0000';
    }

    /**
     * 获取利息&手续费
     * @param     $o_user
     * @param     $o_user_credit
     * @param     $amount
     * @param     $days
     * @param     $term
     * @param int $type 1借款日息 2商城日息
     * @return array|bool
     */
    public function getInterestAndWithdraw($o_user, $o_user_credit, $amount, $days, $term, $type = 1){
        if(empty($o_user) || !is_object($o_user)){
            return FALSE;
        }
        if(empty($o_user_credit) || !is_object($o_user_credit)){
            return FALSE;
        }
        if(empty($amount) || empty($days) || empty($term)){
            return FALSE;
        }
        if(!in_array($type,[1,2])){
            return FALSE;
        }
        switch ($type){
            case 1:
                $interest = empty($o_user_credit->interest_rate) ? ($o_user_credit->interest_rate) / 100 : 0.00098;
                break;
            case 2:
                $interest = empty($o_user_credit->shop_interest_rate) ? ($o_user_credit->shop_interest_rate) / 100 : 0.00098;
                break;
        }
        $is_installment = $o_user_credit->installment_result == 1 ? TRUE : FALSE;
        $loanfee = (new User_loan())->loan_Fee_rate_new($amount, $interest, $days, $o_user->user_id, $term, $loan_type = 1, $is_installment);
        return $loanfee;
    }
}
