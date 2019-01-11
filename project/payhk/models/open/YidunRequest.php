<?php

namespace app\models\open;

use Yii;

/**
 * This is the model class for table "yidun_request".
 *
 * @property integer $id
 * @property integer $requestid
 * @property integer $aid
 * @property string $name
 * @property string $idcard
 * @property string $phone
 * @property string $token
 * @property string $password
 * @property string $query_pwd
 * @property integer $is_smscode
 * @property integer $is_imgcode
 * @property string $captcha_path
 * @property integer $is_smscodejldx
 * @property string $bizno
 * @property string $orgbizno
 * @property integer $process_code
 * @property string $website
 * @property integer $source
 * @property integer $create_time
 * @property integer $modify_time
 * @property integer $result_status
 * @property string $callbackurl
 * @property integer $client_status
 * @property integer $from
 */
class YidunRequest extends \app\models\open\OpenBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yidun_request';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['requestid', 'aid', 'is_smscode', 'is_imgcode', 'is_smscodejldx', 'process_code', 'source', 'create_time', 'modify_time', 'result_status', 'client_status', 'from'], 'integer'],
            [['name', 'idcard', 'phone', 'password', 'website', 'callbackurl'], 'required'],
            [['name', 'password', 'query_pwd', 'bizno', 'orgbizno', 'website'], 'string', 'max' => 50],
            [['idcard', 'phone'], 'string', 'max' => 20],
            [['token', 'captcha_path', 'callbackurl'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'requestid' => 'Requestid',
            'aid' => 'Aid',
            'name' => 'Name',
            'idcard' => 'Idcard',
            'phone' => 'Phone',
            'token' => 'Token',
            'password' => 'Password',
            'query_pwd' => 'Query Pwd',
            'is_smscode' => 'Is Smscode',
            'is_imgcode' => 'Is Imgcode',
            'captcha_path' => 'Captcha Path',
            'is_smscodejldx' => 'Is Smscodejldx',
            'bizno' => 'Bizno',
            'orgbizno' => 'Orgbizno',
            'process_code' => 'Process Code',
            'website' => 'Website',
            'source' => 'Source',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'result_status' => 'Result Status',
            'callbackurl' => 'Callbackurl',
            'client_status' => 'Client Status',
            'from' => 'From',
        ];
    }
    public static function getResultStatus(){
        return [
            0=>'初始',
            1=>'采集完成',
            2=>'采集失败'
        ];
    }
    public static function getClientStatus(){
        return [
            0=>'未响应',
            1=>'已响应'
        ]; 
    }
}