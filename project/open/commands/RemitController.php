<?php
/**
 * 出款计划任务
 * windows d:\xampp\php\php.exe D:\www\open\yii remit []
 */
// #出款
// */5 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii remit runRemits 1>/dev/null 2>&1
// #查询
// */5 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii remit runQuerys 1>/dev/null 2>&1
// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii remit run-notify 1>/dev/null 2>&1

namespace app\commands;
use Yii;
use app\common\Logger;
use app\models\remit\Remit;
use app\modules\api\common\remit\CNotify;
use app\modules\api\common\remit\CRemit;

/**
 * 出款任务相关功能
 */
class RemitController extends BaseController {
	/**
	 * 出款命令
	 * 每五分钟执行一次
	 */
	private function runRemits() {
		$oM = new CRemit;
		$data = $oM->runRemits();
		Logger::dayLog('command', 'remit', $data);
		return json_encode($data);
	}
	/**
	 * 查询
	 * 每五分钟执行一次
	 */
	private function runQuerys() {
		$oM = new CRemit;
		$data = $oM->runQuerys();
		Logger::dayLog('command', 'remit', $data);
		return json_encode($data);
	}
	/**
	 * 通知
	 * 每五分钟执行一次
	 * @param $start_time 默认五分前
	 * @param $end_time 默认当前分钟
	 */
	private function runNotify($start_time = null, $end_time = null) {
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

		$oM = new CNotify;
		$data = $oM->runMinute($start_time, $end_time);
		Logger::dayLog('command', 'remit', $data);
		return json_encode($data);
	}
}