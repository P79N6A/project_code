<?php
/**
 *  定时
 */
namespace app\commands;
use app\common\Logger;
use app\modules\api\common\jd\CJdquick;
use Yii;


// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/paysystem/yii  jd-query runNotify 1>/dev/null 2>&1
class JdQueryController extends BaseController {
    /**
     * @desc 京东快捷支付 支付异常查询
     * @param $start_time
     * @param $end_time
     */
    public function runNotify($start_time = null, $end_time = null) {
        $time = time();
        if (!$end_time) {
            $end_time = date('Y-m-d H:i:s',$time - 1800);
        }
        if (!$start_time) {
            // 30分钟之前的 7天
            $start_time = date('Y-m-d H:i:00', $time - 606600);
        }
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $oM = new CJdquick();
        $data = $oM->runMinute($start_time, $end_time);
        Logger::dayLog('command', 'remit', $data);
        return json_encode($data);
    }

}