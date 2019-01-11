<?php

namespace app\models\dev;

//use app\models\news\User_remit_list;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class User_loan_extend extends ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_loan_extend';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
        ];
    }

    public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }
    public function getLoanlist(){
        return $this->hasOne(User_remit_list::className(),[['loan_id' => 'loan_id']]);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getLoanflow() {
        return $this->hasOne(User_loan_flows::className(), ['loan_id' => 'loan_id'])->where([User_loan_flows::tableName() . '.loan_status' => 6]);
    }

    public function addList($condition) {
        if (!empty($condition['loan_id'])) {
            $loansubsidiary = (new User_loan_extend())->getUserLoanSubsidiaryByLoanId($condition['loan_id']);
            if (!empty($loansubsidiary)) {
                $result = $loansubsidiary->updateUserLoanSubsidiary($condition);
                return $loansubsidiary->id;
            }
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $time = date('Y-m-d H:i:s');
        $this->last_modify_time = $time;
        $this->create_time = $time;
        $this->version = 0;
        $result = $this->save();
        if ($result) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }

    public function updateUserLoanSubsidiary($condition) {
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->version ++;
        $time = date('Y-m-d H:i:s');
        $this->last_modify_time = $time;
        $result = $this->save();
        return $result;
    }

    public function getUserLoanSubsidiaryByLoanId($loan_id) {
        if (empty($loan_id)) {
            return false;
        }
        $result = User_loan_extend::find()->where(['loan_id' => $loan_id])->one();
        return $result;
    }

}
