<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "alipay_order".
 *
 * @property integer $id
 * @property integer $payorder_id
 * @property integer $account_id
 * @property integer $aid
 * @property string $orderid
 * @property string $cli_orderid
 * @property integer $amount
 * @property string $productname
 * @property string $productdesc
 * @property string $identityid
 * @property string $cli_identityid
 * @property integer $orderexpdate
 * @property string $userip
 * @property string $create_time
 * @property string $modify_time
 * @property integer $status
 * @property string $other_orderid
 * @property string $error_code
 * @property string $error_msg
 * @property integer $version
 */
class AlipayOrder extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'alipay_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payorder_id', 'account_id', 'aid', 'orderid', 'cli_orderid', 'amount', 'productname', 'productdesc', 'identityid','userip', 'create_time', 'modify_time', 'other_orderid', 'error_msg', 'version'], 'required'],
            [['payorder_id', 'account_id', 'aid', 'amount', 'orderexpdate', 'status', 'version'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['orderid', 'userip'], 'string', 'max' => 30],
            [['cli_orderid', 'productname','other_orderid', 'error_code', 'error_msg'], 'string', 'max' => 50],
            [['productdesc'], 'string', 'max' => 200],
            [['identityid'], 'string', 'max' => 20],
            [['orderid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payorder_id' => 'Payorder ID',
            'account_id' => 'Account ID',
            'aid' => 'Aid',
            'orderid' => 'Orderid',
            'cli_orderid' => 'Cli Orderid',
            'amount' => 'Amount',
            'productname' => 'Productname',
            'productdesc' => 'Productdesc',
            'identityid' => 'Identityid',
            'orderexpdate' => 'Orderexpdate',
            'userip' => 'Userip',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'status' => 'Status',
            'other_orderid' => 'Other Orderid',
            'error_code' => 'Error Code',
            'error_msg' => 'Error Msg',
            'version' => 'Version',
        ];
    }
    public function createOrder($oPayorder,$accountInfo){
        $data = $oPayorder->attributes;
        $data['payorder_id'] = $data['id'];
        $data['account_id']  = $accountInfo->id;
        $data['cli_orderid'] = $accountInfo->id.'_'.$oPayorder->orderid;
        // 参数检证是否有错
        if ($errors = $this->chkAttributes($postData)) {
            return $this->returnError(false, implode('|', $errors));
        }
        $result = $this->save($postData);
        if (!$result) {
            return $this->returnError(false, implode('|', $this->errors));
        }
        return true;
    }
} 