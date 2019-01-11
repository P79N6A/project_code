<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "yi_address". 
 * 
 * @property string $id
 * @property string $user_id
 * @property double $latitude
 * @property double $longitude
 * @property string $address
 * @property integer $type
 * @property integer $come_from
 * @property string $create_time
 */
class Address extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc 
     */
    public static function tableName() {
        return 'yi_address';
    }

    /**
     * @inheritdoc 
     */
    public function rules() {
        return [
            [['user_id', 'latitude', 'longitude', 'address', 'come_from', 'create_time'], 'required'],
            [['user_id', 'type', 'come_from'], 'integer'],
            [['latitude', 'longitude'], 'number'],
            [['create_time'], 'safe'],
            [['address'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc 
     */
    public function attributeLabels() {
        return [
            'id' => '主键，自增',
            'user_id' => '用户id',
            'latitude' => '纬度',
            'longitude' => '经度',
            'address' => '街道地址',
            'type' => '行为：默认为0，暂时预留字段',
            'come_from' => '获取来源：1、IOS,2、安卓',
            'create_time' => '获取地理位置的时间',
        ];
    }

    public function addAddress($user_id, $latitude, $longitude, $address, $come_from = 1) {
        if (!intval($user_id)) {
            return false;
        }
        $user = User::findOne($user_id);
        if (empty($user)) {
            return false;
        }
        $this->user_id = $user_id;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->address = $address;
        $this->come_from = $come_from;
        $this->create_time = date('Y-m-d H:i:s');
        return $this->save();
    }

}
