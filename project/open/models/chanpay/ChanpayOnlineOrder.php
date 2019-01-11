<?php

namespace app\models\chanpay;


/**
 * This is the model class for table "chanpay_online_order".
 *
 */
class ChanpayOnlineOrder extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'chanpay_online_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'orderid', 'aid_orderid', 'transtime', 'amount', 'mobile', 'terminaltype', 'orderexpdate', 'userip', 'callbackurl', 'fcallbackurl', 'version', 'paytypes', 'create_time', 'modify_time', 'chanpayborderid', 'error_msg', 'chanpay_url'], 'required'],
            [['aid', 'currency', 'productcatalog', 'amount', 'terminaltype', 'orderexpdate', 'version', 'pay_status', 'client_status', 'error_code'], 'integer'],
            [['transtime', 'create_time', 'modify_time'], 'safe'],
            [['chanpay_url'], 'string'],
            [['orderid'], 'string', 'max' => 30],
            [['aid_orderid', 'productname', 'userip', 'paytypes', 'chanpayborderid', 'error_msg'], 'string', 'max' => 50],
            [['mobile'], 'string', 'max' => 12],
            [['callbackurl', 'fcallbackurl'], 'string', 'max' => 255]
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
            'productcatalog' => 'Productcatalog',
            'productname' => 'Productname',
            'amount' => 'Amount',
            'mobile' => 'Mobile',
            'terminaltype' => 'Terminaltype',
            'orderexpdate' => 'Orderexpdate',
            'userip' => 'Userip',
            'callbackurl' => 'Callbackurl',
            'fcallbackurl' => 'Fcallbackurl',
            'version' => 'Version',
            'paytypes' => 'Paytypes',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'pay_status' => 'Pay Status',
            'client_status' => 'Client Status',
            'chanpayborderid' => 'Chanpayborderid',
            'error_code' => 'Error Code',
            'error_msg' => 'Error Msg',
            'chanpay_url' => 'Chanpay Url',
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
    	$postData['transtime'] = $postData['create_time'] = $postData['modify_time'] = date('Y-m-d H:i:s');
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