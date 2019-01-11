<?php

namespace app\models\dev;

use Yii;
use app\models\dev\CardLimit;

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
class User_bank extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        		[['type','bank_abbr','bank_name','province','city','sub_bank','card'], 'safe'],
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
    
    public function getBankProvince()
    {
    	return $this->hasOne(Province::className(), ['id' => 'province']);
    }
    
    public function getBankCity()
    {
    	return $this->hasOne(Province::className(), ['id' => 'city']);
    }
    
    public function getUser()
    {
    	return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }
    
    public function changeDefault(){
        if($this->default_bank==1){
            return true;
        }
        $defaultList = $this->getBankByUserId($this->user_id,0,'',array('default_bank'=>1));
        if(!empty($defaultList)){
            foreach ($defaultList as $key=>$val){
                $val->default_bank = 0;
                $val->last_modify_time = date('Y-m-d H:i:s');
                $val->save();
            }
        }
        $this->default_bank = 1;
        $this->last_modify_time = date('Y-m-d H:i:s');
        $this->save();
        return true;
    }

        /**
     * 获取用户银行卡信息
     * @param type $user_id
     * @param int $type 0、借记卡，1、信用卡，2、借记卡+信用卡
     */
    public function getBankByUserId($user_id,$type=2,$order='',$condition = array()){
        if(empty($user_id)){
            return null;
        }
        $bank = User_bank::find()->where(['user_id'=>$user_id,'status'=>1]);
        if($type!=2){
            $bank = $bank->andWhere(['type'=>$type]);
        }
        if(!empty($order)){
            $bank = $bank->orderBy($order);
        }
        if(!empty($condition)){
            $bank = $bank->andWhere($condition);
        }
        $bank = $bank->all();
        return $bank;
    }
    
    public function addUserBank($condition){
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
    
    public function updateUserBank($condition){
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

    /**
     * 银行卡限制规则排序
     * @param $userid   用户id
     * @param int $type   0:出款,1:还款，2全部
     * @param int $loan_type   0、借记卡，1、信用卡
     * @return array|bool|\yii\db\ActiveRecord[]
     */

    public function limitCardsSort($userid, $type = 2, $loan_type = 0){
        if(empty($userid)){
            return false;
        }
        
        $limit_arr   = array();
        $where = [
            'AND', 
            ['user_id' => $userid],
            ['status' => 1],
        ];
        if($type == 0){
            switch($loan_type) {
                case 0:
                    $where[] = ['type' => 0];
                    break;
                case 1:
                    $where[] = ['type' => 1];
                    break;
            }
        }
        $cards = self::find()->where($where)->orderBy('default_bank desc,last_modify_time desc')->asArray()->all();

        if($type != 2){
            $cards_limit = array();
            $cards_allow = array();
            $limit_cards = CardLimit::find()->where(['type'=>($type+1), 'status'=>1])->asArray()->all();
            if(!empty($limit_cards)){
                foreach ($limit_cards as $val) {
                    $limit_arr[] = $val['bank_name']."_".$val['card_type'];
                }
            }
            foreach ($cards as $k=>$vol) {
                $str = $vol['bank_abbr']."_".$vol['type'];
                if(in_array($str, $limit_arr)){
                    $cards_limit[$k] = $cards[$k];
                    $cards_limit[$k]['sign'] = 1;
                }else{
                    $cards_allow[$k] = $cards[$k];
                    $cards_allow[$k]['sign'] = 2;
                }
            }
            return (array_merge($cards_allow,$cards_limit));
        } else {
            foreach ($cards as $k=>$value) {
                $cards[$k]['sign'] = 2;
            }
            return $cards;
        }
    }
}
