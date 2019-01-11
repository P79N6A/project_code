<?php

namespace app\models;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "authbank_order".
 *
 * @property integer $id
 * @property integer $channel_id
 * @property string $idcard
 * @property string $username
 * @property string $cardno
 * @property integer $card_type
 * @property string $phone
 * @property string $bankname
 * @property string $bankcode
 * @property string $create_time
 * @property string $modify_time
 * @property integer $status
 */
class AuthbankOrder extends BaseModel
{   
    // 状态
    const STATUS_INIT = 0;   //初始状态
    const STATUS_SUCC = 1;   //成功 
    const STATUS_FAIL = 2;   //失败
    const STATUS_UNBIND = 3; // 解绑
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'authbank_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channel_id', 'idcard', 'username', 'cardno', 'phone', 'create_time', 'modify_time'], 'required'],
            [['channel_id', 'card_type', 'status'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['idcard', 'username', 'phone', 'bankcode'], 'string', 'max' => 20],
            [['cardno', 'bankname'], 'string', 'max' => 50],
            [['cardno'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'channel_id' => 'Channel ID',
            'idcard' => 'Idcard',
            'username' => 'Username',
            'cardno' => 'Cardno',
            'card_type' => 'Card Type',
            'phone' => 'Phone',
            'bankname' => 'Bankname',
            'bankcode' => 'Bankcode',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'status' => 'Status',
        ];
    }

    /**
	 * 根据银行卡号查询数据
	 * @param $cardno 银行卡号
	 * @return object | null
	 */
	public function getByCardno($cardno)
    {
		if(!$cardno){
			return null;
		}
        $where = [
            'cardno' => $cardno,
            // 'status' => self::STATUS_SUCC
            ];
		return static::find() -> where($where) ->one();
	}

    /**
	 * 查询四要素是否正确
	 * @param obj $oCard 四要素
	 * @return bool
	 */
	public function chk($postData){
        return 	$this->cardno	== $postData['cardno'] &&
                $this->idcard	== $postData['idcard'] &&
                $this->username == $postData['username'] &&
                $this->phone    == $postData['phone'];
	}

    /**
     * 保存到数据库中
     */
    public function savaData($postData) {
        $data = [
            'channel_id' => $postData['channelId'],
            'cardno' => $postData['cardno'],
            'idcard' => $postData['idcard'],
            'username' => $postData['username'],
            'card_type' =>isset($postData['cardType']) ? $postData['cardType']:0,
            'phone' => $postData['phone'],
            'bankname' => isset($postData['bankName']) ? $postData['bankName']:'',
            'bankcode' => isset($postData['bankCode']) ? $postData['bankCode']:'',
            'status' => static::STATUS_SUCC,
            'create_time' => date('Y-m-d H:i:s'),
            'modify_time' => date('Y-m-d H:i:s'),
        ];

        $error = $this->chkAttributes($data);
        if ($error) {
            Logger::dayLog('authbankorder','记录保存失败', $error);
            return $this->returnError(false, implode("|", $error));
        }
        return $this->save();
    }

    /**
     * 更新
     */
    public function updateData($postData,$status) {
        $this->channel_id = $postData['channelId'];
        $this->phone = $postData['phone'];
        $this->status = $status;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

}
