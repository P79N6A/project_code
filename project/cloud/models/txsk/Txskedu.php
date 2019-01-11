<?php

namespace app\models\txsk;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "dc_txskedu".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $aid
 * @property string $name
 * @property string $idcard
 * @property string $retCode
 * @property string $result_info
 * @property string $modify_time
 * @property string $create_time
 */
class Txskedu extends \app\models\repo\CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_txskedu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'aid', 'name', 'idcard', 'modify_time', 'create_time'], 'required'],
            [['user_id', 'aid'], 'integer'],
            [['result_info'], 'string'],
            [['modify_time', 'create_time'], 'safe'],
            [['name', 'idcard', 'retCode'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '业务端用户唯一标识',
            'aid' => '请求来源：1 一亿元；8 7-14',
            'name' => '用户真实姓名',
            'idcard' => '身份证',
            'retCode' => '请求状态码',
            'result_info' => '返回结果详情',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 记录天行数科学信请求
     */
    public function saveData($data)
    {
        $time = date("Y-m-d H:i:s"); 
        $postData = [ 
            'user_id' => (int)$data['user_id'],
            'aid' => isset($data['aid']) ? (int)$data['aid'] : 1,
            'name' => (string)($data['realname']),
            'idcard' =>  (string)$data['identity'],
            'modify_time' => $time,
            'create_time' => $time,
        ]; 
        $error = $this->chkAttributes($postData); 
        if ($error) { 
            Logger::dayLog("org","db","Txskedu/saveData","save failed", $postData, $error);
            return false;
        }
        return $this->save();
    }

    /**
     * 更新天行数科学信网信息
     */
    public function updateXxwInfo($edu_res)
    {
        if (isset($edu_res['data']) && isset($edu_res['success']) && $edu_res['success']) {
            $edu_res = $edu_res['data'];
        }
        
        $this->retCode = isset($edu_res['code']) ? (string)$edu_res['code'] : '0000';
        $this->modify_time = date("Y-m-d H:i:s");
        $this->result_info = isset($edu_res) ? json_encode($edu_res,JSON_UNESCAPED_UNICODE) : '';
        $res = $this->save();
        if (!$res) {
            Logger::dayLog("org","db","Txskedu/updateXxwInfo","save failed", $edu_res, $this->errors);
        }
        return $res;
    }

    /**
     * 获取学信网信息
     */
    public function getResult($user_id, $idcard){
        $where = ['AND',
            [
                'user_id' => $user_id,
                'idcard' => $idcard,
                'retCode' => '0000',
            ],
            ['!=','result_info','null'],
        ];
        $data = static::find() -> where($where)->orderBy('id DESC')->asArray()->limit(1)->one();
        return $data;
    }   
}
