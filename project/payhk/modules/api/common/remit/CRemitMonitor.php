<?php
/**
 * 出款监控计划任务
 * 分为两种监控:
 * 1. 系统监控 sys开头
 * 2. 业务监控
 */
namespace app\modules\api\common\remit;
use Yii;
use app\common\Logger;
use app\models\remit\ClientNotify;
use app\models\remit\Remit;
use app\models\remit\ApiLog;
use app\models\remit\Setting;
use yii\helpers\ArrayHelper;
use app\common\Func;

class CRemitMonitor {
	/**
	 * 邮箱数组
	 * @var []
	 */
	private $mailers;
	/**
	 * 发短信的手机号
	 * @var []
	 */
	private $phones;
	/**
	 * 初始化接口
	 */
	public function __construct() {
		$configPath =  __DIR__ . '/config/config.php';
		if( !file_exists($configPath) ){
			throw new \Exception($configPath."配置文件不存在",6000);
		}
		$config = include( $configPath );

		$this->mailers = $config['mailers'];
		$this->phones= $config['phones'];
		if(!$this->mailers &&$this->phones ){
			throw new \Exception($configPath."收件人和手机均为空",6000);
		}
	}

	//************************************start 系统监控***********/
	/**
	 * 监控创建时间在相应的表中是否在某段时间内没有纪录
	 */
	public function sys($start_time){
		//1 起止时间为前后一小时, 精确到分钟
		$time = strtotime($start_time);
		$start_time = date('Y-m-d H:00:00', $time );
		$end_time  = date('Y-m-d H:00:00', $time + 3600 );

		//2 查询接收,中信接口和通知表
		$request_total = $this->sysRequest($start_time, $end_time);
		$api_total = $this->sysApi($start_time, $end_time);
		$notify_total = $this->sysNotify($start_time, $end_time);

		//3 查询超时情况
		$api_timeouts = [];
		if($api_total > 0){
			$api_timeouts = $this->sysApiTimeout($start_time, $end_time);
		}

		//3 若数据=0, 则可能存在异常
		$except = 	$request_total==0  || // 表示无请求为0. 可能有异常
					$api_total==0 ||  // 表示访问中信接口为0. 可能有异常
					$notify_total==0 ||   // 表示通知次数为0. 可能有异常
					$api_timeouts;   // 表示访问中信接口含有超时数据, 那么有异常
		if(!$except){
			return false; // 表示无通知,无异常
		}


		//5 各表数据添加情况
		$data =  [
			'request_total'=> $request_total,
			'api_total' 	=> $api_total ,
			'notify_total'=> $notify_total ,
			'start_time' => $start_time,
			'end_time'  => $end_time,
			'api_timeouts'  => $api_timeouts,
			'remitStatus'  =>  (new Remit) -> getStatus(),
		];

		//5 发送邮件
		$isMail = $this->sendMail("remitmonitor/sys", "系统监控: 异常报告_{$start_time} ~ {$end_time}", $data);
		if (!$isMail) {
			Logger::dayLog('remitmonitor', 'sys', "邮件发送失败", $data);
		}

		return true;

	}
	// rt_remit 接收请求数量
	private function sysRequest($start_time, $end_time) {
		$where = [
			'AND',
			['>=', 'create_time', $start_time],
			['<', 'create_time', $end_time], 
		];

		$total = Remit::find() -> where($where) -> count();
		return $total;
	}
	// rt_api_log 出款,查询请求数量
	private function sysApi($start_time, $end_time) {
		$where = [
			'AND',
			['>=', 'start_time', $start_time],
			['<', 'start_time', $end_time], 
		];

		$total = ApiLog::find() -> where($where) -> count();
		return $total;
	}
	// rt_api_log 出款,查询无响应的数据
	private function sysApiTimeout($start_time, $end_time) {
		$where = [
			'AND',
			['rsp_status'=>'HTTP_NOT_200'],
			['>=', 'start_time', $start_time],
			['<', 'start_time', $end_time], 
		];

		$data = ApiLog::find() -> where($where) -> orderBy("start_time DESC") -> limit(100) -> all();
		return $data;
	}

	// rt_client_notify 通知纪录数量
	private function sysNotify($start_time, $end_time) {
		$where = [
			'AND',
			['>=', 'create_time', $start_time],
			['<', 'create_time', $end_time], 
		];

		$total = ClientNotify::find() -> where($where) -> count();
		return $total;
	}
	//************************************end 系统监控***********/



	//************************************start 业务监控***********/
	/**
	 * 最多取最近50条纪录
	 * @param  datetime $start_time 时间默认查询此时间前一小时的数据
	 * @return bool
	 */
	public function business($start_time) {
		//1 时间范围为前一小时
		$time = strtotime($start_time);
		$start_time = date('Y-m-d H:00:00', $time );
		$end_time  = date('Y-m-d H:00:00', $time + 3600 );

		//2 查询接收,中信接口和通知表
		$remitRate = $this->remitRate($start_time, $end_time);
		$remitReq  = $this->remitReqing($start_time, $end_time);
		$queryMax = $this->queryMax($start_time, $end_time);
		$notifyMax = $this->notifyMax($start_time, $end_time);

		$except = $remitRate ||  $remitReq || $queryMax || $notifyMax;
		if(!$except){
			return false; // 表示无通知,无异常
		}

		//3 各表数据监控情况
		$data =  [
			'remitRate'=> $remitRate, // 失败率
			'remitReqing' 	=> $remitReq['remit'] , // 出款请求中状态的数据
			'queryReqing' 	=> $remitReq['query'] ,// 查询请求中状态的数据
			'queryMax'=> $queryMax ,// 查询接口超限
			'notifyMax' => $notifyMax,//通知次数超限
			'remitStatus'  =>  (new Remit) -> getStatus(),
		];

		//print_r($data);exit;
		//4 发送邮件
		$isMail = $this->sendMail("remitmonitor/business", "业务监控: 异常报告_{$start_time} ~ {$end_time}", $data);
		if (!$isMail) {
			Logger::dayLog('remitmonitor', 'business', "邮件发送失败", $data);
		}

		return true;

	}
	/**
	 * 1 失败率: 以修改时间进行统计
	 */
	public function remitRate($start_time, $end_time) {
		//1 获取修改时间范围内的数据
		$where = [
			'AND',
			['>=', 'modify_time', $start_time],
			['<', 'modify_time', $end_time],
		];
		// 最多分析最近50条
		$remitData = Remit::find()->where($where)->orderBy('modify_time DESC')->limit(50)->all();
		if( !$remitData  ){
			return null;
		}

		//2 分析数据: 连续无响应, 成功率
		$success_num = 0; // 成功数量
		$not_200_num = 0; // 无响应数量
		$except = false;// 是否有异常发生
		foreach ($remitData as $oRemit) {
			if( $oRemit -> rsp_status == 'HTTP_NOT_200' ){
				$not_200_num ++;
			}
			if( $oRemit -> remit_status == Remit::STATUS_SUCCESS){
				$success_num ++;
			}
		}

		//3 成功率计算
		$total = count($remitData);
		$success_rate = $success_num / $total* 100;
		if( $success_rate <= 20 ){
			// 成功率小于20被认为可能有异常发生
			$except = true;
		}
		if( $not_200_num > 0 ){
			// 有一条无响应即被认为有异常
			$except = true;
		}

		//3 返回数据
		if($except){
			return [
				'data' => $remitData,
				'success_rate' => $success_rate,
				'not_200_num' => $not_200_num,
				'success_num' => $success_num,
				'total' => $total,
			];
		}else{
			return null;
		}
	}
	/**
	 * 2 查询一直在请求状态中的数据
	 */
	public function remitReqing($start_time, $end_time) {
		//1 出款接口一直请求中的数据
		$where = [
			'AND',
			['remit_status'=>Remit::STATUS_REQING_REMIT,],
			['>=', 'create_time', $start_time],
			['<', 'create_time', $end_time],
		];
		$remitData = Remit::find()->where($where)->orderBy('create_time DESC')->limit(50)->all();

		//2 查询接口一直请求中的数据
		$where = [
			'AND',
			['remit_status'=>Remit::STATUS_REQING_QUERY],
			['>=', 'query_time', $start_time],
			['<', 'query_time', $end_time],
		];
		$queryData = Remit::find()->where($where)->orderBy('query_time DESC')->limit(50)->all();

		//3 返回结果
		if( !$remitData && !$queryData ){
			return null;
		}
		return $data = [
			'remit' => $remitData,
			'query' => $queryData,
		];
	}
	/**
	 * 3 接口查询次数达上限
	 */
	private function queryMax($start_time, $end_time) {
		//1 查询次数超限
		$where = [
			'AND',
			//['remit_status'=>Remit::STATUS_DOING],
			//['>=', 'query_num', Remit::MAX_QUERY_NUM],
			['remit_status' => Remit::STATUS_QUERY_MAX],
			['>=', 'query_time', $start_time],
			['<', 'query_time', $end_time],
		];

		//2 查询超限的数据
		$data = Remit::find()->where($where)->orderBy('query_time DESC')->limit(50)->all();
		return $data;
	}
	/**
	 * 4 通知客户端达上限监控
	 */
	private function notifyMax($start_time, $end_time) {
		//1 查询次数超限. 并且在1小时内
		$where = [
			'AND',
			['notify_status'=>ClientNotify::STATUS_NOTIFY_MAX],
			// ['notify_status'=>ClientNotify::STATUS_RETRY],
			// ['>=', 'notify_num', ClientNotify::MAX_NOTIFY],
			['>=', 'notify_time', $start_time],
			['<', 'notify_time', $end_time], 
		];

		//2 查询超限的数据
		$data = ClientNotify::find()->where($where)->with('remit')->orderBy('notify_time DESC')->limit(50)->all();
		return $data;
	}
	//************************************end 业务监控***********/


	//************************************start 出款每日超限邮件***********/
	public function daylimit() {
		//1 获取超限应用
		$dayLimits = $this->getUpDayLimit();
		if(!$dayLimits){
			return false; // 表示无超限纪录
		}
		$data = [
			'dayLimits' => $dayLimits,
			'aidNames' => [
				1 => '一亿元',
				2 => 'java',
				3 => '商户贷',
				4 => '花生米富',
			],
		];

		//var_export($data);exit;
		
		//2 发送邮件
		$isMail = $this->sendMail("remitmonitor/daylimit", "出款每日超限", $data);
		if (!$isMail) {
			Logger::dayLog('remitmonitor', 'daymax', "邮件发送失败", $data);
		}

		//3 发送短信接口
		//$smsContent = '系统监控可能存在异常';
		//$isSms = $this->sendSms($smsContent);
		//if (!$isSms) {
		//	Logger::dayLog('remitmonitor', 'daymax', "短信发送失败", $smsContent);
		//}

		return true;
	}
	/**
	 * 获取达到超限的纪录
	 * @return [type] [description]
	 */
	public function getUpDayLimit(){
		//1 获取当前未失败的总额
		$oRemit = new Remit;
		$dayMoneys = $oRemit -> getDayMoneyGroup();
		if( !$dayMoneys ){
			return null;
		}
		// 转换成数组
		$dayMoneys = ArrayHelper::toArray($dayMoneys);

		//2 获取限额表中纪录
		$settings = Setting::find() ->select(['aid','day_max_mount']) -> limit(1000) -> all();
		$settings = ArrayHelper::toArray($settings);
		if( !$settings ){
			return null;
		}

		//3 按aid合并两个数组
		$data = Func::appends($settings,$dayMoneys,'aid');

		//3 比较两个数组是否超限
		$newArr = [];
		foreach ($data as $row) {
			if(!isset($row['day_max_mount'])){
				$row['day_max_mount'] = 0;
			}			
			if(!isset($row['settle_amount'])){
				$row['settle_amount'] = 0;
			}
			// 剩余金额
			$row['diffMoney'] = $row['day_max_mount'] - $row['settle_amount'];

			// <= 剩余金额不足1000时
			$limit_money = $row['aid'] == 4 ? 50000 : 1000;
			$row['isLimit'] = bccomp($row['diffMoney'], $limit_money , 2) !== 1;
			if( $row['isLimit'] ){
				$newArr[] = $row;
			}
		}
		return $newArr;
	}
	//************************************end 出款每日超限邮件***********/


	// 发邮件与发短信
	private function sendMail($template, $title, $data) {
		$mail = Yii::$app->mailer->compose($template, $data);
		$mail->setTo($this->mailers);
		$mail->setSubject($title);
		return $mail->send();
	}
	public function sms() {
		//1 获取修改时间范围内的数据
		$t = time();
		$start_time = date('Y-m-d H:i:s', $t - 4000);
		$end_time = date('Y-m-d H:i:s', $t);
		$where = [
			'AND',
			['>=', 'modify_time', $start_time],
			['<', 'modify_time', $end_time],
			['remit_status'=>[3,12]],
			['rsp_status'=>['ED12002','HTTP_NOT_200']]
		];
		$rows = Remit::find()->select(['rsp_status','count(1)'])->where($where)->groupBy('rsp_status') ->all();
		if(empty($rows)){
			return false; // 表明无异常发生, 系统正常着呢
		}

		// 暂时这样, 以后可能优化成数量
		$map  = \yii\helpers\ArrayHelper::map($rows, 'rsp_status','rsp_status');
		if(isset($map['ED12002'])){
			$login = "yes";
		}else{
			$login = 'no';
		}
		if(isset($map['HTTP_NOT_200'])){
			$timeout = "yes";
		}else{
			$timeout = "no";
		}

		$content = "前置机存在问题:无响应:{$timeout};登录异常:{$login}";
		echo $content;

		foreach($this->phones as $phone){
			\app\common\Http::sendByMobile($phone, $content);
		}

		return true;
	}
}