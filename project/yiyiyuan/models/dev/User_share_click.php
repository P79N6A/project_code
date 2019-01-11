<?php

namespace app\models\dev;

use Yii;
use app\commonapi\Common;

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

class User_share_click extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_share_click';
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
    
    public function createClick($array_click){
        $share = new User_share_click();
        $ip = Common::get_client_ip();
        $share->user_id = $array_click['user_id'];
        if (isset($array_click['loan_id']) && $array_click['loan_id'] != 0) {
            $share->loan_id = $array_click['loan_id'];
        }
        $share->remoteip = $ip;
        $share->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $share->click_id = empty($array_click['click_id'])? 0 :$array_click['click_id'];
        $share->type = $array_click['type'];
        $share->create_time = date('Y-m-d H:i:s');
        $share->save();
    }

        public function getUser()
    {
    	return $this->hasOne(User::className(), ['user_id'=>'invest_user_id']);
    }
}
