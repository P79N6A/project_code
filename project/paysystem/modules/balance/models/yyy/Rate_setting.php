<?php

namespace app\modules\balance\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_rate_setting".
 *
 * @property string $id
 * @property integer $day
 * @property string $rate_id
 * @property integer $type
 * @property integer $rate
 * @property double $interest
 * @property string $create_time
 */
class Rate_setting extends YyyBase
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
            [['day'], 'required'],
            [['day', 'rate_id', 'type', 'rate'], 'integer'],
            [['interest'], 'number'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'day' => 'Day',
            'rate_id' => 'Rate ID',
            'type' => 'Type',
            'rate' => 'Rate',
            'interest' => 'Interest',
            'create_time' => 'Create Time',
        ];
    }
}