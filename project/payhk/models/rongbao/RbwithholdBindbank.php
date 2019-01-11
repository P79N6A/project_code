<?php

namespace app\models\rongbao;

use Yii;
// use app\models\Payorder;
use app\common\Func;
use \app\common\Logger;
use app\models\BindBank;

/**
 * This is the model class for table "rb_withhold_bindbank".
 *
 * @property integer $id
 * @property integer $aid
 * @property integer $channel_id
 * @property string $identityid
 * @property string $cli_identityid
 * @property string $requestid
 * @property string $cardno
 * @property string $bankname
 * @property string $idcardtype
 * @property string $idcard
 * @property string $name
 * @property string $phone
 * @property string $userip
 * @property string $create_time
 * @property string $modify_time
 * @property string $error_code
 * @property string $error_msg
 * @property integer $status
 */
class RbwithholdBindbank extends \app\models\BasePay
{   
    // 支付状态
    const STATUS_INIT = 0;
    const STATUS_REQOK = 1; // 请求成功
    const STATUS_BINDOK = 2; // 绑定成功
    const STATUS_DOING = 4; //处理中
    const STATUS_BINDFAIL = 11; // 绑定失败
    const STATUS_REQNO = 12; // 请求失败
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rb_withhold_bindbank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'channel_id', 'identityid', 'requestid', 'cardno', 'bankname', 'idcard', 'name', 'phone', 'userip', 'create_time', 'modify_time'], 'required'],
            [['aid', 'channel_id', 'status'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['identityid', 'idcard', 'name', 'phone', 'userip', 'error_code'], 'string', 'max' => 20],
            [['cli_identityid', 'requestid', 'cardno', 'bankname'], 'string', 'max' => 50],
            [['idcardtype'], 'string', 'max' => 10],
            [['error_msg'], 'string', 'max' => 100],
            [['requestid'], 'unique']
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
            'identityid' => 'Identityid',
            'cli_identityid' => 'Cli Identityid',
            'requestid' => 'Requestid',
            'cardno' => 'Cardno',
            'bankname' => 'Bankname',
            'idcardtype' => 'Idcardtype',
            'idcard' => 'Idcard',
            'name' => 'Name',
            'phone' => 'Phone',
            'userip' => 'Userip',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'error_code' => 'Error Code',
            'error_msg' => 'Error Msg',
            'status' => 'Status',
        ];
    }

    public function saveCard($data) {
        //1 生成结果
        $userip = Func::get_client_ip();
        if (!$userip) {
            $userip = '127.0.0.1';
        }

        $time = date('Y-m-d H:i:s');
        $request_id = "p" . $data['channel_id'] . '_' . time() . '_' . rand(10000, 99999);

        $cli_identityid = $this->getPayIdentityid(
                    $data['identityid'], 
                    $data['cardno'], 
                    $data['channel_id']
                );

        $postData = [
            'aid' => $data['aid'],
            'channel_id' =>  $data['channel_id'], 
            'identityid' =>  $data['identityid'], 
            'cli_identityid' =>  $cli_identityid, 
            'requestid' => $request_id, 
            'cardno' => $data['cardno'],
            'bankname' => $data['bankname'],
            'idcard' => $data['idcard'],
            'name' => $data['name'], 
            'phone' => $data['phone'],
            'userip' => $userip,
            'create_time' => $time,
            'modify_time' => $time,
            'status' => 0,
        ];

        //2 是否已经绑定
        // 检测是否已经成功绑定过该卡
        $isBind = $this->getSameUserCard(
            $postData['aid'],
            $postData['channel_id'],
            $postData['identityid'],
            $postData['cardno']
        );
        if ($isBind) {
            return true;
        }

        //3. 字段检查是否正确
        if ($errors = $this->chkAttributes($postData)) {
			Logger::dayLog(
                'rbwithhold/bindcard',
                '提交数据', $postData,
                '失败原因', $errors
            );
            return $this->returnError(false, "数据保存失败");
        }
        return $this->save();
    }

    public function getPayIdentityid($identityid, $cardno, $channel_id) {
        if (!$identityid || !$cardno) {
            return '';
        }
        $card_top = substr($cardno, 0, 6);
        $card_last = substr($cardno, -4);
        $identityid = $identityid . '-' . $card_top . $card_last;

        $cli_identityid = Func::toYeepayCode($identityid, $channel_id); 
        return $cli_identityid;
    }

    public function saveRspStatus($rbResult) {
        //1 处理最终结果
        $this->modify_time = date('Y-m-d H:i:s');
        if (is_array($rbResult) && $rbResult['result_code']!='0000') {
            // 失败时处理逻辑
            $this->error_code = $rbResult['result_code'];
            $this->error_msg = $rbResult['result_msg'];
            $this->status = RbwithholdBindbank::STATUS_BINDFAIL; //绑定失败
            $result = $this->save();
        } else {
            // 成功时处理逻辑
            $this->status = RbwithholdBindbank::STATUS_BINDOK; //确认绑定成功
            $result = $this->save();
            //生成一条主绑卡成功记录
            $BindBank = new BindBank();
            $BindBank ->succBindBank($this);
        }
        //2. 纪录数据库错误日志
        if (!$result) {
            Logger::dayLog('Rbwithhold','bindbank/saveRspStatus', $rbResult,  $this->errors  );
            return false;
        }
        return true;
    }

    public function getSameUserCard($aid,$channel_id, $identityid, $cardno) {
        $where = [
            'aid'=>$aid,
            'channel_id' => $channel_id,
            'identityid' => $identityid,
            'cardno' => $cardno,
            'status' => self::STATUS_BINDOK,
        ];
        $one = static::find()->where($where)->limit(1) -> one();
        return $one;
    }
}
