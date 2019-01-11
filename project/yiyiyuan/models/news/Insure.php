<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_insure".
 *
 * @property string $id
 * @property string $req_id
 * @property string $order_id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $type
 * @property integer $source
 * @property integer $status
 * @property double $money
 * @property double $actual_money
 * @property string $paybill
 * @property string $repay_time
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class Insure extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_insure';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['req_id', 'order_id', 'user_id', 'loan_id', 'last_modify_time', 'create_time', 'version'], 'required'],
            [['user_id', 'loan_id', 'type', 'source', 'status', 'version', 'new_loan_id'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['repay_time', 'last_modify_time', 'create_time'], 'safe'],
            [['req_id', 'order_id', 'paybill'], 'string', 'max' => 64]
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
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'new_loan_id' => 'New Loan ID',
            'type' => 'Type',
            'source' => 'Source',
            'status' => 'Status',
            'money' => 'Money',
            'actual_money' => 'Actual Money',
            'paybill' => 'Paybill',
            'repay_time' => 'Repay Time',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }
    
    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }
    
    public function getInsurance() {
        return $this->hasOne(Insurance::className(), ['req_id' => 'req_id']);
    }
    
    public function getcgremit() {
        return $this->hasOne(Cg_remit::className(), ['loan_id' => 'loan_id']);
    }

    public function saveData($data){
        if(empty($data) || !is_array($data)){
            return false;
        }
        $condition = $data;
        $now = date('Y-m-d H:i:s');
        $condition['create_time'] = $now;
        $condition['last_modify_time'] = $now;
        $condition['status'] = 0;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return FALSE;
        }
        return $this->save();
    }

    public function updateData($data){
        if(empty($data) || !is_array($data)){
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $condition = $data;
        $condition['last_modify_time'] = $now;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return FALSE;
        }
        return $this->save();
    }

    public function getDateByLoanId($loan_id){
        $loan_id = intval($loan_id);
        if(!$loan_id){
            return null;
        }
        return self::find()->where(['loan_id'=>$loan_id])->one();
    }

    public function getInsuranceByOrderIdReqId($orderId, $reqId)
    {
        if (!$orderId || !$reqId) {
            return null;
        }
        return self::find()->where(['order_id' => $orderId, 'req_id' => $reqId])->one();
    }

    public function getDateByReqId($reqId){
        if(!$reqId){
            return null;
        }
        return self::find()->where(['req_id'=>$reqId,'status'=>[0,-1,1]])->one();
    }


    //获取最新一条购买保险记录
    public function getLastInsure($loaninfo)
    {
        $insureWhere = [
            'AND',
            ['loan_id' => $loaninfo->loan_id],
            ['status' => [0,-1]]
        ];
        return self::find()->where($insureWhere)->orderBy('create_time desc')->one();
    }

    public function getInsureStatus($loan_id){
        if (!$loan_id) {
            return false;
        }
        $insureWhere = [
            'AND',
            ['loan_id' => $loan_id],
            ['status' => 1]
        ];
        return self::find()->where($insureWhere)->one();
    }
    /**
     * 获取是否续期中（支付结果同步中的状态）
     * @param loan_id
     * @return bool
     */
    public function getInsurePayingStatus($loan_id){
        if (!$loan_id) {
            return false;
        }
        $insureWhere = [
            'AND',
            ['loan_id' => $loan_id],
            ['status' => [0,-1]]
        ];
        $oInsure=self::find()->where($insureWhere)->one();
        if(!empty($oInsure)){
            return true;
        }
        return false;
    }

    /**
     * 获取展期费用还款成功的
     * 贷后
     * @param $startTime
     * @param $endTime
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRenewalByTime($startTime, $endTime){
        $where = [
            'and',
            ['>=', 'last_modify_time',$startTime],
            ['<',  'last_modify_time', $endTime],
            ['=',  'status',      1],
            ['=',  'type',      3],
        ];
        $loans = self::find()->where($where)->all();
        return $loans;
    }
}
