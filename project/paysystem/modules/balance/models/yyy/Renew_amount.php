<?php

namespace app\modules\balance\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yi_renew_amount".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $renew_fee
 * @property string $chase_fee
 * @property string $create_time
 * @property string $start_time
 * @property string $end_time
 * @property integer $type
 * @property string $user_id
 * @property string $parent_loan_id
 * @property integer $mark
 * @property string $renew
 */
class Renew_amount extends YyyBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_renew_amount';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'type', 'user_id', 'parent_loan_id', 'mark'], 'integer'],
            [['renew_fee', 'chase_fee', 'renew'], 'number'],
            [['create_time', 'start_time', 'end_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'renew_fee' => 'Renew Fee',
            'chase_fee' => 'Chase Fee',
            'create_time' => 'Create Time',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'type' => 'Type',
            'user_id' => 'User ID',
            'parent_loan_id' => 'Parent Loan ID',
            'mark' => 'Mark',
            'renew' => 'Renew',
        ];
    }

    /**
     *通过loan获取展期数据
     * @param $loan_id
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getDataByLoanid($loan_id)
    {
        if (empty($loan_id)){
            return false;
        }
        return self::find()->where(['loan_id'=>$loan_id])->orderBy("create_time desc")->all();
    }
}