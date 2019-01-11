<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "yp_bindbank_confrim".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $requestid
 * @property string $create_time
 * @property string $modify_time
 * @property integer $error_code
 * @property string $error_msg
 * @property string $codesender
 * @property string $smscode
 * @property string $validatecode
 * @property integer $status
 */
class YpBindbankConfrim extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yp_bindbank_confrim';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'create_time', 'modify_time', 'error_code', 'status'], 'integer'],
            [['aid', 'requestid', 'codesender',], 'required'],
            [['requestid'], 'string', 'max' => 50],
            [['error_msg'], 'string', 'max' => 100],
            [['codesender'], 'string', 'max' => 20],
            [['smscode', 'validatecode'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'aid' => '应用id',
            'requestid' => '商户生成的唯一绑卡请求号，最长',
            'create_time' => '发送确认绑卡时间',
            'modify_time' => '最后修改时间',
            'error_code' => '(内部)易宝返回错误码',
            'error_msg' => '(内部)易宝返回错误描述',
            'codesender' => '短信发送方 YEEPAY：易宝发送 | BANK：银行发送  | MERCHANT：商户发送',
            'smscode' => '短信验证码',
            'validatecode' => '用户请求的验证码',
            'status' => '0:默认 1:确认绑定成功; 11:绑定失败',
        ];
    }
	/**
	 * 根据请求$requestid查询
	 */
	public function getByRequestid($requestid){
		if(!$requestid){
			return null;
		}
		return static::find()->where(['requestid'=>$requestid])->one();
	}
	
}
