<?php
namespace app\commands\backstage;

use app\common\Logger;
use app\modules\backstage\models\BillRepay;
use app\modules\backstage\common\CRepay;
use yii\helpers\ArrayHelper;
use app\common\Pcntl;
/**
 * 清结算入口
 */
class RepayController extends \app\commands\BaseController
{
    /**
     * Undocumented function
     * 债匹回款数据同步
     * @return void
     */
    public function syncRepay($bill_date=null){
        if(empty($bill_date)){
            $bill_date = date('Ymd',strtotime('-2 day'));
        }
        $t1 = date('Y-m-d H:i:s');
        $oM = new CRepay;
        $data = $oM->syncRepay($bill_date);
        $t2 = date('Y-m-d H:i:s');
        $t = strtotime($t2)-strtotime($t1);
        echo 'time: '.$t;
        Logger::dayLog('command/repay','开始执行时间',$t1,'结束时间',$t2,'执行时间',$t,'数据',$data);
    }
/**
     * Undocumented function
     * 回款对账
     * @return void
     */
    public function runRecharge(){
        $t1 = date('Y-m-d H:i:s');
        $oM = new CRepay;
        $data = $oM->runRecharge();
        $t2 = date('Y-m-d H:i:s');
        $t = strtotime($t2)-strtotime($t1);
        echo '执行时间：'.$t;
        Logger::dayLog('command/runRecharge','开始执行时间',$t1,'结束时间',$t2,'执行时间',$t,'数据',$data);
    }

        
    

   
}
