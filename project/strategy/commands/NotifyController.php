<?php
// #反欺诈通知回调
//	D:\phpstudy\php55\php.exe D:\phpstudy\WWW\strategy_new\yii notify runNotify
namespace app\commands;
use app\modules\api\common\Notify;
use app\common\Logger;

use Yii;


class NotifyController extends BaseController {
	
	/**
	 * 通知
	 * 每五分钟执行一次
	 * @param $start_time 默认五分前
	 * @param $end_time 默认当前分钟
	 */
	public function runNotify($start_time = null, $end_time = null, $from = null) {
		$time = time();
		if (!$end_time) {
			$end_time = date('Y-m-d H:i:00');
		}
		if (!$start_time) {
			// 默认1小时内
			$start_time = date('Y-m-d H:i:00', $time - 3600 );
		}
		// if(!$from){
		// 	//反欺诈类型码
		// 	$from = Yii::$app->params['from']['STRATEGY_ANTIFRAUD'];
		// }
		$start_time = date('Y-m-d H:i:00', strtotime($start_time));
		$end_time = date('Y-m-d H:i:00', strtotime($end_time));
		
		$oA = new Notify;
		$data = $oA->runMinute($start_time, $end_time);
		Logger::dayLog('command', 'notify', $data);
		return json_encode($data);
	}

	public function rundHappyNotify($start_time = null, $end_time = null, $from = null)
    {
        $time = time();
        if (!$end_time) {
            $end_time = date('Y-m-d H:i:00');
        }
        if (!$start_time) {
            // 默认1小时内
            $start_time = date('Y-m-d H:i:00', $time - 3600 );
        }
        // if(!$from){
        // 	//反欺诈类型码
        // 	$from = Yii::$app->params['from']['STRATEGY_ANTIFRAUD'];
        // }
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));

        $oA = new Notify;
        $data = $oA->rundHappyMinute($start_time, $end_time);
        Logger::dayLog('command', 'notify', $data);
        return json_encode($data);
    }
}