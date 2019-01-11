<?php
/**
 * 创蓝校验手机空号检测
 * 通知表
 * @author 孙瑞
 */
namespace app\models\mbcheck;

use Yii;
use app\models\BaseModel;

class MbcheckNotify extends BaseModel{
	// 通知状态
	const STATUS_INIT = 0; // 初始
	const STATUS_DOING = 1; // 通知中
	const STATUS_SUCCESS = 2; // 成功
	const STATUS_RETRY = 3; // 重试
	const STATUS_FAILURE = 11; // 通知失败
	const STATUS_NOTIFY_MAX = 5; // 通知达上限

	const MAX_NOTIFY = 7; // 最大查询次数

	private $notifyMap;
	public function init(){
		$this->notifyMap = [
			'STATUS_INIT' => static::STATUS_INIT,
			'STATUS_DOING' => static::STATUS_DOING,
			'STATUS_SUCCESS' => static::STATUS_SUCCESS,
			'STATUS_RETRY' => static::STATUS_RETRY,
			'STATUS_FAILURE' => static::STATUS_FAILURE,
			'STATUS_NOTIFY_MAX' => static::STATUS_NOTIFY_MAX,
		];
	}

	public static function tableName(){
		return 'xhh_mbcheck_notify';
	}

	public function rules(){
		return [
			[['requestid','request_status','notify_num','notify_status','create_time'], 'required'],
			[['create_time','notify_time'], 'safe'],
			[['reason'], 'string', 'max' => 200],
		];
	}

	public function attributeLabels(){
		return [
			'id' => 'ID',
			'requestid' => '请求表ID',
			'request_status' => '请求数据采集结果 2成功 11失败',
			'notify_num' => '请求上次数，上限7',
			'notify_status' => '通知状态: 0初始 1通知中 2通知成功 3重试 4通知失败 5通知上限',
			'notify_time' => '下次通知时间',
			'create_time' => '创建时间',
			'reason' => '客户响应结果',
		];
	}

	// 乐观锁
    public function optimisticLock() {
        return "version";
    }

	/**
	 * 添加数据到通知表库中
	 * @param int $requestid 请求表主键Id
	 * @param int $status 请求表数据采集状态
	 * @return bool 数据保存是否成功
	 */
	public function addNotify($requestid, $status) {
		if(!$requestid || !$status){
			return false;
		}
		$nowTime = date('Y-m-d H:i:s');
		$data = [
			'requestid' => $requestid,
			'request_status' => $status,
			'notify_num' => 0,
			'notify_status' => static::STATUS_INIT,
			'reason' => '',
			'notify_time' => $nowTime,
			'create_time' => $nowTime,
		];
		$error = $this->chkAttributes($data);
		if ($error) {
			return $this->returnError(false, current($error));
		}
		return $this->save();
	}

	/**
	 * 获取状态为初始和重试的记录
	 * @param $start_time 获取通知时间区间的开始时间
	 * @param $end_time  获取通知时间区间的结束时间
	 * @param int $limit 每次获取的条数 默认为200
	 * @return array 数组内部是查询出的每条数据的对象
	 */
	public function getNotifyList($start_time = '', $end_time = '', $limit=200) {
		$where = ['AND',
			['in','notify_status',[static::STATUS_INIT, static::STATUS_RETRY]],
			['>=', 'notify_time', $start_time],
			['<', 'notify_time', $end_time],
		];
		$data_list = self::find()->where($where)->limit($limit)->all();
		$sql= self::find()->where($where)->createCommand()->getRawSql();
		if (!$data_list) {
			return [];
		}
		return $data_list;
	}

	/**
	 * 锁定正在通知的状态
	 */
	public function lockNotify($ids) {
		if (!is_array($ids) || empty($ids)) {
			return 0;
		}
		return static::updateAll(['notify_status' => static::STATUS_DOING], ['id' => $ids]);
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
		$result = self::find()->where([$column => $data])->one();
		return $result;
	}

	/**
	 * 回写响应结果
	 * $this 操作数据
	 * @param $notify_status 通知状态
	 * @return bool
	 */
	public function changeNotifyStatus($notifyid, $notify_status, $reason){
		if(!$notifyid || !$notify_status || !$reason){
			return false;
		}
		$oNotifyRow = $this->getOne($notifyid);
		$reason = substr($reason, 0, 20);
		$notify_num = $oNotifyRow->notify_num;
		$notify_num += 1;
		if($notify_status == static::STATUS_FAILURE || $notify_status == static::STATUS_SUCCESS){
			$oNotifyRow->notify_status = $notify_status;
			$oNotifyRow->reason = $reason;
			$oNotifyRow->notify_num = $notify_num;
			return $oNotifyRow->update();
		}
		$notify_time = $oNotifyRow->notify_time;
		if($notify_num < static::MAX_NOTIFY){
			// 未超通知限制, 计算下次查询时间间隔
			$notify_time = $this->acNotifyTime($notify_num, $notify_time);
		}else{
			$notify_status = static::STATUS_NOTIFY_MAX;
		}
		$oNotifyRow->notify_status = $notify_status;
		$oNotifyRow->reason = $reason;
		$oNotifyRow->notify_num = $notify_num;
		$oNotifyRow->notify_time = $notify_time;
		return $oNotifyRow->update();
	}

	/**
	 * 计算下次查询时间
	 * @param int $notify_num 当前次数
	 * @param str $notify_time 当前时间
	 * @return str 下次查询时间
	 */
	public function acNotifyTime($notify_num, $notify_time) {
		// 累加的分钟
		$add_minutes = [
			1 => 5,
			2 => 30,
			3 => 89,
			4 => 233,
			5 => 610,
			6 => 1560,
		];

		// 不在上述时,不改变
		if (!isset($add_minutes[$notify_num])) {
			return $notify_time;
		}

		// 累加时间
		$time = ($notify_time == '0000-00-00 00:00:00') ? time() : strtotime($notify_time);
		$t = $time + $add_minutes[$notify_num] * 60;
		return date('Y-m-d H:i:s', $t);
	}
}