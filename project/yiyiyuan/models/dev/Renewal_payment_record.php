<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "yi_renewal_payment_record".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $order_id
 * @property string $parent_loan_id
 * @property string $user_id
 * @property string $bank_id
 * @property integer $platform
 * @property integer $source
 * @property string $money
 * @property string $actual_money
 * @property string $paybill
 * @property integer $status
 * @property string $last_modify_time
 * @property string $create_time
 */
class Renewal_payment_record extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_renewal_payment_record';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['loan_id', 'order_id', 'parent_loan_id', 'user_id', 'platform', 'source', 'last_modify_time', 'create_time'], 'required'],
            [['loan_id', 'parent_loan_id', 'user_id', 'bank_id', 'platform', 'source', 'status'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['order_id'], 'string', 'max' => 32],
            [['paybill'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'order_id' => 'Order ID',
            'parent_loan_id' => 'Parent Loan ID',
            'user_id' => 'User ID',
            'bank_id' => 'Bank ID',
            'platform' => 'Platform',
            'source' => 'Source',
            'money' => 'Money',
            'actual_money' => 'Actual Money',
            'paybill' => 'Paybill',
            'status' => 'Status',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 添加一条纪录（如果存在记录则更新记录）
     */
    public static function addBatch($loan, $order_id, $bank_id, $money, $platform, $source) {
        if (empty($loan)) {
            return FALSE;
        }
        // 数据
        $create_time = date('Y-m-d H:i:s');
        $o = new self;
        $data = [
            'loan_id' => $loan->loan_id,
            'order_id' => $order_id,
            'parent_loan_id' => $loan->parent_loan_id ? $loan->parent_loan_id : 0,
            'user_id' => $loan->user_id,
            'bank_id' => $bank_id,
            'platform' => $platform,
            'source' => $source,
            'money' => $money,
//                'actual_money' => 'Actual Money',
//                'paybill' => 'Paybill',
            'status' => 0,
            'last_modify_time' => $create_time,
            'create_time' => $create_time,
        ];
        // 保存数据
        $o->attributes = $data;
        $result = $o->save();
        return $result;
    }

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['id' => 'bank_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }
    
}
