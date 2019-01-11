<?php

namespace app\models\open;

use Yii;

/**
 * This is the model class for table "jxl_request".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $name
 * @property string $idcard
 * @property string $phone
 * @property string $token
 * @property string $account
 * @property string $password
 * @property string $query_pwd
 * @property string $captcha
 * @property string $method
 * @property string $type
 * @property string $website
 * @property string $response_type
 * @property integer $process_code
 * @property integer $source
 * @property integer $from
 * @property string $create_time
 * @property string $modify_time
 * @property integer $result_status
 * @property string $result
 * @property string $contacts
 * @property string $callbackurl
 * @property integer $client_status
 */
class OpJxlRequest extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jxl_request';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'name', 'idcard', 'phone', 'token', 'account', 'password', 'captcha', 'type', 'website', 'response_type', 'result', 'contacts', 'callbackurl'], 'required'],
            [['aid', 'process_code', 'source', 'from', 'create_time', 'modify_time', 'result_status', 'client_status'], 'integer'],
            [['result', 'contacts'], 'string'],
            [['name', 'token', 'account', 'password', 'query_pwd', 'website'], 'string', 'max' => 50],
            [['idcard', 'phone', 'captcha', 'method', 'type', 'response_type'], 'string', 'max' => 20],
            [['callbackurl'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => '应用id',
            'name' => '姓名',
            'idcard' => '银行卡',
            'phone' => '手机号',
            'token' => 'token',
            'account' => '帐号',
            'password' => '密码',
            'query_pwd' => '网站查询密码',
            'captcha' => '网站动态验证',
            'method' => '融下一步请求接口的名字',
            'type' => 'SUBMIT_CAPTCHA（提交动态验证码） |  RESEND_CAPTCHA（重发动态验证码）',
            'website' => '网站英文名称',
            'response_type' => '1 CONTROL控制类型的响应结果; 2 ERROR错误类型的响应结果 ;3 RUNNING 正在运行',
            'process_code' => '流程码，见文档',
            'source' => '来源:1:XIANHUAHUA; 2:kuaip',
            'from' => '来源 1: H5  2: app',
            'create_time' => '创建时间',
            'modify_time' => '更新时间',
            'result_status' => '1 采集完成',
            'result' => '采集结果',
            'contacts' => '常见联系人',
            'callbackurl' => '回调地址',
            'client_status' => '客户端响应状态',
        ];
    }

    public function getJxlRequest($where)
    {
        return $this->find()->where($where)->orderby('ID DESC')->limit(1)->one();
    }
}
