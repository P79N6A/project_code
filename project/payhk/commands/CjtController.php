<?php
/**
 *  定时
 */
namespace app\commands;
use app\common\Logger;
use app\modules\api\common\cjt\CCjt;
use Yii;

// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/paysystem/yii  cjt runQuery 1>/dev/null 2>&1
class CjtController extends BaseController {
    /**
     * @desc 畅捷通代扣 支付异常查询
     * @param $start_time 
     * @param $end_time 
     */
    public function runQuery($start_time = null, $end_time = null) {
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
        $oM = new CCjt;
        $data = $oM->runQuery($start_time, $end_time);
        Logger::dayLog('command', 'cjt/runQuery', $data);
        return json_encode($data);
    }
}