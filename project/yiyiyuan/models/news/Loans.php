<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_loans".
 *
 * @property string $loan_id
 */
class Loans extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_loans';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id'], 'required'],
            [['loan_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'loan_id' => 'Loan ID',
        ];
    }

    public function getUserloan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getCmloans() {
        return $this->hasOne(Cm_loans::className(), ['loan_id' => 'loan_id']);
    }
}
