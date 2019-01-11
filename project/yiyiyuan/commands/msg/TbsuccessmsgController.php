<?php
namespace app\commands\msg;

use app\models\news\User_loan_extend;
use app\models\news\WarnMessageList;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * 借款审核通过,未购卡
 */

//避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class TbsuccessmsgController extends Controller {

    // 命令行入口文件
    public function actionIndex($time_type) {
        if(empty($time_type)){
            return false;
        }
        if($time_type == 2){
            $time = 3;
        }elseif ($time_type == 3){
            $time = 8;
        }elseif ($time_type == 4){
            $time = 20;
        }
        $limit = 500;
        $time_end   = date("Y-m-d H:i:00", strtotime("-".$time." hours"));
        $time_start = date("Y-m-d H:i:00", strtotime("-".$time." hours -10 minutes"));

        $where = [
            'AND',
            ['BETWEEN', User_loan_extend::tableName() . '.last_modify_time', $time_start,$time_end],
            [User_loan_extend::tableName() . ".status" => 'TB-SUCCESS'],
        ];
        $loanExtend = User_loan_extend::find()->where($where);
        $total = $loanExtend->count();
        $pages = ceil($total / $limit);

        $this->log("\n". date('Y-m-d H:i:s') . "......................");
        $this->log("\n all:{$total},limit:{$limit},pages:{$pages}\n");

        for ($i = 0; $i < $pages; $i++) {
            $loanExtendInfo = $loanExtend->offset($i*$limit)->limit($limit)->all();
            if (!empty($loanExtendInfo)) {
                $warnMessageModel = new WarnMessageList;
                $result =  $warnMessageModel->todo($loanExtendInfo,$time_type,3);
                $this->log("\n all:{$limit},SUCCESS:{$result},pages:{$i}\n");
            }
        }
    }
    
    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
