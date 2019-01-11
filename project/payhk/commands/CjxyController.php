<?php
/**
 *  定时
 */
namespace app\commands;
use app\common\Logger;
use app\modules\api\common\cjxy\CCjxy;
use Yii;

// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/paysystem/yii  cjxy runQuery 1>/dev/null 2>&1
class CjxyController extends BaseController {
    /**
     * @desc 畅捷协议支付 支付异常查询
     * @param $start_time
     * @param $end_time
     */
    public function runQuery($start_time = null, $end_time = null) {
        $time = time();
        if (!$end_time) {
            $end_time = date('Y-m-d H:i:00',$time - 1800);
        }
        if (!$start_time) {
            // 默认1小时内
            $start_time = date('Y-m-d H:i:00', $time - 3600*24*7);
        }
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
		Logger::dayLog('cjxy', 'command/runQuery', $start_time.' --- '.$end_time.'畅捷协议支付补单操作开始');
        $oM = new CCjxy;
        $data = $oM->runQuery($start_time, $end_time);
        Logger::dayLog('cjxy', 'command/runQuery', '畅捷协议支付补单操作结束,成功条数'.$data);
        return json_encode($data);
    }
}