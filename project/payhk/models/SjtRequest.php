<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "sjt_request".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $name
 * @property string $idcard
 * @property string $phone
 * @property string $task_id
 * @property string $account
 * @property string $password
 * @property string $website
 * @property string $code
 * @property string $message
 * @property string $task_stage
 * @property integer $is_smscode
 * @property integer $is_authcode
 * @property string $auth_code
 * @property integer $source
 * @property integer $from
 * @property string $create_time
 * @property string $modify_time
 * @property string $callbackurl
 * @property integer $client_status
 * @property integer $version
 */
class SjtRequest extends BaseModel
{
    const RESULT_STATUS_INIT = 0;//初始
    const RESULT_STATUS_DOING = 1;//采集中
    const RESULT_STATUS_SUCCESS = 2;//成功
    const RESULT_STATUS_FAILURE = 3;//失败
    const STATUS_REQING_QUERY = 4; // 查询请求中
    const RESULT_DETAIL_SUCCESS = 5;//详单成功
    const RESULT_MIDDLE_SUCCESS = 6;//查询历史数据存在 中间状态
    const STATUS_QUERY_MAX = 13; // 查询达上限
    const MAX_QUERY_NUM = 7; // 最大查询次数
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sjt_request';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid','requestid', 'name', 'phone', 'password','create_time','modify_time', 'callbackurl'], 'required'],
            [['aid', 'requestid','is_smscode', 'is_authcode', 'source', 'from', 'client_status','result_status', 'version'], 'integer'],
            [['create_time', 'modify_time','query_time'], 'safe'],
            [['name', 'task_id','password', 'website'], 'string', 'max' => 50],
            [['idcard', 'phone'], 'string', 'max' => 20],
            [['code', 'task_stage'], 'string', 'max' => 30],
            [['message','auth_code_path', 'callbackurl'], 'string', 'max' => 100],
            [['auth_code'],'string']
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
            'name' => 'Name',
            'idcard' => 'Idcard',
            'phone' => 'Phone',
            'task_id' => 'Task ID',
            'password' => 'Password',
            'website' => 'Website',
            'code' => 'Code',
            'message' => 'Message',
            'task_stage' => 'Task Stage',
            'is_smscode' => 'Is Smscode',
            'is_authcode' => 'Is Authcode',
            'auth_code' => 'Auth Code',
            'source' => 'Source',
            'from' => 'From',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'callbackurl' => 'Callbackurl',
            'client_status' => 'Client Status',
            'result_status' => 'Result Status',
            'version' => 'Version',
        ];
    }
    public function optimisticLock() {
        return "version";
    }
    /**
     * Undocumented function
     * 保存数据
     * @param [type] $postdata
     * @return void
     */
    public function saveData($postdata){
        // 检测数据
		if (!is_array($postdata) || empty($postdata)) {
			return $this->returnError(false, '不能为空');
        }
        $nowTime = date('Y-m-d H:i:s');
        $postdata['create_time'] = $nowTime;
        $postdata['modify_time'] = $nowTime;
        $postdata['query_time']  = $nowTime;
        $error = $this->chkAttributes($postdata);
		if ($error) {
			return $this->returnError(false, $error);
		}
        $result = $this->save();
		return $this;
    }
    /**
     * Undocumented function
     * 根据requestid查询数据集
     * @param [type] $requestid
     * @return void
     */
    public function getSjtData($requestid){        
        if(empty($requestid)) return false;
        $where = [
            'requestid'=>$requestid
        ];
        $data = static::find()->where($where)->one();
        return $data;
    }
    public function getSjtDataByCondition($where){
        if(empty($where) || !is_array($where)){
            return false;
        }
        $data = static::find()->where($where)->one();
        return $data;
    }
    /**
     * Undocumented function
     * 保存创建任务结果
     * @param [type] $task_id
     * @param [type] $code
     * @param [type] $message
     * @return void
     */
    public function saveCreateResult($code,$message,$task_id){
        $this->refresh();
        $this->task_id = $task_id;
        $this->code = (string)$code;
        $this->message = $message;
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * Undocumented function
     * 保存任务结果
     * @param [type] $code
     * @param [type] $message
     * @return void
     */
    public function saveResult($code,$message){
        $this->refresh();
        $this->code = (string)$code;
        $this->message = $message;
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * Undocumented function
     * 保存手机验证码 图片验证码 图片路径
     * @param [type] $data
     * @return void
     */
    public function saveCodeResult($data){
        $this->is_smscode = ArrayHelper::getValue($data,'is_smscode',0);
        $this->is_authcode = ArrayHelper::getValue($data,'is_authcode',0);
        if(!empty($data['auth_code'])){
            $this->auth_code = $data['auth_code'];
        }
        if(!empty($data['auth_code_path'])){
            $this->auth_code_path = $data['auth_code_path'];
        }
        if(!empty($data['task_stage'])){
            $this->task_stage = $data['task_stage'];
        }
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * Undocumented function
     * 任务提交成功 保存为采集中
     * @return void
     */
    public function saveToDoing(){
        $this->result_status = self::RESULT_STATUS_DOING;
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * Undocumented function
     * 保存客户端通知状态
     * @param [type] $client_status
     * @return void
     */
    public function saveClientStatus($client_status){
        $this->client_status = $client_status;
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * Undocumented function
     * 查询采集中的任务
     * @param integer $limit
     * @return void
     */
    public function getSjtDoingData($limit = 100){
        $where = [
            'and',
            ['result_status'=>static::RESULT_STATUS_DOING],
            ['<','query_time',date('Y-m-d H:i:s')],
            ['>=','query_time',date('Y-m-d H:i:s','-7 days')]
        ];
        $data = static::find()->where($where)->limit($limit)->all();
        var_dump($where);die;
        return $data;
    }
    /**
     * Undocumented function
     * 查询已有详单的数据
     * @param integer $limit
     * @return void
     */
    public function getSjtDetailData($limit = 100){
        $where = [
            'and',
            ['result_status'=>static::RESULT_DETAIL_SUCCESS],
            ['<','query_time',date('Y-m-d H:i:s')],
            ['>=','query_time',date('Y-m-d H:i:s','-7 days')]
        ];
        $data = static::find()->where($where)->limit($limit)->all();
        return $data;
    }
    /**
     * 锁定正在查询接口的状态
     */
    public function lockQuery($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['result_status' => static::STATUS_REQING_QUERY], ['id' => $ids]);
        return $ups;
    }
    /**
     * 单条锁定正在查询接口的状态
     */
    public function lockOneQuery(){
        try{
            $this->result_status = static::STATUS_REQING_QUERY;
            $result = $this->save();
        }catch(\Exception $e){
            $result = false;
        }
        return $result;
    }
    /**
     * 计算下次查询时间
     * @param int $query_num 当前次数
     * @param str $query_time 当前时间
     * @return str 下次查询时间
     */
    public function acQueryTime($query_num, $query_time) {
        // 累加的分钟
        $addMinutes = [
            1 => 1,
            2 => 5,
            3 => 10,
            4 => 30,
            5 => 60,
            6 => 120];

        // 不在上述时,不改变
        if (!isset($addMinutes[$query_num])) {
            return $query_time;
        }

        // 累加时间
        $time = ($query_time == '0000-00-00 00:00:00') ? time() : strtotime($query_time);
        $t = $time + $addMinutes[$query_num] * 60;
        return date('Y-m-d H:i:s', $t);
    }
    /**
     * Undocumented function
     * 保存详单成功状态
     * @return void
     */
    public function saveDetailSuccess($is_query=false){
        $this->result_status = static::RESULT_DETAIL_SUCCESS;
        $this->modify_time = date('Y-m-d H:i:s');
        if($is_query){
            $this->query_num++;
            if($this->query_num<static::MAX_QUERY_NUM){
                $this->query_time = $this->acQueryTime($this->query_num,$this->query_time);
            }else{
                $this->result_status = static::STATUS_QUERY_MAX;
            }
        }
        return $this->save();
    }
    /**
     * Undocumented function
     * 保存中间状态
     * @return void
     */
    public function saveMiddleSuccess(){
        $this->result_status = static::RESULT_MIDDLE_SUCCESS;
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * Undocumented function
     * 保存成功状态
     * @return void
     */
    public function saveReportSuccess(){
        $this->result_status = static::RESULT_STATUS_SUCCESS;
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    /**
     * Undocumented function
     * 保存失败状态
     * @return void
     */
    public function saveReportFailure($code,$message){
        $this->result_status = static::RESULT_STATUS_FAILURE;
        $this->modify_time = date('Y-m-d H:i:s');
        $this->code = (string) $code;
        $this->message = $message;
        return $this->save();
    }
}