<?php

namespace app\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_address".
 *
 * @property string $id
 * @property string $user_id
 * @property string $latitude
 * @property string $longitude
 * @property string $address
 * @property integer $type
 * @property integer $come_from
 * @property string $create_time
 */
class YiAddress extends \app\models\yyy\YyyBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
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
    public function attributeLabels()
    {
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

    /**
     * 借款地址
     */
    public function getAddressInfo($where)
    {
        return $this->find()->where($where)->limit(1)->orderby('ID DESC')->one();
    }
    /**
     * 上次借款地址
     */
    public function getLastAddress($where)
    {
        return $this->find()->where($where)->limit(1)->orderby('ID DESC')->all();
    }

    /**
     * 表关联关系
     */
    public function getLoanAddress() {
        return $this->hasOne(YiLoanAddress::className(), ['gps_id' => 'id']);
    }
}
