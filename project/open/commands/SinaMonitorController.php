<?php
/**
 * 出款监控计划任务
 * 分为两种监控:
 * 1. 系统监控 sys开头
 * 2. 业务监控
 * windows d:\xampp\php\php.exe D:\www\open\yii sina-monitor []
 */
// 1 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii sina-monitor sms 1>/dev/null 2>&1
// 1 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii sina-monitor business 1>/dev/null 2>&1

namespace app\commands;
use Yii;
use app\modules\api\common\sinapay\SinaMonitor;

/**
 * 出款监控功能
 */
class SinaMonitorController extends BaseController {
	private $oMonitor;
	public function init(){
		parent::init();
		$this->oMonitor = new SinaMonitor;
	}

	//******start 业务监控***********/
	public function business($start_time=null) {
		if (!$start_time) {
			// 默认前一小时
			$start_time = date('Y-m-d H:00:00', time() - 3600);
		}
		return $this->oMonitor->business($start_time);
	}
	//******end 业务监控***********/

	//******start 短信监控: 仅监测前置机异常情况***********/
	public function sms() {
		return $this->oMonitor->sms();
	}
	public function warnMoney(){
		return $this->oMonitor->warnMoney();
	}
	//******end 仅监测前置机异常情况***********/
}