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
class Loan_collection extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_loan_collection';
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
    /* public function getUser()
      {
      return $this->hasOne(User::className(), ['openid' => 'openid']);
      } */

    public function addLoancollection($condition) {
        if(empty($condition)){
            return false;
        }
        $create_time = date('Y-m-d H:i:s');
        foreach ($condition as $key=>$val){
            $this->{$key}=$val;
        }
        $this->create_time = $create_time;
        $result = $this->save();
        if($result){
            return Yii::$app->db->getLastInsertID();
        }else{
            return false;
        }
    }

}
