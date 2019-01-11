<?php

namespace app\models\dev;

use app\commonapi\Http;
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
class Loan_record extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_loan_record';
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

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'invest_user_id']);
    }

    /**
     * 添加一条投资明细
     */
    public function addInvestInformation($loan_id, $user_id, $amount, $type, $create_time) {
        $sql_invest_detail = "insert into " . Loan_record::tableName() . " (loan_id,invest_user_id,amount,type,create_time) value('$loan_id', '$user_id', '$amount', '$type', '$create_time')";

        $ret_invest_detail = Yii::$app->db->createCommand($sql_invest_detail)->execute();
        if ($ret_invest_detail) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 借款点赞投资
     * @param type $user 投资用户
     * @param type $loaninfo 借款信息
     * @return float 返还用户点赞金额
     */
    public function investByxianhua($user, $loaninfo) {
        //获取一亿元送金额次数
        $clickCount = Loan_record::find()->where(['loan_id' => $loaninfo->loan_id, 'type' => 1])->count();
        $amt = Http::clickLike($clickCount);
        $remainAmt = ( $loaninfo['amount'] - $loaninfo['current_amount'] );
        if ($remainAmt <= 0) {
            return 0;
        }
        if ($amt > $remainAmt) {
            $isSucc = 1;
            $amt = $remainAmt;
        } else {
            $isSucc = 0;
        }
        if($remainAmt-$amt<1){
            $amt=0;
        }
        $time = date('Y-m-d H:i:s');
        $ret = 0;
        if($amt>0){        
            $loanrecordModel = new Loan_record();
            $ret = $loanrecordModel->addInvestInformation($loaninfo->loan_id, $user->userwx->id, $amt, 1, $time);        
        }
        //更新借款已借金额、借款投资记录
        if ($ret) {
            $condition = array(
                'current_amount' => $loaninfo['current_amount'] + $amt,
            );
            if ($isSucc) {
                $condition['withdraw_time'] = $time;
                $condition['status'] = 5;
            }
            $loaninfo = $loaninfo->updateUserLoan($condition);
            return $loaninfo ? $amt : 0;
        } else {
            return 0;
        }
    }

}
