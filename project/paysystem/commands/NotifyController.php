<?php
/**
 * 出款计划任务
 * windows d:\xampp\php\php.exe D:\www\open\yii notify []
 */
// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii notify run-notify 1>/dev/null 2>&1
// 本地测试 ：D:\wamp\bin\php\php5.5.12\php.exe D:\wamp\www\paysystem\yii remit runNotify
namespace app\commands;
use app\common\Logger;
use app\modules\api\common\CNotify;
use Yii;

/**
 * 通知
 */
class NotifyController extends BaseController {
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

        $oM = new CNotify;
        $data = $oM->runMinute($start_time, $end_time);
        Logger::dayLog('command', 'remit', $data);
        return json_encode($data);
    }
    /**
     * Undocumented function
     * 查询锁定中的数据
     * @return void
     */
    public function runLockNotify(){
        $oM = new CNotify;
        $data = $oM->runLockNotify();
        Logger::dayLog('command', 'runLockNotify', $data);
        return json_encode($data);
    }
}