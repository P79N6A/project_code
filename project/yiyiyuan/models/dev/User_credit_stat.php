<?php

namespace app\models\dev;

use Yii;

class User_credit_stat extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_credit_stat';
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

    public function getXhByUserId($user_id,$select='') {
        if (empty($user_id)) {
            return FALSE;
        }
        $stat_info = User_credit_stat::find()->where(['user_id' => $user_id]);
        if(!empty($select)){
            $stat_info = $stat_info->select($select);
        }
        $result = $stat_info->one();
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    public function addUserCreditStat($user_id,$amount) {
        $m_user_credit_stat = new User_credit_stat();
        $m_user_credit_stat->user_id = $user_id;
        $m_user_credit_stat->total_amount = $amount;
        $m_user_credit_stat->version = 1;
        $status_stat = $m_user_credit_stat->save();
        if($status_stat){
            return Yii::$app->db->getLastInsertID();
        }else{
            return false;
        }
    }
    
    public function updateUserCreditStat($amount) {
        $this->total_amount += $amount;
        $this->version += 1;
        $status_stat = $this->save();
        if($status_stat){
            return true;
        }else{
            return false;
        }
    }

}
