<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_activity_share".
 *
 * @property string $id
 * @property string $user_id
 * @property string $mobile
 * @property string $create_time
 */
class Activity_share extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_activity_share';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'mobile'], 'required'],
            [['user_id'], 'integer'],
            [['create_time'], 'safe'],
            [['mobile'], 'string', 'max' => 20],
            [['mobile'], 'unique']
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
            'mobile' => 'Mobile',
            'create_time' => 'Create Time',
        ];
    }
}
