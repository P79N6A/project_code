<?php

namespace app\models\day;

use Yii;

/**
 * This is the model class for table "yi_overdue_loan_guide".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $user_id
 * @property string $bank_id
 * @property string $loan_no
 * @property string $amount
 * @property integer $days
 * @property string $desc
 * @property string $start_date
 * @property string $end_date
 * @property integer $loan_type
 * @property integer $loan_status
 * @property string $interest_fee
 * @property string $contract
 * @property string $contract_url
 * @property string $late_fee
 * @property string $withdraw_fee
 * @property string $chase_amount
 * @property integer $is_push
 * @property integer $business_type
 * @property integer $source
 * @property integer $is_calculation
 * @property string $repay_time
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Overdue_loan_guide extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'qj_overdue_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['loan_id', 'user_id', 'bank_id', 'amount', 'days', 'desc', 'start_date', 'end_date', 'loan_status', 'interest_fee', 'contract', 'is_push', 'business_type', 'source', 'is_calculation', 'repay_time', 'create_time', 'last_modify_time'], 'required'],
            [['loan_id', 'user_id', 'bank_id', 'days', 'loan_type', 'loan_status', 'is_push', 'business_type', 'source', 'is_calculation', 'version'], 'integer'],
            [['amount', 'interest_fee', 'late_fee', 'withdraw_fee', 'chase_amount'], 'number'],
            [['start_date', 'end_date', 'repay_time', 'create_time', 'last_modify_time'], 'safe'],
            [['loan_no'], 'string', 'max' => 64],
            [['desc', 'contract_url'], 'string', 'max' => 128],
            [['contract'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'user_id' => 'User ID',
            'bank_id' => 'Bank ID',
            'loan_no' => 'Loan No',
            'amount' => 'Amount',
            'days' => 'Days',
            'desc' => 'Desc',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'loan_type' => 'Loan Type',
            'loan_status' => 'Loan Status',
            'interest_fee' => 'Interest Fee',
            'contract' => 'Contract',
            'contract_url' => 'Contract Url',
            'late_fee' => 'Late Fee',
            'withdraw_fee' => 'Withdraw Fee',
            'chase_amount' => 'Chase Amount',
            'is_push' => 'Is Push',
            'business_type' => 'Business Type',
            'source' => 'Source',
            'is_calculation' => 'Is Calculation',
            'repay_time' => 'Repay Time',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function getBank() {
        return $this->hasOne(User_bank_guide::className(), ['id' => 'bank_id']);
    }

    public function getUser() {
        return $this->hasOne(User_guide::className(), ['user_id' => 'user_id']);
    }

    public function getPromes() {
        return $this->hasOne(\app\models\news\Promes::className(), ['loan_id' => 'loan_id']);
    }

    public function getRemit() {
        return $this->hasOne(User_remit_list_guide::className(), ['loan_id' => 'loan_id']);
    }
    public function getUserloan() {
        return $this->hasOne(User_loan_guide::className(), ['loan_id' => 'loan_id']);
    }

    /**
     * 结清逾期
     * @param string $repay_time
     * @return bool
     * @author 王新龙
     * @date 2018/8/3 18:27
     */
    public function clearOverdueLoan($repay_time = '') {
        $data = [
            'loan_status' => 8,
            'last_modify_time' => date("Y-m-d H:i:s"),
            'repay_time' => date("Y-m-d H:i:s"),
        ];
        if (!empty($repay_time)) {
            $data['repay_time'] = $repay_time;
        }

        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 根据条件获取总量
     * @param type $where 条件
     * @author zhangtian zhangtian@xianhuahua.com
     * @copyright (c) 2017, 11 17
     */
    public function getTotalNum($where = []) {
        return self::find()->where($where)->count();
    }

    public function getMoneyByCalculation() {
        if ($this->is_calculation == 1) {
            $moneys = $this->amount + $this->interest_fee;
        } else {
            $moneys = $this->amount + $this->interest_fee + $this->withdraw_fee;
        }
        return $moneys;
    }

    /**
     * 根据条件获取订单
     * @param type int $id 主键id
     * @param type int $limit 
     * @author zhangtian zhangtian@xianhuahua.com
     * @copyright (c) 2017, 11 17
     */
    public function getOverdueLoans($overdue_where, $limit = 1000) {
        return self::find()->where($overdue_where)->indexBy('id')->orderBy('id')->limit($limit)->all();
    }

    /**
     * 贷后
     * 根据条件获取逾期信息 
     */
    public function getLoaninfo($where = []) {
        return self::find()->where($where)->one();
    }

    /**
     * 贷后
     * 获取逾期数量
     * @param type $startTime
     * @param type $endTime
     * @return type
     */
    public function getOverdueNum($startTime = null, $endTime = null, $business_type) {
        $where = [
            'and',
            ['in', 'loan_status', [11, 12, 13]],
            ['in', 'is_push', [0, 1]],
            ['in', 'business_type', $business_type],
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
     * 贷后
     * 获取逾期数据
     */

    public function getOverdueInfo($startTime = null, $endTime = null, $offset = 0, $limit = 0, $business_type) {
        $where = [
            'and',
            ['in', 'loan_status', [11, 12, 13]],
            ['in', 'is_push', [0, 1]],
            ['in', 'business_type', $business_type],
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

    public function saveOverdue($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            print_r($error);
            return false;
        }
        $result = $this->save();
        return $result;
    }

    /**
     * 计算单期滞纳金
     * @return floor 逾期应还款金额
     */
    public function getUserLoanChaseAmount() {

        if ($this->is_calculation == 1) {   //前置
            $totalamount = $this->amount + $this->interest_fee;
        } else {
            $totalamount = $this->amount + $this->interest_fee + $this->withdraw_fee;
        }

        $days = floor((time() - strtotime($this->end_date)) / 24 / 3600) + 1;
        $loanModel = User_loan_guide::findOne($this->loan_id);
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

    /**
     * 更改ip_push
     * @param type $condition
     * @return boolean
     */
    public function update_userLoan($condition) {
        if (empty($condition)) {
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 更新charse_amount
     * @param type $loan_id
     * @param type $bill_id
     * @param type $chase_amount
     * @return boolean
     */
    public function saveChaseAmount($loan_id, $chase_amount, $late_fee, $is_push = '') {
        if (!$loan_id)
            return false;
        $where = ['loan_id' => $loan_id];
        if (!empty($is_push)) {
            $data['is_push'] = $is_push;
        }
        $res = $this->getLoaninfo($where);
        $data['chase_amount'] = $chase_amount;
        $data['late_fee'] = $late_fee;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $res->attributes = $data;
        return $res->save();
    }

    public function getLateFeeByLoanId($loanId) {
        if (empty($loanId)) {
            return 0;
        }
        $info = self::find()->where(['loan_id' => $loanId, 'loan_status' => [12, 13]])->sum('late_fee');
        return !empty($info) ? $info : 0;
    }
}
