<?php

/**
 * 提醒用户提现个推提醒添加
 */

namespace app\commands\umengsend;

use app\models\news\Cg_remit;
use app\models\news\Insure;
use app\models\news\SmsSend;
use app\models\news\UmengSend;
use app\commands\BaseController;
use app\models\news\User_loan;
use app\commonapi\Logger;
use app\models\news\User_loan_extend;
use Yii;

set_time_limit(0);
ini_set('memory_limit', '-1');

class InsuranceController extends BaseController {

    public function addPolicyInfo($time,$time_type = '') {
        $limit = 500;
        $time_start = date("Y-m-d H:i:s", strtotime("-".$time." hours"));
        $time_end = date("Y-m-d H:i:s", strtotime("-".$time." hours 10 minute"));
        $where = [
            'AND',
            ["BETWEEN", User_loan_extend::tableName() . ".last_modify_time", $time_start,$time_end],
            [User_loan_extend::tableName() . '.status' => 'TB-SUCCESS'],
            [User_loan::tableName() . '.source' => [2,4]],
        ];
        $userLoan = User_loan::find()->joinWith('loanextend', true, 'LEFT JOIN')->where($where);
        $total = $userLoan->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $loan_info = $userLoan->offset($i * $limit)->limit($limit)->all();
            if (!empty($loan_info)) {
                $res = $this->todo($loan_info,$time,$time_type);
                echo "success:" . $res;
            }
        }
    }

    /**
     * 添加提醒
     * @param $insure_info
     * @param $time
     * @param $time_type
     * @return int
     */
    private function todo($userLoan,$time,$time_type) {
        $success = 0;
        $umengModel = new UmengSend();
        foreach ($userLoan as $k => $v) {
            $res = $umengModel->saveUmengSend($v,2,$time,$time_type);
            if($res){
                $success++;
            }else{
                Logger::dayLog('Umengsend', '添加提醒失败：', $v->loan_id);
            }
        }
        return $success;
    }
}
