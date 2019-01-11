<?php
// #上标
// */5 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii xnremit runRemits 1>/dev/null 2>&1

namespace app\commands;

use app\common\Logger;
use app\modules\api\common\xn\CxnRemit;  //定时上标
use app\modules\api\common\xn\CxnLoan;   //定时账单查询
use app\modules\api\common\xn\CxnRepay;  //定时还款通知
use app\modules\api\common\xn\CxnAccord; // 定时借款协议
use app\modules\api\common\xn\CxnAgreedown; // 定时下载协议

/**
 * 小诺定时任务相关功能
 */
class XnremitController extends BaseController {

    /**
     * 上标
     * 出款命令
     * 每五分钟执行一次
     */
    public function runRemits($start_time=null , $end_time=null) {      
        $time = time();
        if (!$end_time) {
            $end_time = date('Y-m-d H:i:00');
        }
        if (!$start_time) {
            // 默认1小时内
            $start_time = date('Y-m-d H:i:00', $time - 3 * 24 * 3600);
        }
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
        $end_time = date('Y-m-d H:i:00', strtotime($end_time));
        $oM = new CxnRemit;
        $data = $oM->runRemits($start_time,$end_time);
        Logger::dayLog('xn/command', 'runRemits', $data);
        return json_encode($data);
    }


    /**
     * 拉账单
     * 每五分钟执行一次
     */
    public function runLoan() {
        $oM = new CxnLoan;
        $data = $oM->runQuery();
        Logger::dayLog('xn/command', 'runLoan', $data);
        return json_encode($data);
    }

    /**
     * 还款通知
     * 每五分钟执行一次
     */
    public function runRepay() {
        $oM = new CxnRepay;
        $data = $oM->runRepayment();
        Logger::dayLog('xn/command', 'runRepay', $data);
        return json_encode($data);
    }

    /*
     * 借款协议
     * 每五分钟执行一次
     */
    public function runAccord() {
        $oM = new CxnAccord;
        $data = $oM->runAccord();
        Logger::dayLog('xn/command', 'runAccord', $data);
        return json_encode($data);
    }
    /**
     * 协议下载
     */
    public function runAgreedown(){
        $oM = new CxnAgreedown;
        $data = $oM->runAgreedown();
        Logger::dayLog('xn/command', 'runAgreedown', $data);
        return json_encode($data);
    }
    /**
     * 补充拉取协议   下载链接一小时失效
     */
    public function runAgreefix(){
        $oM = new CxnAgreedown;
        $data = $oM->runAgreefix();
        Logger::dayLog('xn/command', 'runAgreefix', $data);
        return json_encode($data);
    }

}
