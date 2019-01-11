<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "yi_loan_outmoney_record".
 *
 * @property string $id
 * @property string $user_id
 * @property string $order_id
 * @property string $loan_id
 * @property integer $old_payment_channel
 * @property integer $new_payment_channel
 * @property integer $is_black
 * @property string $error_code
 * @property string $last_modify_time
 * @property string $create_time
 */
class Loan_outmoney_record extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_loan_outmoney_record';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'order_id', 'loan_id', 'old_payment_channel', 'new_payment_channel', 'error_code', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'old_payment_channel', 'new_payment_channel', 'is_black'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['order_id'], 'string', 'max' => 32],
            [['error_code'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'order_id' => 'Order ID',
            'loan_id' => 'Loan ID',
            'old_payment_channel' => 'Old Payment Channel',
            'new_payment_channel' => 'New Payment Channel',
            'is_black' => 'Is Black',
            'error_code' => 'Error Code',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    public function addRecord($user_id, $order_id, $loan_id, $old_channel, $new_channel, $error_code, $is_black = 0) {
        if (empty($loan_id) || empty($order_id)) {
            return FALSE;
        }
        $o = $this;
        // 数据
        $create_time = date('Y-m-d H:i:s');
        $data = [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'loan_id' => $loan_id,
            'old_payment_channel' => $old_channel,
            'new_payment_channel' => $new_channel,
            'is_black' => $is_black,
            'error_code' => (string)$error_code,
            'last_modify_time' => $create_time,
            'create_time' => $create_time,
        ];
        // 保存数据
        $o->attributes = $data;
        return $o->save();
    }

}
