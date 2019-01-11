<?php
/**
 *  定时
 */
namespace app\commands;
use app\common\Logger;
use app\modules\api\common\yeepay\CYeepaytzt;
use Yii;

// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/paysystem/yii  yeepay runQuery 1>/dev/null 2>&1
class YeepayController extends BaseController {
    /**
     * @desc 易宝投资通支付 支付异常查询
     * @param $start_time 
     * @param $end_time 
     */
    public function runQuery($start_time = null, $end_time = null) {
        $time = time();
        if (!$start_time) {
            // 7天
            $start_time = date('Y-m-d H:i:00', $time - 86400*7);
        }
        if (!$end_time) {
            //3h
            $end_time = date('Y-m-d H:i:00',$time-3600*3);
        }
        
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $oM = new CYeepaytzt;
        $data = $oM->runQuery($start_time, $end_time);
        Logger::dayLog('command', 'yeepay/runQuery', $data);
        return json_encode($data);
    }
}