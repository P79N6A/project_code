<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "access_token".
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
class Newuser_red_packets_receive extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_newuser_red_packets_receive';
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
     * 领取红包
     */
    public function addRedPacket($condition){
    	$now_time = date('Y-m-d H:i:s');
    	$red_packets_receive = new Newuser_red_packets_receive();
    	$red_packets_receive->wx_id = $condition['wx_id'];
    	$red_packets_receive->grant_id = $condition['grant_id'];
    	$red_packets_receive->auth_user_id = $condition['auth_user_id'];
    	$red_packets_receive->amount = $condition['amount'];
    	$red_packets_receive->current_amount = $condition['current_amount'];
    	$red_packets_receive->status = 'NORMAL';
    	$red_packets_receive->create_time = $now_time;
    	$red_packets_receive->invalid_time = date('Y-m-d H:i:s', time()+3600);
    	$red_packets_receive->last_modify_time = $now_time;
    	$red_packets_receive->version = 1;
    	 
    	if($red_packets_receive->save()){
    		$id = Yii::$app->db->getLastInsertID();
    		return $id;
    	}else{
    		return false;
    	}
    }
}
