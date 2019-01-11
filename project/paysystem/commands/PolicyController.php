<?php
// #保险
// */5 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii policy runRemits 1>/dev/null 2>&1

namespace app\commands;

use app\common\Logger;
use app\modules\api\common\policy\CPolicyBatch;
use app\modules\api\common\policy\CPolicyPay;
use app\modules\api\common\policy\CPolicyApply;
use app\modules\api\common\policy\CPolicyCancel;
use app\modules\api\common\policy\CPolicyBill;

/**
 * 众安保险定时任务相关功能
 */
class PolicyController extends BaseController {

    /**
     * 
     * 每五分钟执行一次
     */
    public function runPolicy() {      
        $oM = new CPolicyBatch;
        $data = $oM->runPolicy();
        Logger::dayLog('policy/command', 'runPolicy', $data);
        return json_encode($data);
    }


    /**
     * 保存支付订单
     * 每五分钟执行一次
     */
    public function runPay() {
        $oM = new CPolicyPay;
        $data = $oM->runPay();
        Logger::dayLog('policy/command', 'runPay', $data);
        return json_encode($data);
    }
   
    /**
     * 出单
     * 每五分钟执行一次
     */
    public function runApply() {
        $oM = new CPolicyApply;
        $data = $oM->runApply();
        Logger::dayLog('policy/command', 'runApply', $data);
        return json_encode($data);
    }
    /**
     * 退单
     * 每五分钟执行一次
     */
    public function runCancel() {
        $oM = new CPolicyCancel;
        $data = $oM->runCancel();
        Logger::dayLog('policy/command', 'runCancel', $data);
        return json_encode($data);
    }
    /**
     * Undocumented function
     * T-2
     * @param [type] $bill_date
     * @return void
     */
    public function runBill($bill_date=null){
        if(empty($bill_date)){
            $bill_date = date('Y-m-d',strtotime('-2 day',time()));
        }
        $oM = new CPolicyBill;
        $data = $oM->runBill($bill_date);
        Logger::dayLog('policy/command', 'runBill', $data);
        return json_encode($data);
    }
    /**
     * Undocumented function
     * 处理未支付无效订单请求
     * @return void
     */
    public function runPayQuery(){
        $oM = new CPolicyPay;
        $data = $oM->runPayQuery();
        Logger::dayLog('policy/command', 'runPayQuery', $data);
        return json_encode($data);
    }
}
