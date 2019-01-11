<?php

namespace app\models;

use Yii;
/**
 * 网页爬去
 */

class GrapBank extends \app\models\BaseModel
{

    

    public static function tableName()
    {
        return 'bank_data';
    }
    
    public function rules() {
		return [
			[['bankid', 'pid', 'cid'], 'integer'],
			[['bank_code', 'bank_name', 'bank_tel', 'bank_address'], 'string', 'max' => 100]
		];
    }
    
    public function attributeLabels() {
        return [
            'id' => '主键',
            'bankid' => 'bankid',
            'pid' => 'pid',
            'cid' => 'cid',
            'bank_code' => 'bank_code',
            'bank_name' => 'bank_name',
            'bank_tel' => 'bank_tel',
            'bank_address' => 'bank_address'
        ];
    }

    public function createData($data){
        // var_dump($data);
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        //3 保存数据
        $result = $this->save();
        return $result;
    }

    public function getIsnum($data){//是否存在
        if(!$data){
            return 1;
        }
        $total = static::find()->where(array('bankid'=>$data['bankid'],'pid'=>$data['pid'],'cid'=>$data['cid'],'bank_code'=>$data['bank_code']))->count();
        return empty($total)?0:$total;
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