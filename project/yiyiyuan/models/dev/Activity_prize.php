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
class Activity_prize extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_activity_prize';
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
    
    public function addPrize($condition){
        if(empty($condition)||!isset($condition['user_id'])){
            return false;
        }
        $prizeModel = new Activity_prize();
        foreach ($condition as $key=>$val){
            $prizeModel->{$key}=$val;
        }
        $prizeModel->create_time = date('Y-m-d H:i:s');
        $ret = $prizeModel->save();
        if($ret){
            return $id = Yii::$app->db->getLastInsertID();
        }else{
            return false;
        }
    }

}
