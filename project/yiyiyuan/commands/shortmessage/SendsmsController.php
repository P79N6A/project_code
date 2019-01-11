<?php

namespace app\commands\shortmessage;

/**
 *
 *  linux : sudo -u www /data/wwwroot/yiyiyuan/yii pushoverwithdrawals
 *  windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuana_app\yii pushoverwithdrawals
 */

use app\commonapi\ApiSms;
use app\commonapi\Logger;
use app\models\news\Cg_remit;
use app\models\news\User_loan;
use Yii;
use yii\console\Controller;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SendsmsController extends Controller {

    private $limit = 200;
    public function actionIndex() {
        $begintime=date('Y-m-d 00:00:00', strtotime('-7 day'));
        $endtime=date('Y-m-d H:i:s', strtotime('-36 hour'));
        $where = [
            'AND',
            [Cg_remit::tableName() . '.remit_status' => 'WAITREMIT'],
            [User_loan::tableName() . '.status' => 9],
            ['>',Cg_remit::tableName() . '.create_time',$begintime],
            ['>',Cg_remit::tableName() . '.last_modify_time',$endtime],
        ];
        $sql = Cg_remit::find()->joinWith('userloan',true,'LEFT JOIN')->where($where);
        $total = $sql->count();
        $succ = 0;
        $error = 0;
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $loanList = $sql->limit($this->limit)->all();
            if (!empty($loanList)) {
                $result = $this->sendsms($loanList);
                $succ += $result['succ'];
                $error += $result['error'];
                $this->log("\n all:{$total},SUCCESS:{$result['succ']},EROOR:{$result['error']},pages:{$i}\n");
            }
        }
        Logger::dayLog('shortsendsms',  'TOTAL:' . $total .',SUCCESS:' . $succ . ',FAIL:' . $error);
    }

    private function sendsms($loanList){
        $success = 0;
        $error=0;
        foreach ($loanList as $k => $v) {
           if(!empty($v->loan_id)){
               $loan_arr[]=$v->loan_id;
           }
        }
        if(!empty($loan_arr)){
            $success=1;
            $loan_id_str= implode(",", $loan_arr);
            $mobile=15093560261;
//            $mobile=18401629347;
            (new ApiSms())->sendMonitor($mobile, 2,$loan_id_str);
        }

        return ['succ' => $success, 'error' => $error];
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }


}
