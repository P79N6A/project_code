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
class Guarantee_card_order extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_guarantee_card_order';
    }

//     /**
//      * @inheritdoc
//      */
//     public function rules()
//     {
//         return [
//         ];
//     }
//     /**
//      * @inheritdoc
//      */
//     public function attributeLabels()
//     {
//         return [
//             'id' => 'ID',
//         ];
//     }
    public function getGuarantee() {
        return $this->hasOne(Guarantee_card::className(), ['id' => 'card_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['id' => 'bank_id']);
    }
    
    public function updateGuaCardOrder($condition){
        foreach ($condition as $key=>$val){
            $this->{$key}=$val;
        }
        $result = $this->save();
        return $result;
    }

    public function getCardList($where, $order = '', $limit = 0, $select = '') {
        if(empty($where)){
            return false;
        }
        $cardList = Guarantee_card_order::find();
        if (!empty($where)) {
            $cardList = $cardList->where($where);
        }
        if (!empty($order)) {
            $cardList = $cardList->orderBy($order);
        }
        if($limit!=0){
            $cardList = $cardList->limit($limit);
        }
        $result = $cardList->all();
        return $result;
    }
    
    /**
     * 修改担保卡购买表的信息
     */
    public function setGuaranteeCardOrderInfo($type, $id, $allAmount=''){
    	if($type == 1){
    		$cardsql = "update ".Guarantee_card_order::tableName()." set remain_amount=remain_amount+$allAmount where id=".$id;
    	}else{
    		$cardsql = "update ".Guarantee_card_order::tableName()." set remain_amount=total_amount where id=".$id;
    	}
    	$cardret = Yii::$app->db->createCommand($cardsql)->execute();
    	
    	return $cardret;
    }
    
    /**
     * 
     * **/
    public function optimisticLock()
    {
    	return "version";
    }
    
}
