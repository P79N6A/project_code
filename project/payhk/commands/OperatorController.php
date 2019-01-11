<?php

/**
 * 运营商通知任务
 * windows d:\xampp\php\php.exe D:\www\open\yii operator runnotify
 */

// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii operator runnotify 1>/dev/null 2>&1

namespace app\commands;

use app\common\Logger;
use app\modules\api\common\yidun\OpNotify;
//use app\modules\api\common\llpay\CLLremit;
//use app\modules\api\common\llpay\CNotify;

/**
 * 运营商通知任务
 */
class OperatorController extends BaseController {


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
            $start_time = date('Y-m-d H:i:00', $time - 3600);
        }
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $oM = new OpNotify();
        $data = $oM->runMinute($start_time, $end_time);
        Logger::dayLog('operator', 'runNotify', $data.'|'.$start_time.'|'.$end_time);
        return json_encode($data);
    }

}
