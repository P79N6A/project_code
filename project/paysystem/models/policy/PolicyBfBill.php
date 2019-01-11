<?php

namespace app\models\policy;

use Yii;

/**
 * This is the model class for table "policy_bf_bill".
 *
 * @property integer $id
 * @property string $client_id
 * @property string $bf_orderid
 * @property string $settle_amount
 * @property string $settle_fee
 * @property integer $status
 * @property string $create_time
 * @property string $settle_time
 */
class PolicyBfBill extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'policy_bf_bill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['settle_amount', 'settle_fee'], 'number'],
            [['status'], 'integer'],
            [['create_time'], 'required'],
            [['create_time'], 'safe'],
            [['client_id', 'bf_orderid'], 'string', 'max' => 40],
            [['settle_time'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'Client ID',
            'bf_orderid' => 'Bf Orderid',
            'settle_amount' => 'Settle Amount',
            'settle_fee' => 'Settle Fee',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'settle_time' => 'Settle Time',
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
     * @param [type] $client_id
     * @return void
     */
    public function getBillByClientId($client_id){
        if(empty($client_id)) return false;
        $data = static::find()->where(array('client_id'=>$client_id))->one();
        return $data;
    }
}