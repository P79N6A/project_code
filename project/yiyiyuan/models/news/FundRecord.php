<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_fund_record".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $fund
 * @property string $fund_status
 * @property string $agreement_status
 * @property string $create_time
 * @property string $last_time
 */
class FundRecord extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_fund_record';


    }


    public function getloan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getRemit() {
        return $this->hasOne(User_remit_list::className(), ['loan_id' => 'loan_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getExtend() {
        return $this->hasOne(User_extend::className(), ['user_id' => 'user_id']);
    }

    
    public function getRemitlist() {
        return $this->hasOne(User_remit_list::className(), ['loan_id' => 'loan_id']);
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_id', 'fund'], 'integer'],
            [['loan_id', 'fund', 'create_time'], 'required'],
            [['create_time', 'last_time'], 'safe'],
            [['fund_status', 'agreement_status'], 'string', 'max' => 16]
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
            'loan_id' => 'Loan ID',
            'fund' => 'Fund',
            'fund_status' => 'Fund Status',
            'agreement_status' => 'Agreement Status',
            'create_time' => 'Create Time',
            'last_time' => 'Last Time',
        ];
    }

    public function changeStatus($status)
    {
        $this->agreement_status = $status;
        $this->last_time = date("Y-m-d H:i:s", time());
        if (!$this->save()) {
            return false;
        }
    }
}
