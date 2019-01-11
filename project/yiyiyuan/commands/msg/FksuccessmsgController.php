<?php
namespace app\commands\msg;

use app\models\news\Cg_remit;
use app\models\news\WarnMessageList;
use yii\console\Controller;
use yii\helpers\ArrayHelper;


/**
 * 放款成功，存储数据到yi_warn_message_list表  WarnmessageController.php  定时任务
 */

//避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class FksuccessmsgController extends Controller {

    // 命令行入口文件
    public function actionIndex($time_type='') {
        //出款五分钟
        $limit = 200;
        $type = 5; //放款成功
        if(empty($time_type)){
            $time_type = 1;
        }
        if($time_type == 1){
            $time_start = date('Y-m-d H:i:00',  strtotime('-5 minutes'));
            $time_end = date('Y-m-d H:i:00', time());
        }else{
            $start_time = strtotime('-12 hours')-(5*60);
            $time_start = date("Y-m-d H:i:00",  $start_time);
            $time_end = date("Y-m-d H:i:00", strtotime('-12 hours'));
        }
        
        $where = [
            "AND",
            ["BETWEEN", Cg_remit::tableName() . ".remit_time", $time_start,$time_end],
        ];    
        $cgremit_list = Cg_remit::find()->where(['remit_status'=>'WILLREMIT'])->andWhere($where);
        $total = $cgremit_list->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $cgremit_info = $cgremit_list->offset($i*$limit)->limit($limit)->all();
            if (!empty($cgremit_info)) {  
                $warnmsg_model = new WarnMessageList;
                $res =  $warnmsg_model->todo($cgremit_info,$time_type,$type);
                $this->log("\n all:{$limit},SUCCESS:{$res},pages:{$i}\n");
            }
        }
        //Logger::dayLog('Warnmessage',$limit,$pages);
    }
    
    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
