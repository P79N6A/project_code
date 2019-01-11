<?php

namespace app\models\rc;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;

/**
 * This is the model class for table "rc_baidulbs".
 *
 * @property string $id
 * @property integer $aid
 * @property string $phone
 * @property string $idcard
 * @property string $latitude
 * @property string $longtitude
 * @property string $location
 * @property string $reqid
 * @property integer $retCode
 * @property string $retMsg
 * @property string $result_info
 * @property string $modify_time
 * @property string $create_time
 * @property string $credit_id
 */
class RcBaidulbs extends RcBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rc_baidulbs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'phone', 'idcard', 'latitude', 'longtitude', 'location', 'modify_time', 'create_time'], 'required'],
            [['aid', 'retCode', 'credit_id'], 'integer'],
            [['result_info'], 'string'],
            [['modify_time', 'create_time'], 'safe'],
            [['phone', 'idcard'], 'string', 'max' => 20],
            [['latitude', 'longtitude', 'retMsg'], 'string', 'max' => 64],
            [['location'], 'string', 'max' => 128],
            [['reqid'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => '请求来源：1 一亿元；8 7-14',
            'phone' => '手机',
            'idcard' => '身份证',
            'latitude' => 'gps:纬度',
            'longtitude' => 'gps:经度',
            'location' => '地址',
            'reqid' => '唯一请求识别码',
            'retCode' => '请求返回码',
            'retMsg' => '请求返回信息',
            'result_info' => 'LBS返回结果详情',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
            'credit_id' => '评测ID',
        ];
    }

    /**
     * 记录百度LBS请求
     */
    public function saveData($data,$reqid)
    {
        $time = date("Y-m-d H:i:s"); 
        $postData = [ 
            'aid' => isset($data['aid']) ? (int)$data['aid'] : 1,
            'phone' => (string)$data['mobile'],
            'idcard' =>  (string)$data['identity'],
            'latitude' => ArrayHelper::getValue($data,'latitude',''),
            'longtitude' => ArrayHelper::getValue($data,'longtitude',''),
            'location' => ArrayHelper::getValue($data,'location',''),
            'reqid' => (string)$reqid,
            'modify_time' => $time,
            'create_time' => $time,
            'credit_id' => isset($data['credit_id']) ? (string)$data['credit_id'] : 0,
        ]; 
        $error = $this->chkAttributes($postData); 
        if ($error) { 
            Logger::dayLog("org","db","XsBaidulbs/saveData","save failed", $postData, $error);
            return false; 
        }
        return $this->save(); 
    }

    /**
     * 更新百度LBS请求
     */
    public function updateLbsInfo($baidu_result)
    {
        $this->retCode = $baidu_result['retCode'];
        $this->retMsg = $baidu_result['retMsg'];
        $this->modify_time = date("Y-m-d H:i:s");
        $this->result_info = isset($baidu_result['result']) ? json_encode($baidu_result['result'],JSON_UNESCAPED_UNICODE) : '';
        return $this->save();
    }
}
