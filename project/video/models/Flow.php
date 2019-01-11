<?php

namespace app\models;

use Yii;
class Flow extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%flow}}';
    }
	/**
     * 添加一条新的短信信息
     */
    public function addFlow($mobile,$amount,$status,$flow_id,$type)
    {
    	$flow = new Flow();
    	$flow->mobile = $mobile;
    	$flow->flow_amount = $amount;
    	$flow->status = $status;
    	$flow->flow_id = $flow_id;
    	$flow->type = $type;
    	$flow->create_time = date('Y-m-d H:i:s');
    	$flow->last_modify_time = date('Y-m-d H:i:s');
    	
    	if($flow->save()){
    		return true;
    	}else{
    		return false;
    	}
    }
}
