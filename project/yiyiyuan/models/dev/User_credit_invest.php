<?php

namespace app\models\dev;

use Yii;


class User_credit_invest extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_credit_invest';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            
        ];
    }
    
    public function addCreditInvest($user_id,$condition){
        $m_user_credit_invest = new User_credit_invest();
        $m_user_credit_invest->user_id = $user_id;
        $m_user_credit_invest->amount = isset($condition['amount'])?$condition['amount']:0;
        $m_user_credit_invest->create_time = date("Y-m-d H:i:s");
        $status_invest = $m_user_credit_invest->save(); //投资记录
        if($status_invest){
            return Yii::$app->db->getLastInsertID();
        }else{
            return false;
        }
    }
    
}
