<?php

namespace app\models\xs;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "dc_baiduprea".
 *
 * @property string $id
 * @property string $basic_id
 * @property string $realname
 * @property string $phone
 * @property string $reqid
 * @property string $idcard
 * @property integer $retCode
 * @property string $retMsg
 * @property integer $score
 * @property string $models
 * @property string $modify_time
 * @property string $create_time
 */
class XsBaiduprea extends \app\models\xs\XsBaseNewModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_baiduprea';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['basic_id', 'retCode', 'score'], 'integer'],
            [['realname', 'phone', 'idcard', 'modify_time', 'create_time'], 'required'],
            [['modify_time', 'create_time'], 'safe'],
            [['realname', 'phone', 'idcard'], 'string', 'max' => 20],
            [['reqid', 'retMsg', 'models'], 'string', 'max' => 64]
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
            'phone' => '手机号',
            'reqid' => '唯一请求识别码',
            'idcard' => '身份证',
            'retCode' => '百度金融请求返回码',
            'retMsg' => '百度金融请求返回信息',
            'score' => '信用分值',
            'models' => '信用分模型',
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
        $data = static::find()->where($where)->orderBy('create_time DESC')->limit(1)->one();
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
            if (isset($result['scoreInfo']) && !empty($result['scoreInfo'])) {
                $scoreInfo = $result['scoreInfo'];
                if (!empty($scoreInfo)) {
                    $this->models = isset($scoreInfo['models']) ? (string)$scoreInfo['models'] : '';
                }
                if (!empty($scoreInfo)) {
                    $this->score = isset($scoreInfo['score']) ? (int)$scoreInfo['score'] : 0;
                }
            }
            
        }
        return $this->save();
    }
}
