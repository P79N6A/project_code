<?php
/**
 *  定时查询
 */

namespace app\commands;
use app\common\Logger;
use app\modules\api\common\lian\CAuthlian;
use Yii;

class LianpayController extends BaseController {
    /**
     * @desc 连连认证支付 支付异常查询
     * @param $start_time 
     * @param $end_time 
     */
    public function runException($start_time = null, $end_time = null) {
        $time = time();
        if (!$end_time) {
            $end_time = date('Y-m-d H:i:00',$time - 3600);
        }
        if (!$start_time) {
            $start_time = date('Y-m-d H:i:00', $time - 7200);
        }
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $oM = new CAuthlian;
        $data = $oM->runException($start_time, $end_time);
        Logger::dayLog('command', 'remit', $data);
        return json_encode($data);
    }

}