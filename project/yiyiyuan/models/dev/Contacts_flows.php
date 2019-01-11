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
class Contacts_flows extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_contacts_flows';
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
    
    public function addContactsFlows($condition){
        if(empty($condition)){
            return false;
        }
        foreach ($condition as $key=>$val){
            $this->{$key}=$val;
        }
        $nowtime = date('Y-m-d H:i:s');
        $this->create_time = $nowtime;
        $result = $this->save();
        if($result){
            return Yii::$app->db->getLastInsertID();
        }else{
            return false;
        }
    }
    
}
