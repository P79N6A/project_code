<?php
/**
 *  定时
 */
namespace app\commands;
use app\common\Logger;
use app\modules\api\common\baofoo\BaofooClient;
use app\modules\api\common\baofoo\CBaofooAuth;
use app\modules\api\common\baofoo\CBfXY;
use Yii;

// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/paysystem/yii  bf-query authpayquery 1>/dev/null 2>&1
class BfQueryController extends BaseController {
    /**
     * @desc 宝付代扣 支付异常查询
     * @param $start_time
     * @param $end_time
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
        $oM = new BaofooClient;
        $data = $oM->runMinute($start_time, $end_time);
        Logger::dayLog('command', 'remit', $data);
        return json_encode($data);
    }
    /**
     * @desc 宝付认证绑卡异常查询
     *
    */
    // public function bindingQuery($start_time = null, $end_time = null){
    //     $time = time();
    //     if (!$end_time) {
    //         $end_time = date('Y-m-d H:i:00',$time - 60);
    //     }
    //     if (!$start_time) {
    //         // 默认10分钟
    //         $start_time = date('Y-m-d H:i:00', $time -11*60);
    //     }
    //     $start_time = date('Y-m-d H:i:00', strtotime($start_time));
    //     $end_time = date('Y-m-d H:i:00', strtotime($end_time));

    //     $oM = new CBaofooAuth;
    //     $data = $oM->runBindingMinute($start_time, $end_time);
    //     Logger::dayLog('command', 'bindingQuery', $data);
    //     return json_encode($data);
    // }

    /**
     * @desc 宝付认证 支付异常查询
     *
    */
    public function authpayQuery($start_time = null, $end_time = null){
        $time = time();
        if (!$end_time) {
            $end_time = date('Y-m-d H:i:00',$time -60*60);
        }
        if (!$start_time) {
            // 默认10分钟
            $start_time = date('Y-m-d H:i:00', $time - 16*60);
        }
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $bfauth = new CBaofooAuth;
        $data = $bfauth->authPayQueryMinute($start_time, $end_time);
        Logger::dayLog('command', 'authpayQuery', $data);
        return json_encode($data);
    }


    /**
     * @desc 处理 状态为3、8的订单
     *
    */
    public function authProcess($start_time = null, $end_time = null){
        $time = time();
        if (!$end_time) {
            $end_time = date('Y-m-d H:i:00',$time -10*60*60);
        }
        if (!$start_time) {
            $start_time = date('Y-m-d H:i:00', $time - 20*60*60);
        }
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $bfauth = new CBaofooAuth;
        $data = $bfauth->authPayQueryProcess($start_time, $end_time);
        Logger::dayLog('command', 'authpayQuery', $data);
        return json_encode($data);
    }

    /**
     * 协议支付,补单操作
     */
    public function xypayQuery($start_time = null, $end_time = null){
        $time = time();
        $start_time = $start_time?strtotime($start_time):($time - 60*60*24*7);
        $end_time = $end_time?strtotime($end_time):($time - 1800);
        $start_time = date('Y-m-d H:i:00',$start_time);
        $end_time = date('Y-m-d H:i:00',$end_time);
        $data = (new CBfXY())->xyPayQuery($start_time, $end_time);
        Logger::dayLog('command', 'xypayQuery', $data);
        return json_encode($data);
    }
}