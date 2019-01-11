<?php
/**
 * 创蓝校验手机空号检测
 * 定时任务模块
 * @author 孙瑞
 * @crontab采集地址 ==> *\/5 7-22 * * * /usr/local/php-5.4.40/bin/php /data/wwwroot/open/yii mbcheck runQuerys 1>/dev/null 2>&1
 * @crontab通知地址 ==> *\/5 7-22 * * * /usr/local/php-5.4.40/bin/php /data/wwwroot/open/yii mbcheck runNotify 1>/dev/null 2>&1
 */
namespace app\commands;

use app\modules\api\common\mbcheck\MbcheckService;
use app\modules\api\common\mbcheck\MbcheckNotify;

class MbcheckController extends BaseController {

	/**
	 * 创蓝校验手机空号检测定时采集任务
	 */
	public function runQuerys() {
		$time = time();
        $start_time = $start_time?strtotime($start_time):($time - 86400);
        $end_time = $end_time?strtotime($end_time):($time - 300);
        $start_time = date('Y-m-d H:i:00',$start_time);
        $end_time = date('Y-m-d H:i:00',$end_time);
        $data = (new MbcheckService())->runAll($start_time, $end_time);
        return $data;
	}

	/**
	 * 创蓝校验手机空号检测定时通知任务
	 */
	public function runNotify() {
		$time = time();
        $start_time = $start_time?strtotime($start_time):($time - 86400);
        $end_time = $end_time?strtotime($end_time):($time - 300);
        $start_time = date('Y-m-d H:i:00',$start_time);
        $end_time = date('Y-m-d H:i:00',$end_time);
        $data = (new MbcheckNotify())->runAll($start_time, $end_time);
        return $data;
	}
}