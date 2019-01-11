<?php

/**
 * 出款计划任务
 */
// #畅捷出款
// */5 * * * * /usr/local/php/bin/php  /data/wwwroot/open/yii bfremit runremits 1>/dev/null 2>&1
// #畅捷查询
// */5 * * * * /usr/local/php/bin/php  /data/wwwroot/open/yii bfremit runquerys 1>/dev/null 2>&1
// #畅捷通知回调
// */5 * * * * /usr/local/php/bin/php  /data/wwwroot/open/yii bfremit runnotify 1>/dev/null 2>&1
namespace app\commands;

use app\common\Logger;
use app\modules\api\common\changjie\CNotify;
use app\modules\api\common\changjie\CjRemit;

/**
 * 出款任务相关功能
 */
class CjtremitController extends BaseController {

    /**
     * 出款命令
     * 每五分钟执行一次
     */
    public function runRemits() {
        $oM = new CjRemit;
        $data = $oM->runRemits();
        Logger::dayLog('command', 'cjtremit', $data);
        return json_encode($data);
    }

    /**
     * 查询
     * 每五分钟执行一次
     */
    public function runQuerys() {
        $oM = new CjRemit;
        $data = $oM->runQuerys();
        Logger::dayLog('command', 'cjtremiQuery', $data);
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
        Logger::dayLog('baofoo', 'baofoo_notify', $data);
        return json_encode($data);
    }

}
