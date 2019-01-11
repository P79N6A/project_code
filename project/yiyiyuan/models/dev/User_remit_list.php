<?php

namespace app\models\dev;

use Yii;

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
class User_remit_list extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_remit_list';
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

    public function getLoanextend() {
        return $this->hasOne(User_loan_extend::className(), ['loan_id' => 'loan_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getLoan() {
        return $this->hasOne(User_loan::className(), ['user_id' => 'user_id']);
    }

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['id' => 'bank_id']);
    }

    public function getManager() {
        return Manager_logs::find()->where(['log_id' => $this->id])->asArray()->one();
    }

    public function updateRemit($condition) {
        if (empty($condition)) {
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->last_modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        if ($result) {
            return $this;
        } else {
            return false;
        }
    }

    /**
     * 担保借款财务管理
     * @param  array $remit_status
     * @return array|bool
     */
    public function SecuredLoan(array $remit_status)
    {
        if (empty($remit_status)) return false;
        $user_loan = User_loan::tableName();
        $user_remit_list = self::tableName(); //出款记录表
        //INIT：初始；PROCEING：出款中；SUCCESS：出款成功；FAIL：出款失败；ABNORMAL : 出款异常
        $data = static::find()->innerJoin($user_loan, "{$user_loan}.loan_id = {$user_remit_list}.loan_id")
                ->where([$user_loan.'.business_type'=>4, $user_remit_list.'.remit_status'=>$remit_status]);
        if (!empty($data)){
            return $data;
        }
        return false;
    }

    /**
     * @param array $remit_status
     * @return bool|static
     */
    public function financeLoan(array $remit_status)
    {
        if (empty($remit_status)) return false;
        $user_loan = User_loan::tableName();
        $user_remit_list = self::tableName(); //出款记录表
        //INIT：初始；PROCEING：出款中；SUCCESS：出款成功；FAIL：出款失败；ABNORMAL : 出款异常
        $data = static::find()->innerJoin($user_loan, "{$user_loan}.loan_id = {$user_remit_list}.loan_id")
            ->where([
                'and',
                ['!=', $user_loan.'.business_type', 4],
                [$user_remit_list.'.remit_status'=>$remit_status]
            ]);
        if (!empty($data)){
            return $data;
        }
        return false;
    }

    /**
     *  添加出款记录
     */
    public function addRecord($condition) {
        if (empty($condition)) {
            return false;
        }
        $o = new self();
        foreach ($condition as $key => $val) {
            $o->{$key} = $val;
        }
        $o->create_time = date('Y-m-d H:i:s');
        $o->last_modify_time = date('Y-m-d H:i:s');
        $result = $o->save();
        return $result;
    }

}
