<?php

namespace app\models\policy;

use Yii;

/**
 * This is the model class for table "policy_bill".
 *
 * @property integer $id
 * @property string $channelOrderNo
 * @property string $policyNo
 * @property string $applyDate
 * @property string $policyBeginDate
 * @property string $policyEndDate
 * @property string $policyHolderUserName
 * @property string $policyHolderCertiNo
 * @property string $insuredUserName
 * @property string $insuredCertiNo
 * @property string $sumInsured
 * @property string $premium
 * @property string $policyStatus
 * @property string $create_time
 */
class PolicyBill extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'policy_bill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['applyDate', 'policyBeginDate', 'policyEndDate', 'policyHolderUserName', 'policyHolderCertiNo', 'insuredUserName', 'insuredCertiNo', 'policyStatus', 'create_time'], 'required'],
            [['applyDate', 'policyBeginDate', 'policyEndDate', 'create_time'], 'safe'],
            [['sumInsured', 'premium'], 'number'],
            [['dataType'], 'string', 'max' => 10],
            [['channelOrderNo'], 'string', 'max' => 30],
            [['policyNo'], 'string', 'max' => 50],
            [['policyHolderUserName', 'policyHolderCertiNo', 'insuredUserName', 'insuredCertiNo', 'policyStatus'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'channelOrderNo' => 'Channel Order No',
            'policyNo' => 'Policy No',
            'applyDate' => 'Apply Date',
            'policyBeginDate' => 'Policy Begin Date',
            'policyEndDate' => 'Policy End Date',
            'policyHolderUserName' => 'Policy Holder User Name',
            'policyHolderCertiNo' => 'Policy Holder Certi No',
            'insuredUserName' => 'Insured User Name',
            'insuredCertiNo' => 'Insured Certi No',
            'sumInsured' => 'Sum Insured',
            'premium' => 'Premium',
            'policyStatus' => 'Policy Status',
            'create_time' => 'Create Time',
        ];
    }
    public static function getPolicyStatus(){
        return [
            1=>"出保",
            2=>"退保",
        ];
    }
    //保存数据
    public function saveData($postData)
    { 
        if (!is_array($postData) || empty($postData)) {
            return false;
        }
        $postData['create_time']   = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($postData);
        if ($error) {
            return $this->returnError(null,implode('|', $error));
        }
        $res = $this->save();
        if (!$res) {
            return $this->returnError(null,implode('|', $this->errors));
        }
        return true;
    }
    /**
     * Undocumented function
     * 根据订单号查询账单
     * @param [type] $orderno
     * @return void
     */
    public function getBillByOrderno($orderno,$dataType){
        if(empty($orderno)) return false;
        $data = static::find()->where(array('channelOrderNo'=>$orderno,'dataType'=>$dataType))->one();
        return $data;
    }
    public function countBillDetailData($filter_where){
        $query = self::find();
        $_query = $this->fundWhere($query,$filter_where);
        $data = $_query->count();
        return $data;
    }
    public function countBillData($filter_where){
        $query = self::find();
        $_query = $this->fundWhere($query,$filter_where);
        $data = $_query->groupBy('DATE(`create_time`)')->count();
        return $data;
    }
    /**
     * Undocumented function
     * 获取数据明细
     * @param [type] $pages
     * @param [type] $filter_where
     * @return void
     */
    public function getBillDetailData($pages,$filter_where){
        $query = self::find();
        $_query = $this->fundWhere($query,$filter_where);
        $data = $_query->offset($pages->offset)->limit($pages->limit)->orderBy('id desc')->asArray()->all();
        return $data;
    }
    /**
     * Undocumented function
     * 获取费用分组明细
     * @param [type] $pages
     * @param [type] $filter_where
     * @return void
     */
    public function getBillData($pages,$filter_where){
        $query = self::find()->select([ 'id,DATE(create_time) as bill_date,count(if(dataType=1,1,null)) as policy_num,sum(if(dataType=1,premium,0)) as policy_money,count(if(dataType=2,1,null)) as cancel_num, sum(if(dataType=2,premium,0)) as cancel_money']);
        $_query = $this->fundWhere($query,$filter_where);
        $data = $_query->offset($pages->offset)->limit($pages->limit)->groupBy('DATE(create_time)')->orderBy('create_time desc')->asArray()->all();
        return $data;
    }
    /**
     * Undocumented function
     * 导出费用列表
     * @param [type] $filter_where
     * @return void
     */
    public function getExportBill($filter_where){
        $query = self::find()->select([ 'id,DATE(create_time) as bill_date,count(if(dataType=1,1,null)) as policy_num,sum(if(dataType=1,premium,0)) as policy_money,count(if(dataType=2,1,null)) as cancel_num, sum(if(dataType=2,premium,0)) as cancel_money']);
        $_query = $this->fundWhere($query,$filter_where);
        $data = $_query->groupBy('DATE(create_time)')->orderBy('create_time desc')->asArray()->all();
        return $data;
    }
    /**
     * Undocumented function
     * 导出数据明细
     * @param [type] $filter_where
     * @return void
     */
    public function getExportDetail($filter_where){
        $query = self::find()->select(['id,DATE(create_time) as bill_date,policyNo,policyHolderUserName,premium,dataType']);
        $_query = $this->fundWhere($query,$filter_where);
        $data = $_query->orderBy('id desc')->asArray()->all();
        return $data;
    }
    /**
     * 初始条件
     * @param $filter_where
     * @return int|\yii\db\ActiveQuery
     */
    private function fundWhere($query,$filter_where)
    {
        
        if (!empty($filter_where['start_time'])){
            $query->andWhere(['>=', 'create_time', $filter_where['start_time']]);
        }
        if (!empty($filter_where['end_time'])){
            $query->andWhere(['<=', 'create_time', $filter_where['end_time']. ' 23:59:59']);
        }
        if(!empty($filter_where['dataType'])){
            $query->andWhere(['dataType' => $filter_where['dataType']]);
        }
        if(!empty($filter_where['channelOrderNo'])){
            $query->andWhere(['channelOrderNo' => $filter_where['channelOrderNo']]);
        }
        if(!empty($filter_where['policyNo'])){
            $query->andWhere(['policyNo' => $filter_where['policyNo']]);
        }
        return $query;
    }
}