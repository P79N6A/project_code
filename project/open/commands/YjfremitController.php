<?php

/**
 * 出款计划任务
 * windows d:\xampp\php\php.exe D:\www\open\yii remit []
 */
// #出款
// */5 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii rbremit runRemits 1>/dev/null 2>&1
// #查询
// */5 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii rbremit runQuerys 1>/dev/null 2>&1
// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii rbremit run-notify 1>/dev/null 2>&1

namespace app\commands;

use app\common\Logger;
use app\modules\api\common\yjf\CYjfremit;
use app\modules\api\common\yjf\CNotify;

/**
 * 出款任务相关功能
 */
class YjfremitController extends BaseController {

    /**
     * 出款命令
     * 每五分钟执行一次
     */
    public function runRemits() {
        $oM = new CYjfremit();
        $data = $oM->runRemits();
        Logger::dayLog('yjfcommand', 'runRemits', $data);
        echo  json_encode($data);
    }

     /**
     * 查询
     * 每五分钟执行一次
     */
    public function runQuerys() {
        $oM = new CYjfremit;
        $data = $oM->runQuerys();
        Logger::dayLog('yjfcommand', 'runQuerys', $data);
        return json_encode($data);
    }
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

        $oM = new CNotify();
        $data = $oM->runMinute($start_time, $end_time);
        Logger::dayLog('llcommand', 'runNotify', $data);
        return json_encode($data);
    }

}
