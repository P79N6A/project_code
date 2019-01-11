<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;
use app\commonapi\Logger;

/**
 * This is the model class for table "yi_loan_rate".
 *
 * @property string $id
 * @property string $mobile
 * @property string $label
 * @property string $create_time
 */
class Loan_rate extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_loan_rate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'user_id', 'days', 'rate'], 'integer'],
            [['interest'], 'number'],
            [['create_time'], 'safe'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan Id',
            'user_id' => 'User Id',
            'days' => 'Days',
            'rate' => 'Rate',
            'interest' => 'Interest',
            'create_time' => 'Create Time',
        ];
    }

    public function addloanrate($condition){
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 获取日息利率根据loan_id
     * @param $loanId
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getRateByLoanId($loanId)
    {
        if(empty($loanId) || !is_numeric($loanId)){
            return false;
        }
        return self::find()->where(['loan_id'=>$loanId])->one();
    }
}
