<?php

namespace app\models\news;

use app\models\BaseModel;
use app\models\dev\Promes;

/**
 * This is the model class for table "yi_overdue_loan".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $user_id
 * @property string $bill_id
 * @property string $bank_id
 * @property string $loan_no
 * @property string $amount
 * @property integer $days
 * @property string $desc
 * @property string $start_date
 * @property string $end_date
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
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class OverdueLoan extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_overdue_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
//            [['loan_id', 'user_id', 'bank_id', 'amount', 'days', 'desc', 'start_date', 'end_date', 'loan_status', 'interest_fee', 'contract', 'contract_url', 'is_push', 'business_type', 'source', 'is_calculation', 'create_time', 'last_modify_time'], 'required'],
            [['loan_id', 'user_id', 'loan_type', 'bank_id', 'days', 'loan_status', 'is_push', 'business_type', 'source', 'is_calculation', 'version'], 'integer'],
            [['amount', 'current_amount', 'interest_fee', 'late_fee', 'withdraw_fee', 'chase_amount'], 'number'],
            [['start_date', 'end_date', 'create_time', 'repay_time', 'last_modify_time'], 'safe'],
            [['loan_no', 'bill_id'], 'string', 'max' => 64],
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
            'bill_id' => 'Bill ID',
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
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    public function getGoodsbill() {
        return $this->hasOne(GoodsBill::className(), ['bill_id' => 'bill_id']);
    }

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['id' => 'bank_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getUserloan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getPromes() {
        return $this->hasOne(Promes::className(), ['loan_id' => 'loan_id']);
    }

    public function getRemit() {
        return $this->hasOne(User_remit_list::className(), ['loan_id' => 'loan_id']);
    }

    public function saveOverdue($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
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
     * 更新charse_amount
     * @param type $loan_id
     * @param type $bill_id
     * @param type $chase_amount
     * @return boolean
     */
    public function saveChaseAmount($loan_id, $bill_id, $chase_amount, $late_fee, $is_push = '') {
        if (!$loan_id)
            return false;
        $where = ['loan_id' => $loan_id];
        if (!empty($bill_id)) {
            $where = array_merge($where, ['bill_id' => $bill_id]);
        }
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

    /**
     * 根据条件获取逾期信息 
     */
    public function getLoaninfo($where = []) {
        return self::find()->where($where)->one();
    }

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
     * 获取逾期记录，根据loan_id
     * 注：逾期未还清记录
     * @param $loanId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function listOverdueByLoanId($loanId) {
        if (!is_numeric($loanId) || empty($loanId)) {
            return null;
        }
        return self::find()->where(['loan_id' => $loanId, 'loan_status' => [12, 13]])->orderBy(['create_time' => 'desc'])->all();
    }

    /**
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

    /**
     * 获取滞纳金根据loan_id
     * @param $loanId
     */
    public function getLateFeeByLoanId($loanId) {
        if (!is_numeric($loanId) || empty($loanId)) {
            return 0;
        }
        $info = self::find()->where(['loan_id' => $loanId, 'loan_status' => [12, 13]])->sum('late_fee');
        return !empty($info) ? $info : 0;
    }

    /**
     * 获取分期账单逾期金额
     */
    public function getOverdueAmount($bill_id) {
        if (empty($bill_id)) {
            return false;
        }
        $res = self::find()->where(['bill_id' => $bill_id])->one();
        return $res;
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
        $loanModel = User_loan::findOne($this->loan_id);
        if ($loanModel->end_date >= '2039-01-10') {//TODO 开始时间修改
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

    /**
     * 根据主键查询
     */
    public function getByLoanId($loan_id) {
        $res = self::find()->where(['loan_id' => $loan_id])->one();
        return $res;
    }
    
    /**
     * 贷后管理费兼容分期
     */
    public function getoverAmount( $oUserLoan ){
        $management_amount = 0;
        if(empty($oUserLoan)){
            return $management_amount;
        }
        if ( !in_array($oUserLoan->business_type, [5, 6, 11]) && in_array($oUserLoan->status,[12,13]) ) {  //单期
            $oOverdueLoan=OverdueLoan::find()->where(['loan_id'=>$oUserLoan->loan_id])->one();
            if(!empty($oOverdueLoan)){
                $tem_amount = bcsub($oOverdueLoan->chase_amount,$oUserLoan->amount,2);
                $tem_amount = bcsub($tem_amount,$oUserLoan->withdraw_fee,2);
                $management_amount = bcsub($tem_amount,$oOverdueLoan->interest_fee,2);
            }
            return $management_amount;
        }
        
        if( in_array($oUserLoan->business_type, [5, 6, 11]) ){
            $goodsBillpPay = GoodsBill::find()->where(['loan_id' =>$oUserLoan->loan_id ,'bill_status' => 12 ])->all();
            foreach ($goodsBillpPay as $val){
                $management_amount += bcsub($val['actual_amount'],$val['current_amount'],2);
            }
            return $management_amount;
        }
        return $management_amount;
    }
    
    
    
}
