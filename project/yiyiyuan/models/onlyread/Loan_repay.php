<?php

namespace app\models\onlyread;

/**
 * This is the model class for table "yi_loan_repay".
 *
 * @property string $id
 * @property string $repay_id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $bank_id
 * @property integer $platform
 * @property integer $source
 * @property string $pic_repay1
 * @property string $pic_repay2
 * @property string $pic_repay3
 * @property integer $status
 * @property string $money
 * @property string $actual_money
 * @property string $pay_key
 * @property string $code
 * @property string $paybill
 * @property string $last_modify_time
 * @property string $createtime
 * @property string $repay_time
 * @property string $repay_mark
 */
class Loan_repay extends ReadBaseModel {

    public $huankuan_amount;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_loan_repay';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'loan_id', 'last_modify_time', 'createtime'], 'required'],
            [['user_id', 'loan_id', 'bank_id', 'platform', 'source', 'status', 'version'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['last_modify_time', 'createtime'], 'safe'],
            [['repay_id', 'pay_key', 'repay_time'], 'string', 'max' => 32],
            [['paybill'], 'string', 'max' => 64],
            [['pic_repay1', 'pic_repay2', 'pic_repay3', 'repay_mark'], 'string', 'max' => 128],
            [['code'], 'string', 'max' => 6]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'repay_id' => 'Repay ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'bank_id' => 'Bank ID',
            'platform' => 'Platform',
            'source' => 'Source',
            'pic_repay1' => 'Pic Repay1',
            'pic_repay2' => 'Pic Repay2',
            'pic_repay3' => 'Pic Repay3',
            'status' => 'Status',
            'money' => 'Money',
            'actual_money' => 'Actual Money',
            'pay_key' => 'Pay Key',
            'code' => 'Code',
            'paybill' => 'Paybill',
            'last_modify_time' => 'Last Modify Time',
            'createtime' => 'Createtime',
            'repay_time' => 'Repay Time',
            'repay_mark' => 'Repay Mark',
        ];
    }

}
