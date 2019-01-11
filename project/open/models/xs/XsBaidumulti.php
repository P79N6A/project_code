<?php

namespace app\models\xs;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "dc_baidumulti".
 *
 * @property string $id
 * @property string $basic_id
 * @property string $realname
 * @property string $phone
 * @property string $idcard
 * @property string $reqid
 * @property integer $retCode
 * @property string $retMsg
 * @property integer $score
 * @property string $models
 * @property string $modify_time
 * @property string $create_time
 */
class XsBaidumulti extends \app\models\repo\CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_baidumulti';
    }

    /**
     * @inheritdoc
     */
    public function rules() 
    { 
        return [
            [['basic_id', 'retCode', 'name_score', 'id_score', 'ph_score'], 'integer'],
            [['realname', 'phone', 'idcard', 'modify_time', 'create_time'], 'required'],
            [['modify_time', 'create_time'], 'safe'],
            [['realname', 'phone', 'idcard'], 'string', 'max' => 20],
            [['reqid'], 'string', 'max' => 32],
            [['retMsg', 'name_details', 'id_details', 'ph_details'], 'string', 'max' => 64]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'basic_id' => '请求表id',
            'realname' => '用户真实姓名',
            'phone' => '手机',
            'idcard' => '身份证',
            'reqid' => '唯一请求识别码',
            'retCode' => '百度金融请求返回码',
            'retMsg' => '百度金融请求返回信息',
            'name_score' => '姓名信用分值',
            'name_details' => '姓名匹配详情',
            'id_score' => '身份证信用分值',
            'id_details' => '身份证匹配详情',
            'ph_score' => '手机号信用分值',
            'ph_details' => '手机号匹配详情',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ]; 
    } 

    /**
     * 获取百度金融结果
     */
    public function getResult($phone,$idcard){
        $datetime = date('Y-m-d H:i:s', strtotime('-3 month'));
        $where = ['AND',
            [
                'phone' => $phone,
                'idcard' => $idcard,
                'retCode' => 0,
            ],
            ['!=','retMsg','null'],
            ['>','create_time',$datetime],
        ];
        $data = static::find() -> where($where)->orderBy('create_time DESC')  -> limit(1) ->one();
        return $data;
    }   
    /**
     * 记录百度金融请求
     */
    public function saveData($data,$reqid)
    {
        $time = date("Y-m-d H:i:s"); 
        $postData = [ 
            'basic_id' => (int)$data['id'],
            'realname' => (string)$data['name'],
            'phone' => (string)$data['phone'],
            'idcard' =>  (string)$data['idcard'],
            'reqid' => (string)$reqid,
            'modify_time' => $time,
            'create_time' => $time,
        ]; 
        $error = $this->chkAttributes($postData); 
        if ($error) { 
            Logger::dayLog("xs","db","XsBaiduprea/saveData","save failed", $postData, $error);
            return false; 
        }
        return $this->save(); 
    }
    //更新请求
    public function updateBdInfo($baidu_result)
    {
        $this->retCode = $baidu_result['retCode'];
        $this->retMsg = $baidu_result['retMsg'];
        $this->modify_time = date("Y-m-d H:i:s");
        if (isset($baidu_result['result']) && !empty($baidu_result['result'])) {
            $result = $baidu_result['result'];
            if (isset($result['name']) && !empty($result['name'])) {
                $name = $result['name'];
                if ($name['res'] != 0) {
                    $this->name_score = isset($name['values'][0]) ? (int)$name['values'][0] : 0;
                    $this->name_details = isset($name['values'][1]) ? (string)$name['values'][1] : '';

                }
            }
            if (isset($result['identity']) && !empty($result['identity'])) {
                $identity = $result['identity'];
                if ($identity['res'] != 0) {
                    $this->id_score = isset($identity['values'][0]) ? (int)$identity['values'][0] : 0;
                    $this->id_details = isset($identity['values'][1]) ? (string)$identity['values'][1] : '';
                }
            }
            if (isset($result['phone']) && !empty($result['phone'])) {
                $phone = $result['phone'];
                if ($phone['res'] != 0) {
                    $this->ph_score = isset($phone['values'][0]) ? (int)$phone['values'][0] : 0;
                    $this->ph_details = isset($phone['values'][1]) ? (string)$phone['values'][1] : '';
                }
            }
        }
        return $this->save();
    }
}
