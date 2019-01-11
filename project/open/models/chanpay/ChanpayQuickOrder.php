<?php

namespace app\models\chanpay;

use Yii;

/**
 * This is the model class for table "chanpay_quick_order".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $orderid
 * @property string $aid_orderid
 * @property string $transtime
 * @property integer $currency
 * @property integer $amount
 * @property string $productname
 * @property string $productdesc
 * @property string $payer_name
 * @property string $id_number
 * @property string $buyer_mobile
 * @property string $phone_number
 * @property string $payer_card_no
 * @property string $card_type
 * @property string $bank_code
 * @property string $expiry_date
 * @property string $cvv2
 * @property integer $orderexpdate
 * @property string $callbackurl
 * @property string $imei
 * @property string $userip
 * @property string $ua
 * @property string $create_time
 * @property string $modify_time
 * @property string $closetime
 * @property integer $pay_status
 * @property integer $client_status
 * @property string $chanpayborderid
 * @property integer $error_code
 * @property string $error_msg
 * @property string $source_type
 */
class ChanpayQuickOrder extends \app\models\BaseModel
{
	const STATUS_INIT = 0;
	const STATUS_PAYOK = 2;
	const STATUS_PAYFAIL = 11;
	const STATUS_PAYING = 4;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'chanpay_quick_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'orderid', 'aid_orderid', 'transtime', 'amount', 'productname', 'payer_name', 'id_number', 'buyer_mobile', 'phone_number', 'payer_card_no', 'card_type', 'bank_code', 'expiry_date', 'cvv2', 'callbackurl', 'userip', 'create_time', 'modify_time', 'closetime'], 'required'],
            [['aid', 'currency', 'amount', 'pay_status', 'client_status', 'error_code'], 'integer'],
            [['transtime', 'create_time', 'modify_time', 'closetime'], 'safe'],
            [['orderid', 'userip', 'source_type'], 'string', 'max' => 30],
            [['aid_orderid', 'productname', 'payer_name', 'id_number', 'imei', 'chanpayborderid', 'error_msg'], 'string', 'max' => 50],
            [['productdesc'], 'string', 'max' => 200],
            [['buyer_mobile', 'phone_number', 'payer_card_no', 'bank_code'], 'string', 'max' => 20],
            [['card_type'], 'string', 'max' => 4],
        	[['orderexpdate'], 'string', 'max' => 10],
            [['expiry_date', 'cvv2'], 'string', 'max' => 32],
            [['callbackurl'], 'string', 'max' => 255],
            [['ua'], 'string', 'max' => 100],
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
            'aid' => 'Aid',
            'orderid' => 'Orderid',
            'aid_orderid' => 'Aid Orderid',
            'transtime' => 'Transtime',
            'currency' => 'Currency',
            'amount' => 'Amount',
            'productname' => 'Productname',
            'productdesc' => 'Productdesc',
            'payer_name' => 'Payer Name',
            'id_number' => 'Id Number',
            'buyer_mobile' => 'Buyer Mobile',
            'phone_number' => 'Phone Number',
            'payer_card_no' => 'Payer Card No',
            'card_type' => 'Card Type',
            'bank_code' => 'Bank Code',
            'expiry_date' => 'Expiry Date',
            'cvv2' => 'Cvv2',
            'orderexpdate' => 'Orderexpdate',
            'callbackurl' => 'Callbackurl',
            'imei' => 'Imei',
            'userip' => 'Userip',
            'ua' => 'Ua',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'closetime' => 'Closetime',
            'pay_status' => 'Pay Status',
            'client_status' => 'Client Status',
            'chanpayborderid' => 'Chanpayborderid',
            'error_code' => 'Error Code',
            'error_msg' => 'Error Msg',
            'source_type' => 'Source Type',
        ];
    }
    
    // 保存请求的数据
    public function saveOrder($postData) {
    	//1 数据验证
    	if (!is_array($postData) || empty($postData)) {
    		return $this->returnError(false, "数据不能为空");
    	}
    	if (empty($postData['orderid'])) {
    		return $this->returnError(false, "订单不能为空");
    	}
    	if (empty($postData['aid'])) {
    		return $this->returnError(false, "应用id不能为空");
    	}
    	$postData['transtime'] = $postData['create_time'] = $postData['modify_time'] = $postData['closetime'] = date('Y-m-d H:i:s');
    	// 参数检证是否有错
    	if ($errors = $this->chkAttributes($postData)) {
    		return $this->returnError(false, implode('|', $errors));
    	}
    	 
    	$result = $this->save($postData);
    	if (!$result) {
    		return $this->returnError(false, implode('|', $this->errors));
    	}
    	return $this;
    }
}
