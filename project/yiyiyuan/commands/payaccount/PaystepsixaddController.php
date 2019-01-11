<?php

namespace app\commands\payaccount;

/**
 *  老的授权新增一条新的授权
 *  linux : sudo -u www /data/wwwroot/yiyiyuan/yii pushoverwithdrawals
 *  windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuana_app\yii pushoverwithdrawals
 */

use app\commonapi\Logger;
use app\models\news\Payaccount;
use app\models\news\PayAccountExtend;
use Yii;
use yii\console\Controller;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class PaystepsixaddController extends Controller {

    private $limit = 200;
    public function actionIndex() {
        $where = [
            'AND',
            [Payaccount::tableName() . '.type' => 2],
            [Payaccount::tableName() . '.step' => 5],
            [Payaccount::tableName() . '.activate_result' => 1],
        ];
        $sql = Payaccount::find()->joinWith('payaccountextend',true,'LEFT JOIN')->where($where)->groupBy('user_id');
        $total = $sql->count();
        $succ = 0;
        $error = 0;
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $oPayaccount = $sql->limit($this->limit)->all();
            if (!empty($oPayaccount)) {
                $result = $this->AddPayaccount($oPayaccount);
                $succ += $result['succ'];
                $error += $result['error'];
                $this->log("\n all:{$total},SUCCESS:{$result['succ']},EROOR:{$result['error']},pages:{$i}\n");
            }
        }
        Logger::dayLog('stepsixpayaccount',  'TOTAL:' . $total .',SUCCESS:' . $succ . ',FAIL:' . $error);
    }

    private function AddPayaccount($oPayaccount){
        $success = 0;
        $error=0;
        foreach ($oPayaccount as $k => $v) {
            if(empty($v)){
                $error++;
                continue;
            }
            $oStpeFive=Payaccount::find()->where(['type'=>2,'step'=>4,'activate_result'=>1,'user_id'=>$v->user_id])->one();
            if(empty($oStpeFive)){
                $error++;
                continue;
            }
            if(!empty($v->payaccountextend)){
                $error++;
                continue;
            }
            $condition=[
                'user_id'=>$v->user_id,
                'type'=>$v->type,
                'step'=>6,
                'activate_result'=>1,
                'activate_time'=>$v->activate_time,
                'accountId'=>$v->accountId,
            ];
            $res_add=(new Payaccount())->add_step($condition);
            if(empty($res_add)){
                $error++;
                continue;
            }
            $stepfourtime=date('Y-m-d ',strtotime($v->activate_time)+5*365*86400);
            $stepfivetime=date('Y-m-d ',strtotime($oStpeFive->activate_time)+5*365*86400);
            $condition_extend=[
                'pay_account_id'=>$res_add,
                'user_id'=>$v->user_id,
                'step'=>6,
                'paymax'=>50000.0000,
                'paydeadline'=>$stepfourtime,
                'repaymax'=>50000.0000,
                'repaydeadline'=>$stepfivetime,
            ];
            $res_payextend_add=(new PayAccountExtend())->addRecord($condition_extend);
            if($res_add && $res_payextend_add){
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
