<?php
/**
 * 出款监控计划任务
 */
namespace app\modules\api\common\sinapay;
use Yii;
use app\common\Logger;
use app\models\sina\SinaBindbank;
use app\models\sina\SinaBindbankLog;
use app\models\sina\SinaRemit;
use app\models\sina\SinaUser;
use yii\helpers\ArrayHelper;
use app\common\Func;

class SinaMonitor {
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
		//暂与中信用一套机制
		$configPath =  __DIR__ . '/../remit/config/config.php';
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
		$remitRest  = $this->remitRest($start_time, $end_time);

		$except = $remitRate ||  $remitRest;
		if(!$except){
			return false; // 表示无通知,无异常
		}

		//3 各表数据监控情况
		$data =  [
			'remitRate'=> $remitRate, // 失败率
			'remitRest'=> $remitRest, // 余额不足
			'remitStatus'  =>  (new SinaRemit) -> getStatus(),
		];

		//print_r($data);exit;
		//4 发送邮件
		$isMail = $this->sendMail("sinamonitor/business", "新浪出款监控: 异常报告_{$start_time} ~ {$end_time}", $data);
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
			['>=', 'create_time', $start_time],
			['<', 'create_time', $end_time],
		];
		// 最多分析最近100条
		$remitData = SinaRemit::find()->where($where)->orderBy('create_time DESC')->limit(100)->all();
		if( !$remitData  ){
			return null;
		}

		//2 分析数据: 连续无响应, 成功率
		$success_num = 0; // 成功数量
		$except = false;// 是否有异常发生
		foreach ($remitData as $oRemit) {
			if( $oRemit -> remit_status == SinaRemit::STATUS_SUCCESS){
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

		//3 返回数据
		if($except){
			return [
				'data' => $remitData,
				'success_rate' => $success_rate,
				'success_num' => $success_num,
				'total' => $total,
			];
		}else{
			return null;
		}
	}
	/**
	 * 余额不足报告
	 */
	public function remitRest($start_time, $end_time) {
		//1 获取修改时间范围内的数据
		$where = [
			'AND',
			['>=', 'create_time', $start_time],
			['<', 'create_time', $end_time],
			['remit_status'=>SinaRemit::STATUS_FAILURE],
		];
		return SinaRemit::find()->where($where)->count();
	}

	// 发邮件与发短信
	private function sendMail($template, $title, $data) {
		$mail = Yii::$app->mailer->compose($template, $data);
		$mail->setTo($this->mailers);
		$mail->setSubject($title);
		return $mail->send();
	}
	/**
	 * 出款失败:短信监控
	 * @return [type] [description]
	 */
	public function sms() {
		//1 获取修改时间范围内的数据
		$t = time();
		$start_time = date('Y-m-d H:i:s', $t - 3600);
		$end_time = date('Y-m-d H:i:s', $t);
		$total =  $this-> remitRest($start_time, $end_time);
		if(!$total){
			return false;
		}
		$content = "sina支付存在异常:出款失败{$total}条";
		echo $content;
		$this->sendSms($content);
		return true;
	}
	/**
	 * 余额不足时提醒
	 * @return [type] [description]
	 */
	public function warnMoney(){
		$sinapay = new Sinapay();
		$res = $sinapay->query_middle_account('1001');
		if(!$res){
			return true;
		}
		$arr = explode('^', $res);
		$restMoney = $arr[2];
		if($restMoney >= 200000){
			return true;
		}

		// 进行提醒
		$content = "sina支付存在异常:余额不足{$restMoney}元";
		echo $content;
		$this->sendSms($content);
		return true;
	}
	/**
	 * 发送短信
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	private function sendSms($content){
		foreach($this->phones as $phone){
			\app\common\Http::sendByMobile($phone, $content);
		}
	}
}