<?php

namespace app\modules\balance\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_user_rate".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property integer $user_rate_id
 * @property string $last_modify_time
 * @property string $create_time
 */
class User_rate extends YyyBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_rate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'type', 'user_rate_id'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'type' => 'Type',
            'user_rate_id' => 'User Rate ID',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }
}