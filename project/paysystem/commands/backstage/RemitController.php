<?php
/**
 *  系统后台对账 定时脚本
 */
namespace app\commands\backstage;
use app\common\Logger;
use app\commands\BaseController;
use Yii;
use app\modules\backstage\models\AcRemitBill;
use app\modules\backstage\common\CAcRemitBill;
use yii\helpers\ArrayHelper;
class RemitController extends BaseController {
    /**
     * Undocumented function
     * 存管出款债权数据同步
     * @return void
     */
    public function syncRemit($bill_date=null){
        if(empty($bill_date)){
            $bill_date = date('Ymd',strtotime('-2 day'));
        }
        $t1 = date('Y-m-d H:i:s');
        $oM = new CAcRemitBill;
        $data = $oM->syncRemit($bill_date);
        $t2 = date('Y-m-d H:i:s');
        $t = strtotime($t2)-strtotime($t1);
        print_r($data);
        echo '执行时间：'.$t;
        Logger::dayLog('command/syncRemit','开始执行时间',$t1,'结束时间',$t2,'执行时间',$t,'数据',$data);
    }
    /**
     * Undocumented function
     * 出款债权对账
     * @return void
     */
    public function runRemit(){
        $t1 = date('Y-m-d H:i:s');
        $oM = new CAcRemitBill;
        $data = $oM->runRemit();
        $t2 = date('Y-m-d H:i:s');
        $t = strtotime($t2)-strtotime($t1);
        print_r($data);
        echo '执行时间：'.$t;
        Logger::dayLog('command/runRemit','开始执行时间',$t1,'结束时间',$t2,'执行时间',$t,'数据',$data);
    }
}