<?php

namespace app\models\xinyan;

use app\common\Func;
use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "pay_xy_bindbank".
 *
 * @property integer $id
 * @property integer $aid
 * @property integer $channel_id
 * @property string $identityid
 * @property string $cli_identityid
 * @property string $requestid
 * @property string $cardno
 * @property string $bankname
 * @property string $bankcode
 * @property string $idcardtype
 * @property string $idcard
 * @property string $name
 * @property string $phone
 * @property string $userip
 * @property string $create_time
 * @property string $modify_time
 * @property integer $code
 * @property string $desc
 * @property string $trade_no
 * @property string $org_code
 * @property string $org_desc
 * @property string $bank_id
 * @property string $bank_description
 * @property string $fee
 */
class PayXyBindbank extends \app\models\BaseModel
{
    // 支付状态
    const STATUS_SUCCESS = 0;//亲，认证成功（收费）
    const STATUS_ONE = 1;    //亲，认证信息不一致（收费）
    const STATUS_THREE = 3;  //亲，认证失败（不收费）
    const STATUS_NINE = 9;   //亲，其他异常（不收费）
    const STATUS_INIT = -1; //初始
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_xy_bindbank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'channel_id', 'identityid', 'requestid', 'cardno', 'bankname', 'bankcode', 'idcard', 'name', 'phone', 'userip', 'create_time', 'modify_time'], 'required'],
            [['aid', 'channel_id', 'code'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['identityid', 'bankcode', 'idcard', 'name', 'phone', 'userip', 'bank_id'], 'string', 'max' => 20],
            [['cli_identityid', 'requestid', 'cardno', 'bankname', 'bank_description'], 'string', 'max' => 50],
            [['idcardtype'], 'string', 'max' => 10],
            [['desc', 'trade_no', 'org_code', 'org_desc'], 'string', 'max' => 255],
            [['fee'], 'string', 'max' => 5],
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
            'bankcode' => 'Bankcode',
            'idcardtype' => 'Idcardtype',
            'idcard' => 'Idcard',
            'name' => 'Name',
            'phone' => 'Phone',
            'userip' => 'Userip',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'code' => 'Code',
            'desc' => 'Desc',
            'trade_no' => 'Trade No',
            'org_code' => 'Org Code',
            'org_desc' => 'Org Desc',
            'bank_id' => 'Bank ID',
            'bank_description' => 'Bank Description',
            'fee' => 'Fee',
        ];
    }

/**
     * 判断某帐号是否成功绑定过此卡
     * @param $channel_id
     * @param $identityid
     * @param $cardno
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getSameUserCard($channel_id, $identityid, $cardno) {
        $where = [
            'channel_id' => $channel_id,
            'identityid' => $identityid,
            'cardno' => $cardno,
            'code' => self::STATUS_SUCCESS,
        ];
        $one = static::find()->where($where)->limit(1) -> one();
        return $one;
    }

    public function getSameUserCardOne($channel_id, $identityid, $cardno) {
        $where = [
            'channel_id' => $channel_id,
            'identityid' => $identityid,
            'cardno' => $cardno,
            'code'  => self::STATUS_INIT,
        ];
        $one = static::find()->where($where)->limit(1)->orderBy("id desc") -> one();
        return $one;
    }

    /**
     * 从主订单进行绑卡操作
     * @param $data
     * @return bool|false|null
     */
    public function saveCard($data)
    {
        if (empty($data)){
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $postData = [
            'aid'               => ArrayHelper::getValue($data, 'aid'),
            'channel_id'        => ArrayHelper::getValue($data, 'channel_id'),
            'identityid'        => ArrayHelper::getValue($data, 'identityid'),
            'cli_identityid'    => ArrayHelper::getValue($data, 'cli_identityid'),
            'requestid'         => ArrayHelper::getValue($data, 'requestid'),
            'cardno'            => ArrayHelper::getValue($data, 'cardno'),
            'bankname'          => ArrayHelper::getValue($data, 'bankname'),
            'bankcode'          => ArrayHelper::getValue($data, 'bankcode'),
            'idcardtype'        => (string)ArrayHelper::getValue($data, 'idcardtype'),
            'idcard'            => ArrayHelper::getValue($data, 'idcard'),
            'name'              => ArrayHelper::getValue($data, 'name'),
            'phone'             => ArrayHelper::getValue($data, 'phone'),
            'userip'            => ArrayHelper::getValue($data, 'userip'),
            'create_time'       => $time,
            'modify_time'       => $time,
        ];

        //3. 字段检查是否正确
        if ($errors = $this->chkAttributes($postData)) {
            Logger::dayLog(
                'xinyan/bindcard',
                '提交数据', $postData,
                '失败原因', $errors
            );
            return $this->returnError(false, "数据保存失败");
        }
        return $this->save();
    }

    /**
     * 处理最终绑卡结果
     * @param $bfresult
     * @return bool
     */
    public function saveRspStatus($bfresult) {
        //1 处理最终结果
        $this->modify_time = date('Y-m-d H:i:s');
        $isError = ArrayHelper::getValue($bfresult, 'success');
        if (!$isError) {
            // 失败时处理逻辑
            $this->org_code = (string)$bfresult['errorCode'];
            $this->org_desc = (string)$bfresult['errorMsg'];
            $this->code = self::STATUS_NINE; //绑定失败
            $result = $this->save();
        } else {
            $data = ArrayHelper::getValue($bfresult, 'data');
            $code = ArrayHelper::getValue($data, 'code');
            $this->code = ArrayHelper::getValue($data, 'code');
            $this->desc = ArrayHelper::getValue($data, 'desc');
            $this->trade_no = ArrayHelper::getValue($data, 'trade_no');
            $this->org_code = ArrayHelper::getValue($data, 'org_code');
            $this->org_desc = ArrayHelper::getValue($data, 'org_desc');
            $this->bank_id = ArrayHelper::getValue($data, 'bank_id');
            $this->bank_description = ArrayHelper::getValue($data, 'bank_description');
            $this->fee = ArrayHelper::getValue($data, 'fee');
            // 成功时处理逻辑
            $this->code = $code; //确认绑定成功
            $result = $this->save();
        }

        //2. 纪录数据库错误日志
        if (!$result) {
            Logger::dayLog(  'xinyan/payxybindbank',   'bindbank/saveRspStatus', $bfresult,  $this->errors  );
            return false;
        }
        return true;
    }

}