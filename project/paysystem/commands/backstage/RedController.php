<?php
namespace app\commands\backstage;

use app\common\Logger;
use app\modules\backstage\models\BillRepay;
use app\modules\backstage\common\CRed;
use yii\helpers\ArrayHelper;
use app\common\Pcntl;
/**
 * 债券贴息对账
 */
class RedController extends \app\commands\BaseController
{
    /**
     * Undocumented function
     * 米富贴息数据同步
     * @return void
     */
    public function syncRed($bill_date=null){
        if(empty($bill_date)){
            $bill_date = date('Ymd',strtotime('-2 day'));
        }
        $t1 = date('Y-m-d H:i:s');
        $oM = new CRed;
        $data = $oM->syncRed($bill_date);
        $t2 = date('Y-m-d H:i:s');
        $t = strtotime($t2)-strtotime($t1);
        echo 'time: '.$t;
        Logger::dayLog('command/syncRed','开始执行时间',$t1,'结束时间',$t2,'执行时间',$t,'数据',$data);
    }
    /**
     * Undocumented function
     * 米富贴息对账
     * @return void
     */
    public function runRecharge(){
        $t1 = date('Y-m-d H:i:s');
        $oM = new CRed;
        $data = $oM->runRecharge();
        $t2 = date('Y-m-d H:i:s');
        $t = strtotime($t2)-strtotime($t1);
        print_r($data);
        echo '执行时间：'.$t;
        Logger::dayLog('command/runRecharge','开始执行时间',$t1,'结束时间',$t2,'执行时间',$t,'数据',$data);
    }

        
    

   
}
