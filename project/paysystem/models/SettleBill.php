<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "settle_bill".
 *
 * @property integer $id
 * @property string $req_id
 * @property integer $remit_channel_id
 * @property string $remit_channel
 * @property string $remit_type
 * @property integer $loan_id
 * @property integer $user_id
 * @property string $loan_time
 * @property integer $loan_days
 * @property string $loan_money
 * @property string $withdraw_fee
 * @property string $interest_fee
 * @property string $fund
 * @property string $end_date
 * @property string $repay_status
 * @property string $all_money
 * @property string $chase_amount
 * @property string $settle_status
 * @property integer $is_yq
 * @property string $repay_time
 * @property integer $yq_days
 * @property string $repay_money
 * @property string $repay_actual_money
 * @property integer $is_badloan
 * @property string $badloan_money
 * @property string $badloan_actualmoney
 * @property string $badloan_fee
 * @property string $badloan_back
 * @property string $create_time
 * @property string $update_time
 * @property integer $status
 */
class SettleBill extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'settle_bill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['req_id'], 'required'],
            [['remit_channel_id','pay_channel_id', 'loan_id', 'user_id', 'loan_days', 'is_yq', 'yq_days', 'is_badloan', 'status','version'], 'integer'],
            [['remit_channel_id'], 'required'],
            [['remit_channel_id','pay_channel_id', 'loan_id', 'user_id', 'loan_days', 'is_yq', 'yq_days', 'is_badloan', 'status', 'remit_status','version'], 'integer'],
            [['loan_time', 'end_date', 'repay_time', 'create_time', 'update_time','remit_time','remit_create_time'], 'safe'],
            [['loan_money', 'withdraw_fee', 'like_amount', 'interest_fee', 'all_money', 'chase_amount','free_amount', 'repay_money', 'repay_actual_money', 'late_fee', 'badloan_money', 'badloan_actualmoney', 'badloan_fee'], 'number'],
            [['req_id', 'remit_channel','pay_channel'], 'string', 'max' => 50],
            [['remit_type', 'fund', 'repay_status', 'settle_status', 'badloan_back'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'req_id' => 'Req ID',
            'remit_channel_id' => 'Remit Channel ID',
            'remit_channel' => 'Remit Channel',
            'remit_type' => 'Remit Type',
            'loan_id' => 'Loan ID',
            'user_id' => 'User ID',
            'loan_time' => 'Loan Time',
            'loan_days' => 'Loan Days',
            'loan_money' => 'Loan Money',
            'withdraw_fee' => 'Withdraw Fee',
            'interest_fee' => 'Interest Fee',
            'like_amount' => 'like amount Fee',
            'fund' => 'Fund',
            'end_date' => 'End Date',
            'repay_status' => 'Repay Status',
            'all_money' => 'All Money',
            'chase_amount' => 'Chase Amount',
            'settle_status' => 'Settle Status',
            'is_yq' => 'Is Yq',
            'repay_time' => 'Repay Time',
            'yq_days' => 'Yq Days',
            'repay_money' => 'Repay Money',
            'repay_actual_money' => 'Repay Actual Money',
            'late_fee'          => 'Late Fee',
            'is_badloan' => 'Is Badloan',
            'badloan_money' => 'Badloan Money',
            'badloan_actualmoney' => 'Badloan Actualmoney',
            'badloan_fee' => 'Badloan Fee',
            'badloan_back' => 'Badloan Back',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'remit_time' => 'remit_time',
            'remit_create_time' => 'remit_create_time',
            'status' => 'Status',
            'remit_status' => 'Remit Status'
        ];
    }
    public function optimisticLock() {
        return "version";
    }
    public function createData($data){
        $data['create_time'] = date("Y-m-d H:i:s");
        $data['update_time'] = date("Y-m-d H:i:s");
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        //3 保存数据
        $result = $this->save();
        return $result;
    }
    //查询pay_channel_id=0的
    public function getDataList($where){
        $data = static::find()->where($where)->all();
        return $data;
    }


    public function getBillInfo($reqId){
        if(!$reqId){
            return false;
        }
        $data = static::find()->where(array('req_id'=>$reqId))->one();
        if(empty($data)){
            return false;
        }
        return true;
    }

    public function getLoanNum($loanId){
        if(!$loanId){
            return false;
        }
        $total = static::find()->where(array('loan_id'=>$loanId))->count();
        return empty($total)?0:$total;
    }

    public function getLoanCapital($loanId){//获得用户还款本金
        if(!$loanId){
            return false;
        }
        $money = static::find()->select(['sum(repay_actual_money) as settle_amount'])->where(array('loan_id'=>$loanId))->scalar();
        return empty($money) ? 0 : $money;
    }

    public function getLoanInterest($loanId){//获得用户利息总额
        if(!$loanId){
            return false;
        }
        $money = static::find()->select(['sum(interest_fee) as interest_amount'])->where(array('loan_id'=>$loanId))->scalar();
        return empty($money) ? 0 : $money;
    }

    public function upStatus($loanId) {
        if(!$loanId){
            return false;
        }
        $field = ['status' => 0];
        $where = ['loan_id' => $loanId];
        $ups = static::updateAll($field, $where);
        return $ups;
    }
    /**
     * 分组获取两条及两条以上的数据总数
     * @return int
     */
    public function getGroupBillCount()
    {
        return self::find()
                    ->select("loan_id")
                    ->distinct()
                    ->where(['remit_status' => 0, 'status'=>0])
                    ->count();
    }

    /**
     * 分组获取两条及两条以上的数据loan_id
     * @param int $limit
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getGroupBillData($limit = 100)
    {
        return self::find()
                    ->select("loan_id")
                    ->distinct()
                    ->where(['remit_status' => 0, 'status'=>0])
                    ->limit($limit)
                    ->all();
    }

    /**
     * 计算对应loan_id的chase_amount值
     * @param $loan_id
     * @return bool|int|mixed
     */
    public function getChaseAmountSum($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        $total = self::find()->where(['loan_id'=>$loan_id, 'status'=>0])->sum('chase_amount');
        return empty($total) ? 0 : $total;
    }

    /**
     * 计算对应loan_id的repay_money值
     * @param $loan_id
     * @return bool|int|mixed
     */
    public function getRepayMoneySum($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        $total = self::find()->where(['loan_id'=>$loan_id, 'status'=>0])->sum('repay_money');
        return empty($total) ? 0 : $total;
    }

    /**
     * 锁定
     * @param $loanIds
     * @return int
     */
    public function lockLoan($loanIds)
    {
        return self::updateAll(['remit_status' => 2], ['remit_status' => 0, 'loan_id' => explode(',', $loanIds)]);
    }

    /**
     * 计算loan_id条数
     * @param $loan_id
     * @return bool|int
     */
    public function getStatusCount($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        $total = self::find()->where(['loan_id'=>$loan_id, 'status'=>0])->count();
        return empty($total) ? 0 : $total;
    }

    /**
     * 计算对应loan_id的interest_fee值
     * @param $loan_id
     * @return bool|int|mixed
     */
    public function getInterestFeeSum($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        $total = self::find()->where(['loan_id'=>$loan_id, 'status'=>0])->sum('interest_fee');
        return empty($total) ? 0 : $total;
    }

    public function getLateFeeSum($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        $total = self::find()->where(['loan_id'=>$loan_id, 'status'=>0])->sum('late_fee');
        return empty($total) ? 0 : $total;
    }
    /**
     * 计算对应loan_id的repay_actual_money值
     * @param $loan_id
     * @return bool|int|mixed
     */
    public function getRepayActualMoneySum($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        $total = self::find()->where(['loan_id'=>$loan_id, 'status'=>0])->sum('repay_actual_money');
        return empty($total) ? 0 : $total;
    }

    /**
     * 通过loan_id获取数据
     * @param $loan_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getInfo($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        return self::find()->where(['loan_id'=>$loan_id])->orderBy("id asc")->one();
    }

    /**
     * 查找数据是否存在
     * @param $loan_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getLoanStatusInfo($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        $where_config = [
            'AND',
            ['loan_id'=>$loan_id],
            ['>', 'status', 1],
        ];
        return self::find()->where($where_config)->one();
    }

    /**
     * 查找数据是否存在
     * @param $req_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getLoanReqId($req_id)
    {
        if (empty($req_id)){
            return false;
        }
        return self::find()->where(['req_id'=>$req_id])->one();
    }

    /**
     * 修改数据
     * @param $data_set
     * @return bool
     */
    public function updateData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        foreach ($data_set as $k=>$v){
            $this->$k = $v;
        }
        $this->update_time = date("Y-m-d H:i:s");
        return $this->save();
    }

    /**
     * 状态完成
     * @param $loan_id
     * @return int
     */
    public function completeRemitStatus($loan_id)
    {
        return self::updateAll(['remit_status' => 1], ['remit_status' => 2, 'loan_id' => $loan_id]);
    }

    /**
     * 通过loan_id查找数据
     * @param $loan_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getLoanMoney($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        return self::find()->where(['loan_id'=>$loan_id])->one();
    }

    /**
     * 通过loan_id计算回款总额（repay_money）
     * @param $loan_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getRepayMoney($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        $ret = self::find()->where(['loan_id'=>$loan_id])->sum('repay_money');
        return empty($ret) ? 0 : $ret;
    }

    /**
     * 通过loan_id计算滞纳金收益
     * @param $loan_id
     * @return bool|int|mixed
     */
    public function getlateFee($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        $ret = self::find()->where(['loan_id'=>$loan_id])->sum('late_fee');
        return empty($ret) ? 0 : $ret;
    }

    /**
     * 通过loan_id计算利息
     * @param $loan_id
     * @return bool|int|mixed
     */
    public function getInterestFee($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        $ret = self::find()->where(['loan_id'=>$loan_id])->sum('interest_fee');
        return empty($ret) ? 0 : $ret;
    }
}