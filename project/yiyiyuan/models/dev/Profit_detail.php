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
class Profit_detail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_profit_detail';
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
     * 添加一条收益明细记录
     */
    public function addProfitDetail($standard_id, $user_id, $total_onInvested_share, $profit, $yield, $cycle, $now_time){
    	$profit_detail = new Profit_detail();
    	$profit_detail->version = 1;
    	$profit_detail->standard_id = $standard_id;
    	$profit_detail->user_id = $user_id;
    	$profit_detail->profit_type = 'STANDARD';
    	$profit_detail->buy_amount = $total_onInvested_share;
    	$profit_detail->buy_share = $total_onInvested_share;
    	$profit_detail->total_profit = $profit;
    	$profit_detail->yield = $yield;
    	$profit_detail->on_period = $cycle;
    	$profit_detail->last_modify_time = $now_time;
    	$profit_detail->create_time = $now_time;
    	
    	if($profit_detail->save()){
    		return true;
    	}else{
    		return false;
    	}
    	
    }
}
