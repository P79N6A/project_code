<?php

namespace app\models; 

use Yii; 

/** 
 * This is the model class for table "{{%black_ip}}". 
 * 
 * @property integer $id
 * @property string $ip
 * @property string $create_time
 */ 
class BlackIp extends \app\models\BaseModel
{ 
    /** 
     * @inheritdoc 
     */ 
    public static function tableName() 
    { 
        return '{{%black_ip}}'; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['ip', 'create_time'], 'required'],
            [['create_time'], 'safe'],
            [['ip'], 'string', 'max' => 30]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'ip' => 'IP地址',
            'create_time' => '创建时间',
        ]; 
    } 


    public function getId() {
        return $this->id;
    }


    public function getIp() {
        return $this->ip;
    }

    /**
     * 判断ip是否为黑名单
     * @param  [type]  $ip [description]
     * @return boolean     [description]
     */
    public function isBlackIp($ip){

        if (empty($ip)) {
            return true;
        }

        $ret = static::find()->where(['ip' => $ip])->one();
        if (!empty($ret)) {
            return true;
        }
        return false;
    }
    
    public function getBlackIpById($id){
        if (empty($ip)) {
            return null;
        }
        return $ret = self::findOne($id);
    }
    
    public function createData($data){
        $data['create_time'] = date("Y-m-d H:i:s", time());
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        //3 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        }else{
            return $result;
        }
    }
    
    public function updateData($data){
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        //3 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        }else{
            return $result;
        }
    }
    
    
} 