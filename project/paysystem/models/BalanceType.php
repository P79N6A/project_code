<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "balance_type".
 *
 * @property string $id
 * @property string $cp_name
 * @property integer $type
 * @property integer $status
 * @property string $tip
 */
class BalanceType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'balance_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cp_name', 'tip'], 'required'],
            [['type', 'status'], 'integer'],
            [['cp_name', 'tip'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cp_name' => 'Cp Name',
            'type' => 'Type',
            'status' => 'Status',
            'tip' => 'Tip',
        ];
    }
} 