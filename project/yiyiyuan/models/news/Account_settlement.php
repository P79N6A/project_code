<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_account_settlement".
 *
 * @property string $id
 * @property string $user_id
 * @property string $amount
 * @property string $settlement_id
 * @property string $bank_id
 * @property integer $type
 * @property string $status
 * @property integer $admin_id
 * @property string $create_time
 * @property string $fee
 * @property string $version
 * @property integer $source
 */
class Account_settlement extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_account_settlement';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'amount', 'create_time'], 'required'],
            [['user_id', 'bank_id', 'type', 'admin_id', 'version', 'source'], 'integer'],
            [['amount', 'fee'], 'number'],
            [['create_time'], 'safe'],
            [['settlement_id'], 'string', 'max' => 32],
            [['status'], 'string', 'max' => 16]
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
            'amount' => 'Amount',
            'settlement_id' => 'Settlement ID',
            'bank_id' => 'Bank ID',
            'type' => 'Type',
            'status' => 'Status',
            'admin_id' => 'Admin ID',
            'create_time' => 'Create Time',
            'fee' => 'Fee',
            'version' => 'Version',
            'source' => 'Source',
        ];
    }
}
