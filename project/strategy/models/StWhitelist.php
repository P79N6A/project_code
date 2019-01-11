<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "st_whitelist".
 *
 * @property integer $id
 * @property integer $service_id
 * @property string $ip
 * @property integer $status
 * @property string $create_time
 */
class StWhitelist extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_whitelist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'status', 'create_time'], 'integer'],
            [['ip', 'create_time'], 'required'],
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
            'service_id' => '服务ID',
            'ip' => 'IP地址',
            'status' => '0:未启用, 1:启用',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 根据service_id获取信息
     */
    public function getWhiteByServiceId( $service_id ){
        if( !$service_id ){
            return null;
        }
        $res = static::find()->select('ip')->where(["service_id"=>$service_id, "status"=> 1])->asArray()->limit(100)->all();
        $ips = ArrayHelper::getColumn($res,'ip',[]);
        return $ips;
    }
}
