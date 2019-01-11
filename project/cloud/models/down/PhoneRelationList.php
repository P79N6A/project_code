<?php

namespace app\models\down;

use Yii;

/**
 * This is the model class for table "phone_relation_list".
 *
 * @property string $id
 * @property string $user_phone
 * @property string $phone
 * @property integer $type
 */
class PhoneRelationList extends DownBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'phone_relation_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_phone', 'phone'], 'required'],
            [['type'], 'integer'],
            [['user_phone', 'phone'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_phone' => '用户手机号',
            'phone' => '手机号',
            'type' => '1：详单，2：通讯录',
        ];
    }

    public function getRelationByphones($where){
        return $this->find()->where($where)->asArray()->limit(5000)->orderBy('id DESC')->all();
    }
}
