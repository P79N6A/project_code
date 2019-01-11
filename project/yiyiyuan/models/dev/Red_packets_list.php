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
class Red_packets_list extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_red_packets_list';
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
     *添加一条红包发送记录
     */
    public function addRedPacketsList($condition){
    	$now_time = date('Y-m-d H:i:s');
    	$red_packets_list = new Red_packets_list();
    	$red_packets_list->grant_id = $condition['grant_id'];
    	$red_packets_list->amount = $condition['amount'];
    	$red_packets_list->create_time = $now_time;
    	$red_packets_list->last_modify_time = $now_time;
    	$red_packets_list->version = 1;
    	
    	if($red_packets_list->save()){
    		$id = Yii::$app->db->getLastInsertID();
            return $id;
    	}else{
    		return false;
    	}
    }
}
