<?php
/**
 * 创蓝校验手机空号检测
 * 请求表
 * @author 孙瑞
 */
namespace app\models\mbcheck;

use Yii;
use app\common\Logger;
use app\models\BaseModel;

class MbcheckRequest extends BaseModel{
	const STATUS_INIT = 1; // 初始
	const STATUS_DOING = 101; // 抓取中
	const STATUS_SUCCESS = 2; // 成功
	const STATUS_RETRY = 3; // 重试
	const STATUS_FAILURE = 11; // 通知失败

	private $rquestMap;
	public function init(){
		$this->rquestMap = [
			'STATUS_INIT' => static::STATUS_INIT,
			'STATUS_DOING' => static::STATUS_DOING,
			'STATUS_SUCCESS' => static::STATUS_SUCCESS,
			'STATUS_RETRY' => static::STATUS_RETRY,
			'STATUS_FAILURE' => static::STATUS_FAILURE,
		];
	}

	public static function tableName(){
		return 'xhh_mbcheck_request';
	}

	public function rules(){
		return [
			[['mobile', 'request_status', 'create_time', 'aid', 'modify_time', 'callback_url'], 'required'],
			[['create_time', 'modify_time'], 'safe'],
			[['request_status', 'aid', 'mobile_status', 'batch_no'], 'integer'],
			[['mobile'], 'string', 'max' => 20],
			[['callback_url'], 'string', 'max' => 100],
			[['reason'], 'string', 'max' => 200],
		];
	}

	public function attributeLabels(){
		return [
			'id' => 'ID',
			'aid' => '应用ID',
			'mobile' => '手机号',
			'batch_no' => '请求批次号',
			'mobile_status' => '手机号状态 0:未检测 1:空号  2:实号  3:停机  4:库无  5:沉默号 11:失败',
			'create_time' => '创建时间',
			'modify_time' => '更新时间',
			'callback_url' => '回调url',
			'request_status' => '请求状态 1：默认，2成功，3重试，11失败',
			'reason' => '创蓝检测返回业务信息',
		];
	}

	// 乐观锁
    public function optimisticLock() {
        return "version";
    }
	
	/**
	 * 添加请求记录
	 * @param1 array $saveData 请求数据
	 * @return bool true 添加成功并已初始化 false 添加失败
	 */
	public function insertRequestInfo($save_data){
		if(!$save_data){
			return 0;
		}
		$nowTime = date('Y-m-d H:i:s');
		$save_data['create_time'] = $nowTime;
		$save_data['modify_time'] = $nowTime;
		$save_data['request_status'] = static::STATUS_INIT;
		$error = $this->chkAttributes($save_data);
		if ($error) {
			Logger::dayLog('mbcheck','request/error 添加请求数据错误:'.json_encode($error));
			return $this->returnError(0, $error);
		}
		if(!$this->save()){
			return 0;
		}
		return $this->id;
	}

	/**
	 * 根据条件查询单条语句
	 * @param mixed $data 查询条件字段值
	 * @param string $column 字段名
	 * @param string $order 排序规则
	 * @return false获取失败 obj返回查询到的数据对象
	 */
	public function getOne($data, $column='id', $order='id asc'){
		if(!$data){
			return false;
		}
		$result = self::find()->where([$column => $data])->orderBy($order)->one();
		return $result;
	}

	/**
     * 查询是否超过最大请求限制
     * @param int $mobile 手机号
     * @param int $time 时间区间单位秒 默认300秒
     * @param int $max 最大请求次数 默认10
     * @return bool False指请求大于最大请求次数
     */
    public function isOverMaxRequest($mobile, $time = 300, $max = 10){
        if(!$mobile){
            return false;
        }
        $timestamp = time();
        $endTime = date('Y-m-d H:i:s', $timestamp);
        $startTime = date('Y-m-d H:i:s', $timestamp-$time);
        $where = ['AND',
            ['mobile' => $mobile],
            ['>', 'create_time', $startTime],
            ['<=', 'create_time', $endTime],
        ];
        $num = self::find()->where($where)->count();
        return $num<$max;
    }

	/**
	 * 获取符合抓取要求的数据列表
	 * @param int $limit 每次获取的条数 默认为200
	 * @return array 数组内部是查询出的每条数据的对象
	 */
	public function getProcessList($start_time, $end_time, $limit=200) {
		$where = ['AND',
			['in','request_status',[static::STATUS_INIT, static::STATUS_RETRY]],
			['>=', 'create_time', $start_time],
			['<', 'create_time', $end_time],
		];
		$data_list = self::find()->where($where)->limit($limit)->all();
		if (!$data_list) {
			return [];
		}
		return $data_list;
	}

	/**
	 * 单独修改请求表状态
	 * @param int $requestid 请求表id
	 * @param int $request_status 请求修改成的状态
	 * @param array $save_data 保存数据信息
	 * @return bool 状态是否修改成功
	 */
	public function changeRequestStatus($requestid, $request_status, $mobile_status, $reason){
		if(!$requestid || !$request_status || !$mobile_status || !$reason){
			return false;
		}
		$requestObj = $this->getOne($requestid);
		if(!$requestObj){
			Logger::dayLog('mbcheck', 'request/error 请求id为'.$requestid.'的数据对象获取失败');
			return false;
		}
		$now_time = date('Y-m-d H:i:s');
		$save_data['request_status'] = $request_status;
		$save_data['mobile_status'] = $mobile_status;
		$save_data['reason'] = $reason;
		$save_data['modify_time'] = $now_time;
		$error = $requestObj->chkAttributes($save_data);
		if ($error) {
			Logger::dayLog('mbcheck', 'request/error 请求id为'.$requestid.'的表状态修改失败');
			return $requestObj->returnError(false, $error);
		}
		if(!$requestObj->update($save_data)){
			return false;
		}
		return true;
	}

	/**
	 * 批量修改请求状态
	 * @param array $ids 请求表id数组
	 * @param string $reason 操作理由
	 * @return bool 状态修改是否成功
	 */
	public function retryStatus($ids, $reason) {
		if(!is_array($ids) || empty($ids) || !$reason){
			return false;
		}
		$data = [
			'request_status' => static::STATUS_RETRY,
			'modify_time' => date('Y-m-d H:i:s'),
			'reason' => $reason,
		];
		return static::updateAll($data, ['id' => $ids]);
	}

	/**
	 * 锁定正在抓取的状态
	 * @param array $ids 请求表id数组
	 * @return bool 锁定是否成功
	 */
	public function lockStatus($ids) {
		if (!is_array($ids) || empty($ids)) {
			return false;
		}
		return static::updateAll(['request_status' => static::STATUS_DOING], ['id' => $ids]);
	}

	/**
	 * 获取最新的批次号
	 * @return int 最新的批次号
	 */
	public function getNewBatch() {
		$max_batch = self::find()->orderBy('batch_no desc')->one();
		if($max_batch){
			$batch_no = intval($max_batch->batch_no) + 1;
		}else{
			$batch_no = 1;
		}
		return $batch_no;
	}
}