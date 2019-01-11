<?php

namespace app\models\slbank;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\BaseModel;
/**
 * This is the model class for table "xhh_slbank_request".
 * @property integer $id
 * @property integer $user_id
 * @property string $org_biz_no
 * @property string $biz_no
 * @property integer $aid
 * @property integer $request_status
 * @property string $create_time
 * @property string $modify_time
 * @property string $callback_url
 * @property string $reason
 * @property string $name
 * @property string $mobile
 * @property string $idcard
 */
class SlbankRequest extends BaseModel{
    // 请求生命周期 0->4->1->2/11/3
    const STATUS_INIT = 0; // 初始
    const STATUS_DOING = 1; // 抓取中
    const STATUS_SUCCESS = 2; // 成功
    const STATUS_RETRY = 3; // 重试
    const STATUS_AUTH = 4;  // 已授权
    const STATUS_FAILURE = 11; // 通知失败

    private $rquestMap;
    public function init(){
        $this->rquestMap = [
            'STATUS_INIT' => static::STATUS_INIT,
            'STATUS_DOING' => static::STATUS_DOING,
            'STATUS_SUCCESS' => static::STATUS_SUCCESS,
            'STATUS_RETRY' => static::STATUS_RETRY,
            'STATUS_AUTH' => static::STATUS_AUTH,
            'STATUS_FAILURE' => static::STATUS_FAILURE,
        ];
    }

    public static function tableName(){
        return 'xhh_slbank_request';
    }

    public function rules(){
        return [
            [['user_id','request_status','create_time','aid','modify_time','callback_url'], 'required'],
            [['create_time','modify_time'], 'safe'],
            [['request_status', 'user_id','aid'], 'integer'],
            [['org_biz_no','biz_no','name'], 'string', 'max' => 50],
            [['callback_url'], 'string', 'max' => 100],
            [['reason'], 'string', 'max' => 200],
            [['idcard','mobile'], 'string', 'max' => 20],
        ];
    }

    public function attributeLabels(){
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'org_biz_no' => '商户生成请求流水号',
            'biz_no' => '数立平台生成抓取流水号',
            'aid' => '应用ID',
            'request_status' => '请求状态 0：默认，1抓取中，2成功，3重试，4已授权，11失败',
            'create_time' => '创建时间',
            'modify_time' => '更新时间',
            'callback_url' => '回调url',
            'reason' => '数据采集结果返回信息',
            'name' => '姓名',
            'mobile' => '手机号',
            'idcard' => '身份证号',
        ];
    }

    public function isFailRequest($request_status){
        return in_array($request_status, [static::STATUS_FAILURE, static::STATUS_INIT]);
    }

    public function isDoingRequest($request_status){
        return in_array($request_status, [static::STATUS_AUTH, static::STATUS_DOING, static::STATUS_RETRY]);
    }

    /**
     * 查询此用户是否成功获取过流水信息
     * @param int $userId 用户id
     * @param int $time 时间区间单位秒 默认300秒
     * @param int $max 最大请求次数 默认10
     * @return bool False指请求大于最大请求次数
     */
    public function isOverMaxRequest($userId,$time = 300,$max = 10){
        if(!$userId){
            return -1;
        }
        $timestamp = time();
        $endTime = date('Y-m-d H:i:s',$timestamp);
        $startTime = date('Y-m-d H:i:s',$timestamp-$time);
        $where = ['AND',
            ['user_id' => $userId],
            ['>', 'create_time', $startTime],
            ['<=', 'create_time', $endTime],
        ];
        $num = self::find()->where($where)->count();
        return $num<$max;
    }

    /**
     * 添加请求记录
     * @param1 array postData 请求数据
     * @return bool true 添加成功并已初始化 false 添加失败
     */
    public function insertRequestInfo($postData){
        $nowTime = date('Y-m-d H:i:s');
        $postData['create_time'] = $nowTime;
        $postData['modify_time'] = $nowTime;
        $postData['request_status'] = static::STATUS_INIT;
        $error = $this->chkAttributes($postData);
        if ($error) {
            Logger::dayLog('slbank/Request','index/error 添加请求数据错误:'.$error);
            return $this->returnError(false, $error);
        }
        if(!$this->save()){
            return false;
        }
        return $this->id;
    }

    /**
     * 根据条件查询单条语句
     * @param mixed $data 查询条件字段值
     * @param string $column 字段名
     * @return false获取失败 obj返回查询到的数据对象
     */
    public function getOne($data,$column='id'){
        if(!$data){
            return false;
        }
        $result = self::find()->where([$column=>$data])->one();
        return $result;
    }

    /**
     * 保存授权之后的数据信息
     * @param array $postData 授权后数据信息
     * @param object $requestObj 查询出的数据对象
     * @return bool false失败 true成功
     */
    public function saveBizNo($postData,$requestObj){
        if(!$requestObj || !$postData){
            return false;
        }
        $error = $requestObj->chkAttributes($postData);
        if ($error) {
            Logger::dayLog('slbank/Request','index/error 保存请求数据失败:'.$error);
            return $requestObj->returnError(false, $error);
        }
        $nowTime = date('Y-m-d H:i:s');
        $requestObj->biz_no = ArrayHelper::getValue($postData, 'biz_no');
        $requestObj->org_biz_no = ArrayHelper::getValue($postData, 'org_biz_no');
        $requestObj->request_status = static::STATUS_AUTH;
        $requestObj->modify_time = $nowTime;
        return $requestObj->update();
    }

    /**
     * 获取符合抓取要求的数据列表
     * @param int $limit 每次获取的条数 默认为200
     * @return array 数组内部是查询出的每条数据的对象
     */
    public function getRequestList($limit=200) {
		$startTime = time()-604800; // 60*60*24*7 一周内
		$endTime = time()-300; // 60*5 五分钟内
        $startTime = date('Y-m-d H:i:00', $startTime);
        $endTime = date('Y-m-d H:i:00', $endTime);
        $where = ['AND',
            ['request_status' => [static::STATUS_DOING, static::STATUS_AUTH, static::STATUS_RETRY]],
            ['>=', 'create_time', $startTime],
            ['<', 'create_time', $endTime],
        ];
        $dataList = self::find()->where($where)->limit($limit)->all();
        if (!$dataList) {
            return [];
        }
        return $dataList;
    }

    /**
     * 修改请求表状态
     * @param int $requestid 请求主键ID
     * @param int $requestStatus 请求修改成的状态
     * @param string $reason 修改状态原因
     * @param array $userInfo 请求返回的用户信息
     * @return bool 状态是否修改成功
     */
    public function changeRequestStatus($requestid,$requestStatus,$reason,$userInfo=[]){
        if(!$requestid || !$requestStatus || !$reason){
            return false;
        }
        $requestObj = $this->getOne($requestid);
        if(!$requestObj){
            Logger::dayLog('slbank/Collect','error 需要修改的数据信息不存在');
            return false;
        }
        $nowTime = date('Y-m-d H:i:s');
        $data = [
            'request_status' => $requestStatus,
            'modify_time' => $nowTime,
            'reason' => $reason,
        ];
        if(!empty($userInfo)){
            $data['name'] = ArrayHelper::getValue($userInfo, 'name');
            $data['mobile'] = ArrayHelper::getValue($userInfo, 'mobile');
            $data['idcard'] = ArrayHelper::getValue($userInfo, 'idcard');
        }
        $error = $requestObj->chkAttributes($data);
        if ($error) {
            Logger::dayLog('slbank/Collect','error 修改的数据状态失败:'.$error);
            return $requestObj->returnError(false, $error);
        }
        $requestObj->request_status = $requestStatus;
        $requestObj->modify_time = $nowTime;
        $requestObj->reason = $reason;
        if(!empty($userInfo)){
            $requestObj->name = ArrayHelper::getValue($userInfo, 'name','');
            $requestObj->mobile = ArrayHelper::getValue($userInfo, 'mobile','');
            $requestObj->idcard = ArrayHelper::getValue($userInfo, 'idcard','');
        }
        return $requestObj->update();
    }

    /**
     * 锁定正在抓取的状态
     */
    public function lockStatus($ids) {
        if (!is_array($ids) || empty($ids)) {
            return false;
        }
        return static::updateAll(['request_status' => static::STATUS_DOING], ['id' => $ids]);
    }
}