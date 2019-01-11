<?php

namespace app\models\news; 

use app\models\BaseModel;

/** 
 * This is the model class for table "address_list". 
 * 
 * @property string $id
 * @property integer $aid
 * @property string $user_id
 * @property string $user_phone
 * @property string $phone
 * @property string $name
 * @property string $modify_time
 * @property string $create_time
 */ 
class Address_list_new extends BaseModel{
    /** 
     * @inheritdoc 
     */ 
    public static function tableName() 
    { 
        return 'address_list'; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['aid', 'modify_time', 'create_time'], 'required'],
            [['aid', 'user_id'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['user_phone', 'phone'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 32]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'aid' => 'Aid',
            'user_id' => 'User ID',
            'user_phone' => 'User Phone',
            'phone' => 'Phone',
            'name' => 'Name',
            'modify_time' => 'Modify Time',
            'create_time' => 'Create Time',
        ]; 
    } 
} 