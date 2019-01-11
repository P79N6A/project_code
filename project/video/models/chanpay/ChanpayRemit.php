<?php

namespace app\models\chanpay;

use Yii;

/**
 * This is the model class for table "chanpay_remit".
 *
 * @property string $id
 * @property integer $aid
 * @property string $req_id
 * @property string $client_id
 * @property string $settle_amount
 * @property string $settle_fee
 * @property string $real_amount
 * @property integer $remit_type
 * @property integer $remit_status
 * @property string $rsp_status
 * @property string $rsp_status_text
 * @property string $identityid
 * @property string $user_mobile
 * @property string $guest_account_name
 * @property integer $account_type
 * @property string $guest_account_bank
 * @property string $guest_account
 * @property string $settlement_desc
 * @property string $callbackurl
 * @property string $create_time
 * @property string $modify_time
 * @property string $remit_time
 * @property string $query_time
 * @property integer $query_num
 * @property string $version
 */
class ChanpayRemit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'chanpay_remit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'req_id', 'client_id', 'rsp_status', 'rsp_status_text', 'identityid', 'user_mobile', 'guest_account_name', 'guest_account_bank', 'guest_account', 'callbackurl', 'create_time', 'modify_time', 'query_time'], 'required'],
            [['aid', 'remit_type', 'remit_status', 'account_type', 'query_num', 'version'], 'integer'],
            [['settle_amount', 'settle_fee', 'real_amount'], 'number'],
            [['create_time', 'modify_time', 'remit_time', 'query_time'], 'safe'],
            [['req_id'], 'string', 'max' => 40],
            [['client_id', 'guest_account'], 'string', 'max' => 30],
            [['rsp_status'], 'string', 'max' => 50],
            [['rsp_status_text', 'settlement_desc', 'callbackurl'], 'string', 'max' => 255],
            [['identityid'], 'string', 'max' => 20],
            [['user_mobile', 'guest_account_name', 'guest_account_bank'], 'string', 'max' => 60],
            [['req_id'], 'unique'],
            [['client_id'], 'unique']
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
            'req_id' => 'Req ID',
            'client_id' => 'Client ID',
            'settle_amount' => 'Settle Amount',
            'settle_fee' => 'Settle Fee',
            'real_amount' => 'Real Amount',
            'remit_type' => 'Remit Type',
            'remit_status' => 'Remit Status',
            'rsp_status' => 'Rsp Status',
            'rsp_status_text' => 'Rsp Status Text',
            'identityid' => 'Identityid',
            'user_mobile' => 'User Mobile',
            'guest_account_name' => 'Guest Account Name',
            'account_type' => 'Account Type',
            'guest_account_bank' => 'Guest Account Bank',
            'guest_account' => 'Guest Account',
            'settlement_desc' => 'Settlement Desc',
            'callbackurl' => 'Callbackurl',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'remit_time' => 'Remit Time',
            'query_time' => 'Query Time',
            'query_num' => 'Query Num',
            'version' => 'Version',
        ];
    }
    
    // 保存请求的数据
    public function saveOrder($postData) {
    	//1 数据验证
    	if (!is_array($postData) || empty($postData)) {
    		return $this->returnError(false, "数据不能为空");
    	}
    	if (empty($postData['req_id'])) {
    		return $this->returnError(false, "订单不能为空");
    	}
    	if (empty($postData['aid'])) {
    		return $this->returnError(false, "应用id不能为空");
    	}
    	$postData['create_time'] = $postData['modify_time'] = date('Y-m-d H:i:s');
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
