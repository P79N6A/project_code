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
class Webunion_account extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_webunion_account';
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

    /**
     * 添加网盟用户账户
     */
    public function addAccount($condition)
    {
    	$webunion_account = new Webunion_account();
    	$webunion_account->user_id = isset($condition['user_id']) ? $condition['user_id'] : '';
    	$webunion_account->total_history_interest = isset($condition['total_history_interest']) ? $condition['total_history_interest'] : 0;
    	$webunion_account->total_history_flow = isset($condition['total_history_flow']) ? $condition['total_history_flow'] : 0;
    	$webunion_account->total_on_interest = isset($condition['total_on_interest']) ? $condition['total_on_interest'] : 0;
    	$webunion_account->total_on_flow = isset($condition['total_on_flow']) ? $condition['total_on_flow'] : 0;
    	$webunion_account->frozen_interest = isset($condition['frozen_interest']) ? $condition['frozen_interest'] : 0;
    	$webunion_account->score = isset($condition['score']) ? $condition['score'] : 0;
    	$webunion_account->create_time = date('Y-m-d H:i:s');
    	$webunion_account->last_modify_time = date('Y-m-d H:i:s');
    	$webunion_account->version = 1;
    	
    	if($webunion_account->save()){
    		$id = Yii::$app->db->getLastInsertID();
    		return $id;
    	}else{
    		return false;
    	}
    }

    /**
     * 修改网盟用户的账户
     * 
     */
    public function setAccountinfo($user_id, $condition = array()) {
        if (empty($user_id) || empty($condition)) {
            return null;
        }

        $account = Webunion_account::find()->where(['user_id' => $user_id])->one();
//        if (isset($condition['total_history_interest'])) {
//            $account->total_history_interest += $condition['total_history_interest'];
//            unset($condition['total_history_interest']);
//        }
//        if (isset($condition['total_history_flow'])) {
//            $account->total_history_flow += $condition['total_history_flow'];
//            unset($condition['total_history_flow']);
//        }
//        if (isset($condition['total_on_interest'])) {
//            $account->total_on_interest += $condition['total_on_interest'];
//            unset($condition['total_on_interest']);
//        }
//        if (isset($condition['total_on_flow'])) {
//            $account->total_on_flow += $condition['total_on_flow'];
//            unset($condition['total_on_flow']);
//        }
//        if (isset($condition['score'])) {
//            $account->score += $condition['score'];
//            unset($condition['score']);
//        }
        foreach ($condition as $key => $val) {
            if ($account->{$key} + $val <= 0) {
                $account->{$key} = 0;
            } else {
                $account->{$key} += $val;
            }
        }
        $account->last_modify_time = date('Y-m-d H:i:s');
        $account->version += 1;

        if ($account->save() > 0) {
            return true;
        } else {
            return false;
        }
    }

}
