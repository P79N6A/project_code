<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pay_white_ip".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $ip
 * @property integer $status
 * @property string $create_time
 */
class WhiteIp extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%white_ip}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'status'], 'integer'],
            [['ip', 'create_time'], 'required'],
            [['create_time'], 'safe'],
            [['ip'], 'string', 'max' => 50]
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
            'ip' => 'Ip',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }
    
    public static function getStatus(){
        return [
            0 => '禁用',
            1 => '启用',
        ];
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
    
    public function findOneByIp($ip){
        if(empty($ip)){
            return null;
        }
        $info = WhiteIp::find()->where(['ip' => $ip])->one();
        return $info;
    }


    /**
     * 验证ip是否正确
     */
    public function validIp($aid, $ip){
        $ips = $this->getValidIps($aid);
        if( empty($ips) ){
            return false;
        }
        
        return in_array($ip,$ips);
    }
    /**
     * 获取某商户的可用ip列表
     */
    public function getValidIps($aid){
        $data = self::find()->where(["aid"=>$aid])->all();
        if(empty($data)){
            return null;
        }
        foreach($data as $o){
            if( $o -> status == 1){
                $ips[] = $o->ip;
            }
        }
        return $ips;
    }
}
