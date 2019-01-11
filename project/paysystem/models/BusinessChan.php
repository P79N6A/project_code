<?php

namespace app\models; 

use Yii; 

/** 
 * This is the model class for table "{{%business_chan}}". 
 * 
 * @property integer $id
 * @property integer $aid
 * @property integer $business_id
 * @property integer $channel_id
 * @property integer $status
 * @property integer $sort_num
 * @property string $create_time
 */ 
class BusinessChan extends BaseModel
{ 
    /** 
     * @inheritdoc 
     */ 
    public static function tableName() 
    { 
        return '{{%business_chan}}'; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['aid', 'business_id', 'channel_id', 'status', 'sort_num'], 'integer'],
            [['business_id', 'channel_id', 'create_time'], 'required'],
            [['create_time'], 'safe']
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'aid' => '应用id',
            'business_id' => '业务id',
            'channel_id' => '通道id',
            'status' => '0:未开通; 1:已开通; 2:临时关闭;',
            'sort_num' => '排序',
            'create_time' => '创建时间',
        ]; 
    } 


    
    public static function getStatus(){
        return [
            0 => '未开通',
            1 => '已开通',
            2 => '临时关闭',
        ];
    }
    
    public function getApp(){
        return $this->hasOne(App::className(),['id' => 'aid']);
    }
    
    public function getChannel(){
        return $this->hasOne(Channel::className(),['id' => 'channel_id']);
    }
    public function getBusiness(){
        return $this->hasOne(Business::className(),['id' => 'business_id']);
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
    
    public function getBusinessChanByConditions($conditions , $one= false){
        $where = [];
        if(!empty($conditions)){
            $where[] = 'AND';
            foreach ($conditions as $k => $v){
                $where[] = [$k => $v];
            }
        }
        if($one){
            return $data = self::find()->where($where)->one();
        }else{
            return $data = self::find()->where($where)->all();
        }
    }


    public function getByBusiness($business_code){
        if (empty($business_code)) {
            return null;
        }
        $where = [
            Business::tableName().'.status' => 1,
            BusinessChan::tableName().'.status' => 1,
            'business_code' => $business_code
        ];
        $businessChans = static::find()->joinWith('business',true,'LEFT JOIN')
                        ->where($where)
                        ->orderBy('sort_num asc')
                        ->all();
        if (empty($businessChans)) {
            return null;
        }
        return \yii\helpers\ArrayHelper::getColumn($businessChans, 'id');
    }



}
