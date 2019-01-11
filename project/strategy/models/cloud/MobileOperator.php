<?php

namespace app\models\cloud;

use Yii;

/**
 * This is the model class for table "mobile_operator".
 *
 * @property string $mob
 * @property string $Province
 * @property string $City
 * @property string $Corp
 * @property string $AreaCode
 * @property string $PostCode
 */
class MobileOperator extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mobile_operator';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mob'], 'required'],
            [['mob', 'AreaCode', 'PostCode'], 'integer'],
            [['Province', 'City', 'Corp'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mob' => 'Mob',
            'Province' => 'Province',
            'City' => 'City',
            'Corp' => 'Corp',
            'AreaCode' => 'Area Code',
            'PostCode' => 'Post Code',
        ];
    }

    public function getOne($where,$select = '*') {
        return $this->find()->select($select)->where($where)->one();
    }
}
