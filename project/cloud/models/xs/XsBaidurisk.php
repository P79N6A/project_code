<?php

namespace app\models\xs;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "{{%baidurisk}}".
 *
 * @property string $id
 * @property string $basic_id
 * @property string $identity_id
 * @property string $realname
 * @property string $phone
 * @property string $idcard
 * @property string $black_level
 * @property string $detail_info
 * @property string $modify_time
 * @property string $create_time
 */
class XsBaidurisk extends \app\models\repo\CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_baidurisk';
    }

    /**
     * @inheritdoc
     */
        public function rules() 
    { 
        return [
            [['basic_id', 'retCode'], 'integer'],
            [['identity_id', 'realname', 'phone', 'idcard', 'modify_time', 'create_time'], 'required'],
            [['detail_info'], 'string'],
            [['modify_time', 'create_time'], 'safe'],
            [['identity_id'], 'string', 'max' => 50],
            [['realname', 'phone', 'idcard'], 'string', 'max' => 20],
            [['retMsg'], 'string', 'max' => 64],
            [['black_level'], 'string', 'max' => 4]
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
            'identity_id' => '用户唯一标识',
            'realname' => '用户真实姓名',
            'phone' => '手机',
            'idcard' => '身份证',
            'retCode' => '百度金融请求返回码',
            'retMsg' => '百度金融请求返回信息',
            'black_level' => '百度金融用户评级',
            'detail_info' => '百度金融征信详情',
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
    public function saveData($data,$res)
    {
        $time = date("Y-m-d H:i:s"); 
        $postData = [ 
            'basic_id' => (int)(ArrayHelper::getValue($res,'basic_id',0)),
            'identity_id' => (string)$data['identity_id'],
            'realname' => (string)$data['name'],
            'phone' => (string)$data['phone'],
            'idcard' =>  (string)$data['idcard'],
            'modify_time' => $time,
            'create_time' => $time,
        ]; 
        $error = $this->chkAttributes($postData); 
        if ($error) { 
            Logger::dayLog("xs","db","XsBaidurisk/saveData","save failed", $postData, $error);
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
            $blackLevel = $baidu_result['result']['blackLevel'];
            if (!empty($blackLevel)) {
                $this->black_level = $blackLevel;
            }
            $blackDetails = $baidu_result['result']['blackDetails'];
            if (!empty($blackDetails)) {
                $this->detail_info = $blackDetails;
            }
        }
        return $this->save();
    }
}
