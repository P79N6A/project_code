<?php

/**
 * 融宝信用卡计划任务
 */
namespace app\commands;

use app\common\Logger;
use app\modules\api\common\rbcredit\CNotify;
use app\modules\api\common\rbcredit\RbcreditRemit;

/**
 * 出款任务相关功能
 */
class RbcreditRemitController extends BaseController {

    /**
     * 出款命令
     * 每五分钟执行一次
     */
    public function runRemits() {
        $oM = new RbcreditRemit;
        $data = $oM->runRemits();
        Logger::dayLog('command', 'rbcreditRemit', $data);
        return json_encode($data);
    }

    /**
     * 查询
     * 每五分钟执行一次
     */
    public function runQuerys() {
        $oM = new RbcreditRemit;
        $data = $oM->runQuerys();
        Logger::dayLog('command', 'rbcreditQuery', $data);
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
        Logger::dayLog('rbcredit', 'rbcreditnotify', $data);
        return json_encode($data);
    }

}
