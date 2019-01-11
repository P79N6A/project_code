<?php

namespace app\models\news;

use app\models\BaseModel;
use app\commonapi\Logger;
use Yii;

/**
 * This is the model class for table "yi_sms".
 *
 * @property string $id
 * @property string $code
 * @property string $send_mobile
 * @property string $recive_mobile
 * @property string $content
 * @property integer $sms_type
 * @property string $create_time
 */
class Sms extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_sms';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sms_type'], 'integer'],
            [['create_time'], 'safe'],
            [['code'], 'string', 'max' => 10],
            [['send_mobile', 'recive_mobile'], 'string', 'max' => 16],
            [['content'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'send_mobile' => 'Send Mobile',
            'recive_mobile' => 'Recive Mobile',
            'content' => 'Content',
            'sms_type' => 'Sms Type',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 当天验证码发送次数
     * @param $mobile
     * @return int
     */
    public function smsCount($mobile){
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $sms_count = self::find()->where("recive_mobile='$mobile' and sms_type=2 and create_time >= '$begintime' and create_time <= '$endtime'")->count();
        return $sms_count;
    }

    /**
     * 检验验证码是否正确
     * @return bool
     * @param  string $mobile 手机号
     * @param  string $code 验证码
     * @param  string $type
     * @return bool
     */
    public function chkCode($mobile, $code, $type = "getcode_register_"){
        if(empty($mobile) || empty($code)){
            return FALSE;
        }
        $key = $type . $mobile;
        $code_byredis = Yii::$app->redis->get($key);
        //验证码错误
        if ($code_byredis != $code) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 统计当天短信发送次数
     * @param str $mobile 手机号码
     * @param int $type 验证码发送类型 1注册 2登录
     * @return int 发送次数
     */
    public function getSmsCount($mobile,$type=1)
    {
        if(empty($mobile)){
            return null;
        }
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $sms_count = Sms::find()->where("recive_mobile='$mobile' and sms_type=$type and create_time >= '$begintime' and create_time <= '$endtime'")->count();
        return $sms_count;
    }

    /**
     * 存储短息
     * @param $condition
     * @return bool
     */
    public function save_sms($condition) {
        if( !is_array($condition) || empty($condition) ){
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if($error){
            Logger::daylog('save_sms', 'error', $data);
            return false;
        }
        return $this->save();
    }


}