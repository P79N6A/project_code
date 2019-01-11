<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_user_label".
 *
 * @property string $id
 * @property string $mobile
 * @property string $label
 * @property string $create_time
 */
class Rate_setting extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_rate_setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time'], 'safe'],
            [['interest'], 'number'],
            [['day', 'rate_id', 'type', 'rate'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'day' => 'User Id',
            'rate_id' => 'Rate_id',
            'type' => 'Type',
            'rate' => 'Rate',
            'interest' => 'Interest',
            'create_time' => 'Create Time',
        ];
    }
}
