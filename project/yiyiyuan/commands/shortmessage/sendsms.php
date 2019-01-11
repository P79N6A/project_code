<?php

namespace app\commands\withdrawals;

/**
 *  未体现结束订单  每天11点半跑数据
 *  linux : sudo -u www /data/wwwroot/yiyiyuan/yii pushoverwithdrawals
 *  windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuana_app\yii pushoverwithdrawals
 */

use app\commonapi\Logger;
use app\models\news\Cg_remit;
use app\models\news\User_loan;
use Yii;
use yii\console\Controller;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class WithdrawalsoverController extends Controller {

    private $limit = 200;
    public function actionIndex() {
        $time_loan_end = date('Y-m-d 00:00:00', strtotime('+1 day'));
        $where = [
            'AND',
            [Cg_remit::tableName() . '.remit_status' => 'WILLREMIT'],
            [User_loan::tableName() . '.status' => 9],
            [User_loan::tableName() . '.end_date' => $time_loan_end],
        ];
        $sql = Cg_remit::find()->joinWith('userloan',true,'LEFT JOIN')->where($where);
        $total = $sql->count();
        $succ = 0;
        $error = 0;
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $loanList = $sql->limit($this->limit)->all();
            if (!empty($loanList)) {
                $result = $this->UpdateStatus($loanList);
                $succ += $result['succ'];
                $error += $result['error'];
                $this->log("\n all:{$total},SUCCESS:{$result['succ']},EROOR:{$result['error']},pages:{$i}\n");
            }
        }
        Logger::dayLog('withdrawovertotal',  'TOTAL:' . $total .',SUCCESS:' . $succ . ',FAIL:' . $error);
    }

    private function UpdateStatus($loanList){
        $success = 0;
        $error=0;
        foreach ($loanList as $k => $v) {
            if(empty($v)){
                $error++;
                continue;
            }
            $update_loan_res = $v->userloan->changeStatus(8);
            $update_cg_res = $v->noRemit();
            $remitlist_result = $v->remitlist->savePaySuccess();
            if($update_loan_res && $update_cg_res && $remitlist_result){
                $success++;
            }else{
                $error++;
            }
        }
        return ['succ' => $success, 'error' => $error];
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }


}
