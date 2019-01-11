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
class Webunion_user_list extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_webunion_user_list';
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
     * 添加网盟用户
     */
    public function addUser($condition)
    {
    	$webunion_user = new Webunion_user_list();
    	$webunion_user->user_id = isset($condition['user_id']) ? $condition['user_id'] : '';
    	$webunion_user->parent_user_id = isset($condition['parent_user_id']) ? $condition['parent_user_id'] : '';
    	$webunion_user->top_user_id = isset($condition['top_user_id']) ? $condition['top_user_id'] : '';
    	$webunion_user->type = isset($condition['type']) ? $condition['type'] : '';
    	$webunion_user->create_time = date('Y-m-d H:i:s');
    	$webunion_user->last_modify_time = date('Y-m-d H:i:s');
    	$webunion_user->version = 1;
    	
    	if($webunion_user->save()){
    		$id = Yii::$app->db->getLastInsertID();
    		return $id;
    	}else{
    		return false;
    	}
    }

	 public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    } 
    
}
