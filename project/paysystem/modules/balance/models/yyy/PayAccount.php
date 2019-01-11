<?php

namespace app\modules\balance\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_pay_account".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property integer $step
 * @property integer $activate_result
 * @property string $activate_time
 * @property string $create_time
 * @property string $accountId
 * @property string $card
 * @property string $orderId
 * @property integer $isopen
 * @property integer $sign
 */
class PayAccount extends YyyBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_pay_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'activate_time', 'create_time'], 'required'],
            [['user_id', 'type', 'step', 'activate_result', 'isopen', 'sign'], 'integer'],
            [['activate_time', 'create_time'], 'safe'],
            [['accountId'], 'string', 'max' => 25],
            [['card', 'orderId'], 'string', 'max' => 64]
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
            'step' => 'Step',
            'activate_result' => 'Activate Result',
            'activate_time' => 'Activate Time',
            'create_time' => 'Create Time',
            'accountId' => 'Account ID',
            'card' => 'Card',
            'orderId' => 'Order ID',
            'isopen' => 'Isopen',
            'sign' => 'Sign',
        ];
    }
}