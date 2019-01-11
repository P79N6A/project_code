<?php
/**
 * 出款计划任务
 * windows d:\xampp\php\php.exe D:\www\open\yii notify []
 */
// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii policynotify runNotify 1>/dev/null 2>&1
// 本地测试 ：D:\wamp\bin\php\php5.5.12\php.exe D:\wamp\www\paysystem\yii remit runNotify
namespace app\commands;
use app\common\Logger;
use app\modules\api\common\CPolicyNotify;
use Yii;

/**
 * 保单通知
 */
class PolicynotifyController extends BaseController {
    /**
     * 通知
     * 每五分钟执行一次
     */
    public function runNotify() {
        $oM = new CPolicyNotify;
        $data = $oM->runMinute();
        Logger::dayLog('command', 'policynotify', $data);
        return json_encode($data);
    }
}