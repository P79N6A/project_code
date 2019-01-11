<?php

/**
 * 出款计划任务     新版  2018-8-3  xlj
 */
// #畅捷出款
// */5 * * * * /usr/local/php/bin/php  /data/wwwroot/open/yii cjremit runremits 1>/dev/null 2>&1
// #畅捷查询
// */5 * * * * /usr/local/php/bin/php  /data/wwwroot/open/yii cjremit runquerys 1>/dev/null 2>&1
// #畅捷通知回调
// */5 * * * * /usr/local/php/bin/php  /data/wwwroot/open/yii cjremit runnotify 1>/dev/null 2>&1

//D:/phpStudy/PHPTutorial/php/php-5.6.27-nts/php.exe  D:/workspace/open/yii cjremit runremits
namespace app\commands;

use app\common\Logger;
use app\modules\api\common\cjremit\CNotify;
use app\modules\api\common\cjremit\CjRemit;

/**
 * 出款任务相关功能
 */
class CjremitController extends BaseController {

    /**
     * 出款命令
     * 每五分钟执行一次
     */
    public function runRemits() {
        $oM = new CjRemit;
        $data = $oM->runRemits();
        Logger::dayLog('cjremit/runRemits', 'cjremit', $data);
        return json_encode($data);
    }

    /**
     * 查询
     * 每五分钟执行一次
     */
    public function runQuerys() {
        $oM = new CjRemit;
        $data = $oM->runQuerys();
        Logger::dayLog('cjremit/runQuerys', 'cjremitQuery', $data);
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
        Logger::dayLog('cjremit/runNotify', 'cjremit_notify', $data);
        return json_encode($data);
    }

}
