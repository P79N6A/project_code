<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_loan_goods_pay".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_goods_id
 * @property string $loan_goods_no
 * @property string $paybill
 * @property string $loan_goods_amount
 * @property string $actual_money
 * @property integer $buy_status
 * @property string $buy_time
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Loan_goods_pay extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_loan_goods_pay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_goods_id', 'loan_goods_no', 'loan_goods_amount', 'actual_money', 'buy_time', 'create_time', 'last_modify_time'], 'required'],
            [['user_id', 'loan_goods_id', 'buy_status', 'version'], 'integer'],
            [['loan_goods_amount', 'actual_money'], 'number'],
            [['buy_time', 'create_time', 'last_modify_time'], 'safe'],
            [['loan_goods_no', 'paybill'], 'string', 'max' => 32]
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
            'loan_goods_id' => 'Loan Goods ID',
            'loan_goods_no' => 'Loan Goods No',
            'paybill' => 'Paybill',
            'loan_goods_amount' => 'Loan Goods Amount',
            'actual_money' => 'Actual Money',
            'buy_status' => 'Buy Status',
            'buy_time' => 'Buy Time',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }
}
