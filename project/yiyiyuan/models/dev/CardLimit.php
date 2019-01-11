<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "yi_card_limit".
 *
 * @property string $id
 * @property string $bank_name
 * @property integer $card_type
 * @property integer $status
 * @property string $start_time
 * @property string $end_time
 * @property integer $type
 * @property string $operation
 * @property string $create_time
 */
class CardLimit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_card_limit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank_name' => 'Bank Name',
            'card_type' => 'Card Type',
            'status' => 'Status',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'type' => 'Type',
            'operation' => 'Operation',
            'create_time' => 'Create Time',
        ];
    }
}
