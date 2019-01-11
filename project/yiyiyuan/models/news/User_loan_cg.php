<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_user_loan_cg".
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
class User_loan_cg extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_loan_cg';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['parent_loan_id', 'number', 'settle_type', 'user_id', 'days', 'type', 'status', 'prome_status', 'version', 'is_push', 'final_score', 'repay_type', 'business_type', 'bank_id', 'source', 'is_calculation'], 'integer'],
            [['user_id', 'amount', 'current_amount'], 'required'],
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

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function createLoan($user_id, $bank_id) {
        $amounts = [2000, 2500, 3000, 3500];
        $amountKey = array_rand($amounts, 1);
        $amount = $amounts[$amountKey];
        $condition = $this->makeCondition($amount, $user_id, $bank_id);
        $loan_id = $this->addUserLoan($condition);
        return $loan_id;
    }

    private function makeCondition($amount, $user_id, $bank_id) {
        $suffix = $user_id . rand(100000, 999999);
        $loan_no = date("YmdHis") . $suffix;

        $day = 28;
        $interest_fee = $amount * $day * 0.0005;
        $withdraw_fee = $amount * 0.1;
        $start_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d', (time() + (28 + 1) * 24 * 3600));
        $condition = array(
            'user_id' => $user_id,
            'loan_no' => $loan_no,
            'real_amount' => $amount,
            'amount' => $amount,
            'credit_amount' => 0,
            'recharge_amount' => 0,
            'current_amount' => $amount,
            'days' => $day,
            'type' => 2,
            'status' => 9,
            'interest_fee' => $interest_fee,
            'withdraw_fee' => $withdraw_fee,
            'desc' => '资金周转',
            'bank_id' => $bank_id,
            'withdraw_time' => date('Y-m-d H:i:s', time()),
            'is_calculation' => 1,
            'source' => 1,
            'business_type' => 1,
            'start_date' => $start_time,
            'end_date' => $end_time,
            'prome_status' => 5,
        );
        return $condition;
    }

    public function addUserLoan($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['number'] = 0;
        $data['settle_type'] = 0;
        $data['open_start_date'] = date('Y-m-d H:i:s');
        $data['open_end_date'] = date('Y-m-d 08:i:s');
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['version'] = 1;

        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        $result = $this->save();
        if (!$result) {
            return false;
        }
        $this->parent_loan_id = $this->loan_id;
        $this->save();
        return $this->loan_id;
    }

}
