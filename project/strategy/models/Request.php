<?php

namespace app\models;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\Result;

/**
 * This is the model class for table "st_request".
 *
 * @property string $id
 * @property string $request_id
 * @property string $user_id
 * @property integer $from
 * @property string $loan_id
 * @property string $create_time
 * @property string $modify_time
 */
class Request extends BaseModel
{
    const REG = 1;
    const SYSLOAN = 5;
    const PROMEV4 = 6;
    const PROMEV4TEST = 99;
    const TIANQI = 7;
    const PERIODS = 8;
    const TXSKEDU = 9;
    const OVERBEFORE = 10;
    const REPORT_CREDIT = 11;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_request';
    }

     /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['user_id', 'from'], 'required'],
            [['user_id', 'from', 'loan_id', 'prd_type', 'basic_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['loan_no'], 'string', 'max' => 64],
            [['req_id'], 'string', 'max' => 30]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'request_id' => '请求ID（唯一）',
            'loan_no' => '借款识别码',
            'user_id' => '业务端用户ID',
            'from' => '决策请求来源',
            'loan_id' => '借款ID',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
            'prd_type' => '产品类型，1 一亿元；2 7-14天',
            'req_id' => '业务端唯一标识',
            'basic_id' => 'cloud基本表唯一标识',
        ]; 
    }

    public function getReqInfo($data)
    {
        $req_id = ArrayHelper::getValue($data,'req_id');
        $aid = ArrayHelper::getValue($data,'aid');
        $from = ArrayHelper::getValue($data,'from');
        $where = [
                'and',
                ['req_id'=>$req_id],
                ['prd_type'=> $aid],
                ['from'=>$from],
            ];
        $req = $this->find()->where($where)->one();
        if (!empty($req)) {
            //获取上一次结果
            $request_id = $req->request_id;
            $result = new Result();
            $res = $result->getRes($request_id);
            return $res;
        }
        return $req;
    }

    public function addRequest($postData)
    {
        $nowtime = date('Y-m-d H:i:s');
        $postData['create_time'] = $nowtime;
        $postData['modify_time'] = $nowtime;
        $error = $this->chkAttributes($postData);
        if ($error) {
            return $this->returnError(false, $error);
        }
        $res = $this->save();
        if (!$res) {
            return false;
        }
        return $id = Yii::$app->db->getLastInsertId();
    }

    public function saveRequest($postData)
    {
        $addData = [
            'loan_no' => (string)(isset($postData['loan_no']) ? $postData['loan_no'] : ''),
            'user_id' => isset($postData['identity_id']) ? $postData['identity_id'] : $postData['user_id'],
            'prd_type' => isset($postData['aid']) ? $postData['aid'] : 1,
            'from' => isset($postData['from']) ? $postData['from'] : 0,
            'loan_id' => isset($postData['loan_id']) ? $postData['loan_id'] : 0,
            'req_id' => (string)(isset($postData['req_id']) ? $postData['req_id'] : ''),
        ];

        $nowtime = date('Y-m-d H:i:s');
        $addData['create_time'] = $nowtime;
        $addData['modify_time'] = $nowtime;

        $error = $this->chkAttributes($addData);
        if ($error) {
            return false;
        }
        $res = $this->save();
        if (!$res) {
            return false;
        }
        return $id = Yii::$app->db->getLastInsertId();
    }

    public function bindRequest($postdata,$ret_info)
    {
        $request_id = ArrayHelper::getValue($postdata,'request_id');
        $basic_id = ArrayHelper::getValue($ret_info,'basic_id');
        $obj = $this->findOne($request_id);
        $obj->basic_id = $basic_id;
        $obj->modify_time = date('Y-m-d H:i:s');
        $res = $obj->save();
        if (!$res) {
            Logger::dayLog('api/bindRequest',$postdata,$ret_info,$obj->errors);
            return $res;
        }
        return $res;
    }

    public function getRequestByReqid($req_id){
        // 一天前
        $time = date("Y-m-d", strtotime("-3 day"));
        $where = [
            'and',
            ['req_id'=>(string)$req_id],
            // ['in','from',[3,16,18]],
            ['>=','create_time',$time],
        ];
        return  $this->find()->where($where)->orderBy('request_id DESC')->one();
    }

    public function getRequestByReqidOne($req_id){
        $where = [
            'and',
            ['req_id'=>(string)$req_id],
        ];
        return  $this->find()->where($where)->asArray()->orderBy('request_id DESC')->one();
    }
}
