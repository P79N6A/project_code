<?php
/**
 * 出款监控计划任务
 * 分为两种监控:
 * 1. 系统监控 sys开头
 * 2. 业务监控
 * windows d:\xampp\php\php.exe D:\www\open\yii remit-monitor []
 */
// 1 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii remit-monitor sys 1>/dev/null 2>&1
// 1 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii remit-monitor business 1>/dev/null 2>&1

namespace app\commands;
use Yii;
use app\modules\api\common\remit\CRemitMonitor;
use app\models\remit\Remit;
use yii\helpers\ArrayHelper;
/**
 * 出款监控功能
 */
class RemitMonitorController extends BaseController {
	private $oMonitor;
	public function init(){
		parent::init();
		$this->oMonitor = new CRemitMonitor;
	}
	//******start 系统监控***********/
	public function sys($start_time=null) {
		if (!$start_time) {
			// 默认前一小时
			$start_time = date('Y-m-d H:00:00', time() - 3600);
		}
		return $this->oMonitor->sys($start_time);
	}
	//******end 系统监控***********/

	//******start 业务监控***********/
	public function business($start_time=null) {
		if (!$start_time) {
			// 默认前一小时
			$start_time = date('Y-m-d H:00:00', time() - 3600);
		}
		return $this->oMonitor->business($start_time);
	}
	//******end 业务监控***********/

	//******start 出款每日超限邮件***********/
	public function daylimit() {
		return $this->oMonitor->daylimit();
	}
	//******end 出款每日超限邮件***********/
	//
	//******start 短信监控: 仅监测前置机异常情况***********/
	public function sms() {
		return $this->oMonitor->sms();
	}
	//******end 仅监测前置机异常情况***********/
	
	/**
	 * 延迟因登录异常造成的失败的时间, 以重复出款
	 * @return [type] [description]
	 */
	public function delayRemitTime(){
		$time = date('Y-m-d H:i:s', time() - 86400 );
		$sql = "SELECT rt_remit.* FROM rt_remit WHERE remit_status = 3 AND rsp_status = 'ED12002' AND  create_time >  '{$time}' AND  id IN 
				(
				    SELECT remit_id FROM rt_api_log WHERE pre_status=0 AND rsp_status IN('ED12002','ET10016') AND start_time > '{$time}'
				)";
		$data = $this->getAllBySql($sql);
		$total = is_array($data) ? count($data) : 0;
		echo "总数:{$total}\n";
		if(!$total){
			return false;
		}

		$ids = ArrayHelper::getColumn($data, 'id');
		//print_r($ids);exit;

		$ups = [];
		$ups['remit_status'] = Remit::STATUS_INIT; 
		$ups['modify_time'] = date('Y-m-d H:i:s');
		$ups['create_time'] = date('Y-m-d H:i:s', time() + 3600);
		$ups['query_time'] =  date('Y-m-d H:i:s');
		$ups['query_num'] = 0;

		$where = [
			'id' => $ids,
			'remit_status' => 3,
			'rsp_status' => 'ED12002',
		];

		$success = Remit::updateAll($ups,$where);
		echo "更新数:{$success}\n";

	}
	/**
	 * 根据sql获取全部数据
	 */
	public function getAllBySql($sql) {
		$connection = Yii::$app->db;
		$command = $connection->createCommand($sql);
		return $command->queryAll();
	}
}