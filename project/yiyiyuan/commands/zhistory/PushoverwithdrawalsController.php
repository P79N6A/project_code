<?php

namespace app\commands;

/**
 *  5天未体现
 *  linux : sudo -u www /data/wwwroot/yiyiyuan/yii pushoverwithdrawals
 *  windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuana_app\yii pushoverwithdrawals
 */

use app\commonapi\Apidepository;
use app\models\news\Cg_remit;
use app\models\news\Push_not_withdrawals;
use app\models\news\User_loan;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class PushoverwithdrawalsController extends BaseController {

    private $limit = 200;
//26051112
    public function actionIndex() {
        $time_start = date("Y-m-d H:i:00", strtotime("-5 day"));
        $time_end = date('Y-m-d H:i:00', strtotime('-60 day'));
        $where = [
            'AND',
            ['>=', Cg_remit::tableName() . '.last_modify_time', $time_end],
            ['<',Cg_remit::tableName() . '.last_modify_time',$time_start],
            [Cg_remit::tableName() . '.remit_status' => 'WILLREMIT'],
//            [User_loan::tableName() . '.loan_id' => 26106223],
            [User_loan::tableName() . '.status' => 9],
            [Push_not_withdrawals::tableName().'.id'=>null]
        ];
        $sql = Cg_remit::find()->joinWith('userloan',true,'LEFT JOIN')->joinWith('pushnotwithdrawals',true,'LEFT JOIN')->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $loanList = $sql->limit($this->limit)->all();
            if (!empty($loanList)) {
                $result = $this->push($loanList);
                echo "total" . $total.',success'.$result;
            }
        }
    }

    public function push($loanList){
        $success = 0;
        $pushModel = new Push_not_withdrawals();
        foreach ($loanList as $k => $v) {
            //推送5天未体现借款
            $params = [
                'loanId' => $v->loan_id,
                'source' => 1
            ];
            $apiDep = new Apidepository();
            $repay_debt = $apiDep->pushOverDate($params);
            if ($repay_debt){
                $success++;
                $nowTime = date('Y-m-d H:i:s');
                $condition[] = [
                    'user_id' => $v->user_id,
                    'loan_id' => $v->loan_id,
                    'notify_status' => 1,
                    'notify_time' => $nowTime,
                    'last_modify_time' => $nowTime,
                    'create_time' => $nowTime
                ];
            }
        }
        if (!empty($condition)) {
            $pushModel->insertBatch($condition);
        }
        return $success;
    }
}
