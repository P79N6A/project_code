<?php
/**
 *  系统后台对账 定时脚本 资金对账
 */
namespace app\commands\backstage;
use app\common\Logger;
use app\commands\BaseController;
use Yii;
use app\modules\backstage\common\CAcFundBill;
class FundController extends BaseController {
    /**
     * Undocumented function
     * 存管充值数据同步
     * @return void
     */
    public function syncRecharge($bill_date=null){
        if(empty($bill_date)){
            $bill_date = date('Ymd',strtotime('-2 day'));
        }
        $t1 = date('Y-m-d H:i:s');
        $oM = new CAcFundBill;
        $data = $oM->syncRecharge($bill_date);
        $t2 = date('Y-m-d H:i:s');
        $t = strtotime($t2)-strtotime($t1);
        print_r($data);
        echo '执行时间：'.$t;
        Logger::dayLog('command/syncRecharge','开始执行时间',$t1,'结束时间',$t2,'执行时间',$t,'数据',$data);
    }
    /**
     * Undocumented function
     * 存管提现数据同步
     * @param [type] $bill_date
     * @return void
     */
    public function syncWithdraw($bill_date=null){
        if(empty($bill_date)){
            $bill_date = date('Ymd',strtotime('-2 day'));
        }
        $t1 = date('Y-m-d H:i:s');
        $oM = new CAcFundBill;
        $data = $oM->syncWithdraw($bill_date);
        $t2 = date('Y-m-d H:i:s');
        $t = strtotime($t2)-strtotime($t1);
        print_r($data);
        echo '执行时间：'.$t;
        Logger::dayLog('command/syncWithdraw','开始执行时间',$t1,'结束时间',$t2,'执行时间',$t,'数据',$data);
    }
    /**
     * Undocumented function
     * 存管免密提现数据同步
     * @param [type] $bill_date
     * @return void
     */
    public function syncAgreeWithdraw($bill_date=null){
        if(empty($bill_date)){
            $bill_date = date('Ymd',strtotime('-2 day'));
        }
        $t1 = date('Y-m-d H:i:s');
        $oM = new CAcFundBill;
        $data = $oM->syncAgreeWithdraw($bill_date);
        $t2 = date('Y-m-d H:i:s');
        $t = strtotime($t2)-strtotime($t1);
        print_r($data);
        echo '执行时间：'.$t;
        Logger::dayLog('command/syncAgreeWithdraw','开始执行时间',$t1,'结束时间',$t2,'执行时间',$t,'数据',$data);
    }
    /**
     * Undocumented function
     * 充值对账
     * @return void
     */
    public function runRecharge(){
        $t1 = date('Y-m-d H:i:s');
        $oM = new CAcFundBill;
        $data = $oM->runRecharge();
        $t2 = date('Y-m-d H:i:s');
        $t = strtotime($t2)-strtotime($t1);
        print_r($data);
        echo '执行时间：'.$t;
        Logger::dayLog('command/runRecharge','开始执行时间',$t1,'结束时间',$t2,'执行时间',$t,'数据',$data);
    }
    /**
     * Undocumented function
     * 提现对账
     * @return void
     */
    public function runWithdraw(){
        $t1 = date('Y-m-d H:i:s');
        $oM = new CAcFundBill;
        $data = $oM->runWithdraw();
        $t2 = date('Y-m-d H:i:s');
        $t = strtotime($t2)-strtotime($t1);
        print_r($data);
        echo '执行时间：'.$t;
        Logger::dayLog('command/runWithdraw','开始执行时间',$t1,'结束时间',$t2,'执行时间',$t,'数据',$data);
    }
}