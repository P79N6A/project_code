<?php

namespace app\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_loan_address".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_no
 * @property string $gps_id
 * @property string $create_time
 */
class YiLoanAddress extends \app\models\yyy\YyyBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_loan_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_no', 'gps_id', 'create_time'], 'required'],
            [['user_id','gps_id'], 'integer'],
            [['create_time'], 'safe'],
            [['loan_no'], 'string', 'max' => 32]
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
            'loan_no' => '借款唯一识别码NUM',
            'gps_id' => '关联yi_address表id',
            'create_time' => '获取地理位置的时间',
        ];
    }

    /**
     * 表关联关系
     */
    public function getAddress() {
        return $this->hasOne(YiAddress::className(), ['id' => 'gps_id']);
    }
    /**
     * 借款地址
     */
    public function getLoanAddress($where)
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
}
