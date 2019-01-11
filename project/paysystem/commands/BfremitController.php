<?php

/**
 * 出款计划任务
 * windows d:\xampp\php\php.exe D:\www\open\yii remit []
 */
// #出款
// */5 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii remit runRemits 1>/dev/null 2>&1
// #查询
// */5 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii remit runQuerys 1>/dev/null 2>&1
// #通知回调
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii remit run-notify 1>/dev/null 2>&1

namespace app\commands;

use app\common\Logger;
use app\modules\api\common\baofoo\CBfAcc;
use app\modules\api\common\baofoo\CBfBill;

/**
 * 出款任务相关功能
 */
class BfremitController extends BaseController {

    /**
     * 转账命令
     * 每五分钟执行一次
     */
    public function runPayment() {
        $oM = new CBfAcc;
        $data = $oM->runPayment();
        Logger::dayLog('command', 'bfpayment', $data);
        return json_encode($data);
    }

    /**
     * 单笔支付转账查询
     * 每五分钟执行一次
     */
    public function runPayquery() {
        $oM = new CBfAcc;
        $data = $oM->runPayquery();
        Logger::dayLog('command', 'bfpayquery', $data);
        return json_encode($data);
    }
    /**
     * 转账账单下载
     * 每五分钟执行一次
     */
    public function runPaybill($bill_date=null) {
        if(empty($bill_date)){
            $bill_date = date('Y-m-d',strtotime('-1 day'));
        }
        $oM = new CBfBill;
        $data = $oM->runPaybill($bill_date);
        Logger::dayLog('command', 'runPaybill', $data);
        return json_encode($data);
    }

}
