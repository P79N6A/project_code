<?php
// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii remit run-notify 1>/dev/null 2>&1
namespace app\commands;
use app\modules\api\common\chanpay\ChanpayNotify;
use app\common\Logger;


class ChanpaynotifyController extends BaseController {
	
	/**
	 * 通知
	 * 每五分钟执行一次
	 * @param $start_time 默认五分前
	 * @param $end_time 默认当前分钟
	 */
	public function runNotify($start_time = null, $end_time = null) {
		$time = time();
		if (!$end_time) {
			$end_time = date('Y-m-d H:i:00');
		}
		if (!$start_time) {
			// 默认1小时内
			$start_time = date('Y-m-d H:i:00', $time - 3600 );
		}
		$start_time = date('Y-m-d H:i:00', strtotime($start_time));
		$end_time = date('Y-m-d H:i:00', strtotime($end_time));
	
		$oM = new ChanpayNotify;
		$data = $oM->runMinute($start_time, $end_time);
		Logger::dayLog('command', 'chanpayremit', $data);
		return json_encode($data);
	}
}