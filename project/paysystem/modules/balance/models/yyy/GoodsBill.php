<?php

namespace app\modules\balance\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_goods_bill".
 *
 * @property string $id
 * @property string $bill_id
 * @property string $order_id
 * @property string $goods_id
 * @property string $loan_id
 * @property string $user_id
 * @property integer $phase
 * @property integer $fee
 * @property integer $number
 * @property string $goods_amount
 * @property string $current_amount
 * @property string $actual_amount
 * @property string $repay_amount
 * @property string $principal
 * @property string $over_principal
 * @property string $interest
 * @property string $over_interest
 * @property string $over_late_fee
 * @property string $start_time
 * @property string $end_time
 * @property integer $days
 * @property integer $bill_status
 * @property string $remit_status
 * @property string $repay_time
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class GoodsBill extends YyyBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods_bill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bill_id', 'order_id', 'goods_id', 'loan_id', 'user_id', 'phase', 'fee', 'number', 'goods_amount', 'current_amount', 'actual_amount', 'repay_amount', 'principal', 'interest', 'start_time', 'end_time', 'days', 'bill_status', 'remit_status', 'repay_time', 'create_time', 'last_modify_time'], 'required'],
            [['goods_id', 'loan_id', 'user_id', 'phase', 'fee', 'number', 'days', 'bill_status', 'version'], 'integer'],
            [['goods_amount', 'current_amount', 'actual_amount', 'repay_amount', 'principal', 'over_principal', 'interest', 'over_interest', 'over_late_fee'], 'number'],
            [['start_time', 'end_time', 'repay_time', 'create_time', 'last_modify_time'], 'safe'],
            [['bill_id', 'order_id'], 'string', 'max' => 64],
            [['remit_status'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bill_id' => 'Bill ID',
            'order_id' => 'Order ID',
            'goods_id' => 'Goods ID',
            'loan_id' => 'Loan ID',
            'user_id' => 'User ID',
            'phase' => 'Phase',
            'fee' => 'Fee',
            'number' => 'Number',
            'goods_amount' => 'Goods Amount',
            'current_amount' => 'Current Amount',
            'actual_amount' => 'Actual Amount',
            'repay_amount' => 'Repay Amount',
            'principal' => 'Principal',
            'over_principal' => 'Over Principal',
            'interest' => 'Interest',
            'over_interest' => 'Over Interest',
            'over_late_fee' => 'Over Late Fee',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'days' => 'Days',
            'bill_status' => 'Bill Status',
            'remit_status' => 'Remit Status',
            'repay_time' => 'Repay Time',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function getOverdueloan() {
        return $this->hasOne(OverdueLoan::className(), ['bill_id' => 'bill_id']);
    }
}