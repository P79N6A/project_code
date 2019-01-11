<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class Sms extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_sms';
    }

    public function rules()
    {
        return [
            [['create_time'], 'safe'],
            [['sms_type'], 'integer'],
            [['recive_mobile'], 'string', 'max' => 11],
            [['content'], 'string', 'max' => 300]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'recive_mobile' => 'Recive Mobile',
            'content' => 'Content',
            'create_time' => 'Create Time',
            'sms_type' => 'Sms Type',
        	'code' => 'Sms code',
        ];
    }
    
    /**
     * 统计当天注册短信发送次数
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
     * 添加一条新的短信信息
     */
    public function addSms($recive_mobile,$content,$type,$code,$send_mobile='')
    {
    	$sms = new Sms();
    	$sms->content = $content;
    	$sms->recive_mobile = $recive_mobile;
    	if(!empty($send_mobile)){
    		$sms->send_mobile = $send_mobile;
    	}
    	$sms->create_time = date('Y-m-d H:i:s');
    	$sms->sms_type = $type;
    	$sms->code = $code;
    	
    	if($sms->save()){
    		return true;
    	}else{
    		return false;
    	}
    }
}
