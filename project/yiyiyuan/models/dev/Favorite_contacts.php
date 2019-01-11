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
class Favorite_contacts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_favorite_contacts';
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
    
    public function getFavorite()
    {
    	return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }
    
    public function getFavoriteByUserId($user_id){
        return Favorite_contacts::find()->where(['user_id'=>$user_id])->one();
    }

        public function addFavoriteContacts($condition){
        if(empty($condition)){
            return false;
        }
        foreach ($condition as $key=>$val){
            $this->{$key}=$val;
        }
        $nowtime = date('Y-m-d H:i:s');
        $this->last_modify_time = $nowtime;
        $this->create_time = $nowtime;
        $result = $this->save();
        if($result){
            return Yii::$app->db->getLastInsertID();
        }else{
            return false;
        }
    }
    
    public function updateFavoriteContacts($condition){
        if(empty($condition)){
            return false;
        }
        foreach ($condition as $key=>$val){
            $this->{$key} = $val;
        }
        $this->last_modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }
    
}
