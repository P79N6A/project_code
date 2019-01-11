<?php

namespace app\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_user_remit_list".
 *
 * @property integer $id
 * @property string $order_id
 * @property integer $loan_id
 * @property integer $admin_id
 * @property string $settle_request_id
 * @property string $real_amount
 * @property string $settle_fee
 * @property string $settle_amount
 * @property string $rsp_code
 * @property string $rsp_msg
 * @property string $remit_status
 * @property string $create_time
 * @property integer $bank_id
 * @property integer $user_id
 * @property integer $type
 * @property string $last_modify_time
 * @property string $remit_time
 * @property integer $fund
 * @property integer $payment_channel
 * @property integer $version
 */
class YiUserRemitList extends \app\models\yyy\YyyBase 
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_remit_list';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'admin_id', 'create_time', 'bank_id'], 'required'],
            [['loan_id', 'admin_id', 'bank_id', 'user_id', 'type', 'fund', 'payment_channel', 'version'], 'integer'],
            [['real_amount', 'settle_fee', 'settle_amount'], 'number'],
            [['create_time', 'last_modify_time', 'remit_time'], 'safe'],
            [['order_id', 'settle_request_id'], 'string', 'max' => 32],
            [['rsp_code'], 'string', 'max' => 30],
            [['rsp_msg'], 'string', 'max' => 50],
            [['remit_status'], 'string', 'max' => 12]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'loan_id' => 'Loan ID',
            'admin_id' => 'Admin ID',
            'settle_request_id' => 'Settle Request ID',
            'real_amount' => 'Real Amount',
            'settle_fee' => 'Settle Fee',
            'settle_amount' => 'Settle Amount',
            'rsp_code' => 'Rsp Code',
            'rsp_msg' => 'Rsp Msg',
            'remit_status' => 'Remit Status',
            'create_time' => 'Create Time',
            'bank_id' => 'Bank ID',
            'user_id' => 'User ID',
            'type' => 'Type',
            'last_modify_time' => 'Last Modify Time',
            'remit_time' => 'Remit Time',
            'fund' => 'Fund',
            'payment_channel' => 'Payment Channel',
            'version' => 'Version',
        ];
    }
    public function getDataByReqId($req_id){
        if(empty($req_id)) return false;
        $data = static::find()->where(array('order_id'=>$req_id))->one();
        return $data;
    }

    public function getRemitData($loan_id){
        if(empty($loan_id)) return false;
        $data = static::find()->where(array('loan_id'=>$loan_id,'remit_status' =>'SUCCESS'))->one();
        return $data;
    }


}