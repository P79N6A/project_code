<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "st_service".
 *
 * @property string $id
 * @property string $project_name
 * @property string $service_id
 * @property string $auth_key
 * @property string $create_time
 */
class StService extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_name', 'service_id', 'auth_key', 'create_time'], 'required'],
            [['create_time'], 'integer'],
            [['project_name', 'service_id', 'auth_key'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_name' => '商家名称',
            'service_id' => '商家帐号',
            'auth_key' => '加密秘钥（3des）',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 根据service_id获取信息
     */
    public function getByServiceId( $service_id ){
        if( !$service_id ){
            return null;
        }
        return static::find()->where(["service_id"=>$service_id, "status"=> 1])->limit(1)->one();
    }

    
}
