<?php

namespace app\models\service;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "white_ip".
 *
 * @property integer $id
 * @property string $service_id
 * @property string $ip
 * @property integer $status
 * @property string $create_time
 */
class WhiteIp extends \app\models\repo\CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'white_ip';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip', 'create_time'], 'required'],
            [['status'], 'integer'],
            [['create_time'], 'safe'],
            [['service_id'], 'string', 'max' => 32],
            [['ip'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键ID',
            'service_id' => '请求方id',
            'ip' => 'IP地址',
            'status' => '0:未启用, 1:启用',
            'create_time' => '创建时间',
        ];
    }

    public function validIp($service_id) {
        $ip_info = $this->find()->select('ip')->where(['service_id'=>$service_id,'status'=>1])->limit(100)->all();
        if (empty($ip_info)) {
            return [];
        }
        $ips = ArrayHelper::getColumn($ip_info,'ip');
        return $ips;
    }
}
