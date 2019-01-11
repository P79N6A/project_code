<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tmp_black".
 *
 * @property string $user_id
 */
class TmpBlack extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tmp_black';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
        ];
    }

    public function getTmpbBlack($where)
    {
        $black = static::find()
            ->where($where)
            ->asArray()
            ->count();
        return $black;
    }
}
