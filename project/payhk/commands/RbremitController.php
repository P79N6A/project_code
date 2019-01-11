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
use app\modules\api\common\rbremit\CNotify;
use app\modules\api\common\rbremit\Rbremit;

/**
 * 出款任务相关功能
 */
class RbremitController extends BaseController {

    /**
     * 出款命令
     * 每五分钟执行一次
     */
    public function runRemits() {
        $oM = new Rbremit;
        $data = $oM->runRemits();
        Logger::dayLog('command', 'rbremit', $data);
        return json_encode($data);
    }

    /**
     * 单笔查询
     * 每五分钟执行一次
     */
    public function runQuerys() {
        $oM = new Rbremit;
        $data = $oM->runQuerys();
        Logger::dayLog('command', 'remit_querys', $data);
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
        Logger::dayLog('rongbao', 'rongbao_notify', $data);
        return json_encode($data);
    }
    /**
     * Undocumented function
     * 融宝数据处理
     * @return void
     */
    public function runErrorQuery(){
        $oM = new Rbremit;
        $data = $oM->runErrorQuerys();
        Logger::dayLog('command', 'remit', $data);
        return json_encode($data);
    }
}
