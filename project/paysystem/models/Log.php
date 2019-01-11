<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%log}}".
 *
 * @property integer $id
 * @property integer $app_id
 * @property integer $service_id
 * @property string $req_url
 * @property string $req_ip
 * @property string $req_encrypt
 * @property string $req_info
 * @property integer $rsp_status
 * @property string $rsp_info
 * @property integer $create_time
 * @property integer $modify_time
 */
class Log extends  \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_id', 'service_id', 'rsp_status', 'create_time', 'modify_time'], 'integer'],
            [['req_url', 'req_ip', 'req_encrypt', 'req_info', 'rsp_info'], 'required'],
            [['req_encrypt', 'req_info', 'rsp_info'], 'string'],
            [['req_url'], 'string', 'max' => 50],
            [['req_ip'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => '应用id',
            'service_id' => '服务id',
            'req_url' => '请求的相对地址',
            'req_ip' => '请求IP',
            'req_encrypt' => '请求原信息(序列化)',
            'req_info' => '请求解密信息(序列化)',
            'rsp_status' => '响应状态0成功，其余失败',
            'rsp_info' => '响应信息(序列化)',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
        ];
    }
}
