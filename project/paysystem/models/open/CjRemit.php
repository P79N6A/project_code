<?php

namespace app\models\open;

use Yii;

/**
 * This is the model class for table "cj_remit".
 *
 * @property integer $id
 * @property integer $aid
 * @property integer $channel_id
 * @property string $req_id
 * @property string $batch_no
 * @property string $client_id
 * @property string $cj_orderid
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
 * @property integer $card_type
 * @property integer $account_type
 * @property string $guest_account_bank
 * @property string $guest_account
 * @property string $guest_account_province
 * @property string $guest_account_city
 * @property string $guest_account_bank_branch
 * @property string $settlement_desc
 * @property string $callbackurl
 * @property string $create_time
 * @property string $modify_time
 * @property string $remit_time
 * @property string $sub_remit_time
 * @property string $query_time
 * @property integer $query_num
 * @property integer $version
 */
class CjRemit extends \app\models\open\OpenBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cj_remit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'req_id', 'client_id', 'rsp_status', 'identityid', 'user_mobile', 'guest_account_name', 'card_type', 'guest_account_bank', 'guest_account', 'guest_account_province', 'guest_account_city', 'guest_account_bank_branch', 'callbackurl', 'create_time', 'modify_time', 'query_time'], 'required'],
            [['aid', 'channel_id', 'remit_type', 'remit_status', 'card_type', 'account_type', 'query_num', 'version'], 'integer'],
            [['settle_amount', 'settle_fee', 'real_amount'], 'number'],
            [['create_time', 'modify_time', 'remit_time', 'sub_remit_time', 'query_time'], 'safe'],
            [['req_id', 'batch_no', 'cj_orderid'], 'string', 'max' => 40],
            [['client_id', 'guest_account'], 'string', 'max' => 30],
            [['rsp_status'], 'string', 'max' => 50],
            [['rsp_status_text', 'settlement_desc', 'callbackurl'], 'string', 'max' => 255],
            [['identityid'], 'string', 'max' => 20],
            [['user_mobile', 'guest_account_name', 'guest_account_bank'], 'string', 'max' => 60],
            [['guest_account_province', 'guest_account_city', 'guest_account_bank_branch'], 'string', 'max' => 150],
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
            'channel_id' => 'Channel ID',
            'req_id' => 'Req ID',
            'batch_no' => 'Batch No',
            'client_id' => 'Client ID',
            'cj_orderid' => 'Cj Orderid',
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
            'card_type' => 'Card Type',
            'account_type' => 'Account Type',
            'guest_account_bank' => 'Guest Account Bank',
            'guest_account' => 'Guest Account',
            'guest_account_province' => 'Guest Account Province',
            'guest_account_city' => 'Guest Account City',
            'guest_account_bank_branch' => 'Guest Account Bank Branch',
            'settlement_desc' => 'Settlement Desc',
            'callbackurl' => 'Callbackurl',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'remit_time' => 'Remit Time',
            'sub_remit_time' => 'Sub Remit Time',
            'query_time' => 'Query Time',
            'query_num' => 'Query Num',
            'version' => 'Version',
        ];
    }
    public static function getStatus(){
        return [
            0=>'初始化',
            1=>'出款请求中',
            3=>'受理中',
            4=>'查询请求中',
            6=>'成功',
            11=>'失败',
            12=>'无响应',
            13=>'查询超限'
        ];
    }
    public function updateData($data)
    {
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        } else {
            return $result;
        }
    }

    public function getRemitOne($client_id)
    {
        return self::find()->where(['client_id' => $client_id])->one();
    }

    public function getOrderId($orderid)
    {
        if (empty($orderid)){
            return false;
        }
        return self::find()->where(['client_id'=>$orderid])->one();
    }

}