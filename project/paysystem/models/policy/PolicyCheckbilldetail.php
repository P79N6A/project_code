<?php

namespace app\models\policy;

use Yii;

/**
 * This is the model class for table "policy_checkbill".
 *
 * @property integer $id
 * @property string $channelOrderNo
 * @property string $policyNo
 * @property string $orderId
 * @property string $billDate
 * @property integer $billStatus
 * @property string $remark
 * @property string $create_time
 */
class PolicyCheckbilldetail extends \app\models\BaseModel 
{
    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'policy_checkbilldetail';
    }
    public static function getStatus() {
        return [
            static::STATUS_SUCCESS => '对账成功',
            static::STATUS_FAILURE => '对账失败',          
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['billDate', 'create_time'], 'required'],
            [['aid', 'fund','billStatus','remit_status','pay_status'], 'integer'],
            [['remark'], 'string'],
            [['create_time'], 'safe'],
            [['policy_premium', 'premium'], 'number'],
            [['user_name','user_mobile','channelOrderNo'], 'string', 'max' => 30],
            [['policyNo', 'orderId'], 'string', 'max' => 50],
            [['billDate'], 'string', 'max' => 20]
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
            'orderId' => 'Order ID',
            'billDate' => 'Bill Date',
            'billStatus' => 'Bill Status',
            'remark' => 'Remark',
            'create_time' => 'Create Time',
        ];
    }
    //保存数据
    public function saveData($postData)
    { 
        if (!is_array($postData) || empty($postData)) {
            return false;
        }
        $postData['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($postData);
        if ($error) {
            return $this->returnError(null,implode('|', $error));
        }
        $res = $this->save();
        if (!$res) {
            return $this->returnError(null,implode('|', $this->errors));
        }
        return $res;
    }
    public function getData($where){
        $data = static::find()->where($where)->one();
        return $data;
    }
    public function updateData($where,$data){
        $res = static::updateAll($data,$where);
        return $res;
    }
    // public static function getGroupCount($where){
    //     $data = static::find()->where($where)->groupBy('billDate')->all();
    //     return count($data);
    // }
    // /**
    //  * Undocumented function
    //  * 获取分组对账日期
    //  * @param [type] $where
    //  * @param [type] $pages
    //  * @return void
    //  */
    // public function getGroupBill($where,$pages){
    //     $data = static::find()->select('billDate')->where($where)->offset($pages->offset)->limit($pages->limit)->orderBy('billDate desc')->groupBy('billDate')->all();
    //     return $data;
    // }
    public function getDiffBill($billDate){
        //查询保单笔数，保单金额，账单笔数，账单金额

        $where = [
            'billDate'=>$billDate
        ];
        $data = static::find()->select('count(id) as policy_number,sum(premium) as policy_premium')->where($where)->asArray()->one();
        $policy_number = $data['policy_number'];
        $policy_premium = $data['policy_premium'];
        //查询失败保单
        $where['billStatus'] = static::STATUS_FAILURE;
        $_data = static::find()->select('count(id) as policy_number,sum(premium) as policy_premium')->where($where)->asArray()->one();
        $policy_diff_number = $_data['policy_number'];
        $policy_diff_premium = $_data['policy_premium'];
        $res_data = [
            'billDate'              => $billDate,
            'policy_number'         => $policy_number,
            'policy_premium'        => $policy_premium,
            'bill_number'           => $policy_number,
            'bill_premium'          => $policy_premium,
            'policy_diff_number'    => $policy_diff_number,
            'policy_diff_premium'   => $policy_diff_premium,
            'bill_diff_number'      => $policy_diff_number,
            'bill_diff_premium'     => $policy_diff_premium,
            'billStatus'            => static::STATUS_FAILURE,
        ];
        return $res_data;
    }
    public function getCompBill($billDate){
        //查询保单笔数，保单金额，账单笔数，账单金额

        $where = [
            'billDate'=>$billDate
        ];
        $data = static::find()->select('count(id) as policy_number,sum(premium) as policy_premium')->where($where)->asArray()->one();
        $policy_number = $data['policy_number'];
        $policy_premium = $data['policy_premium'];
        $res_data = [
            'billDate'              => $billDate,
            'policy_number'         => $policy_number,
            'policy_premium'        => $policy_premium,
            'bill_number'           => $policy_number,
            'bill_premium'          => $policy_premium,
            'billStatus'            => static::STATUS_SUCCESS,
        ];
        return $res_data;
    }
}