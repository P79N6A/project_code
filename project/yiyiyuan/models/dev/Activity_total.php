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
class Activity_total extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_activity_total';
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
    
    public function addTotal($condition){
        if(empty($condition)||!isset($condition['user_id'])){
            return false;
        }
        $totalModel = new Activity_total();
        foreach ($condition as $key=>$val){
            $totalModel->{$key}=$val;
        }
        $totalModel->last_mofify_time = date('Y-m-d H:i:s');
        $totalModel->create_time = date('Y-m-d H:i:s');
        $result = $totalModel->save();
        return $result;
    }
    
    public function updateTotal($condition){
        foreach ($condition as $key=>$val){
            $this->{$key}=$val;
        }
        $this->last_mofify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        if($result){
            return true;
        }else{
            return false;
        }
    }

}
