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
class Webunion_profit_detail_test extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_webunion_profit_detail_test';
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
    
    /**
     * 添加一条收益明细
     */
    public function addProfit($condition){
    	
    	$profit = new Webunion_profit_detail_test();
    	$profit->user_id = isset($condition['user_id']) ? $condition['user_id'] : '';
    	$profit->type = isset($condition['type']) ? $condition['type'] : '';
    	$profit->profit_id = isset($condition['profit_id']) ? $condition['profit_id'] : '';
    	$profit->profit_amount = isset($condition['profit_amount']) ? $condition['profit_amount'] : '';
    	$profit->profit_type = isset($condition['profit_type']) ? $condition['profit_type'] : '';
    	$profit->create_time = date('Y-m-d H:i:s');
    	$profit->last_modify_time = date('Y-m-d H:i:s');
    	$profit->version = 1;
    	
    	if($profit->save()){
    		$id = Yii::$app->db->getLastInsertID();
    		return $id;
    	}else{
    		return false;
    	}
    }

	 
     public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'profit_id']);
    }

     public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'profit_id']);
    }

	public function getInvest() {
        return $this->hasOne(User_invest::className(), ['invest_id' => 'profit_id']);
    }
    
}