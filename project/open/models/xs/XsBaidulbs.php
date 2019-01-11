<?php

namespace app\models\xs;

use Yii;
use app\common\Logger;
use app\models\yyy\YiAddress;

/**
 * This is the model class for table "dc_baidulbs".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $user_id
 * @property integer $aid
 * @property string $name
 * @property string $phone
 * @property string $idcard
 * @property string $reqid
 * @property integer $retCode
 * @property string $retMsg
 * @property string $result_info
 * @property string $modify_time
 * @property string $create_time
 */
class XsBaidulbs extends \app\models\xs\XsBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_baidulbs';
    }

    public static function getDb() {
        return \Yii::$app->db_cloudnew;
    }
    /**
     * @inheritdoc
     */
    public function rules() 
    { 
        return [
            [['loan_id', 'user_id', 'aid', 'gps_id', 'retCode'], 'integer'],
            [['user_id', 'aid', 'name', 'phone', 'idcard', 'modify_time', 'create_time'], 'required'],
            [['result_info'], 'string'],
            [['modify_time', 'create_time'], 'safe'],
            [['loan_no', 'reqid'], 'string', 'max' => 32],
            [['name', 'phone', 'idcard'], 'string', 'max' => 20],
            [['retMsg'], 'string', 'max' => 64]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'loan_no' => '用户业务端借款唯一识别NUM ',
            'loan_id' => '借款ID',
            'user_id' => '业务端用户唯一标识',
            'aid' => '请求来源：1 一亿元；8 7-14',
            'name' => '用户真实姓名',
            'phone' => '手机',
            'idcard' => '身份证',
            'reqid' => '唯一请求识别码',
            'gps_id' => '本次借款对应的gps_id',
            'retCode' => '请求返回码',
            'retMsg' => '请求返回信息',
            'result_info' => 'LBS返回结果详情',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ]; 
    } 

    /**
     * 记录百度LBS请求
     */
    public function saveData($data,$reqid)
    {
        $time = date("Y-m-d H:i:s"); 
        $postData = [ 
            'user_id' => (int)$data['user_id'],
            'loan_no' => isset($data['loan_no']) ? (string)$data['loan_no'] : '',
            'loan_id' =>isset($data['loan_id']) ? (int)$data['loan_id'] : '',
            'aid' => isset($data['aid']) ? (int)$data['aid'] : 1,
            'name' => (string)($data['realname']),
            'phone' => (string)$data['mobile'],
            'idcard' =>  (string)$data['identity'],
            'gps_id' => (int)$data['gps_id'],
            'reqid' => (string)$reqid,
            'modify_time' => $time,
            'create_time' => $time,
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

    /**
     * 获取百度金融结果
     */
    public function getResult($phone,$idcard){
        $where = ['AND',
            [
                'phone' => $phone,
                'idcard' => $idcard,
                'retCode' => 0,
            ],
            ['!=','retMsg','null'],
        ];
        $data = static::find() -> where($where)->orderBy('id DESC')->limit(1)->one();
        return $data;
    }

    public function getBaiduLbsData($where)
    {
        return static::find() -> where($where)->orderBy('id DESC')->limit(1)->one();
    }   

    /**
     * 表关联关系
     */
    public function getAddress() {
        return $this->hasOne(YiAddress::className(), ['id' => 'gps_id']);
    }
}

