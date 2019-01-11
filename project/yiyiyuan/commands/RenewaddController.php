<?php

/**
 * 添加逾前借款可续期记录
 */

namespace app\commands;

use app\models\news\User_rate;
use Yii;
use yii\console\Controller;
use app\commonapi\Logger;
use app\models\news\User_loan;
use app\models\news\Renew_amount;
use app\models\news\User_loan_extend;

set_time_limit(0);
ini_set('memory_limit', '-1');

class RenewaddController extends Controller {

    protected $renew = 0.05;

    public function actionIndex() {
        $limit = 500;
        $modify_start_time = date("Y-m-d H:i:00", strtotime("-10 minutes"));
        $modify_end_time = date("Y-m-d H:i:00", time());
        $time_in = date("Y-m-d H:i:s", strtotime("-5 day"));
//        $source = array(1, 2, 3, 4, 5);
        $where = [
            'AND',
            ['BETWEEN', User_loan_extend::tableName() . '.last_modify_time', $modify_start_time, $modify_end_time],
            [">", User_loan_extend::tableName() . ".create_time", $time_in],
            [User_loan_extend::tableName() . ".status" => "SUCCESS"],
            [User_loan::tableName() . '.status' => 9],
            [User_loan::tableName() . '.business_type' => [1, 4, 9,10]],
//            ['IN', User_loan::tableName() . '.source', $source],
        ];
        $user_loan_extend = User_loan_extend::find()->joinWith('loan', true, 'LEFT JOIN')->joinWith('insurance', true, 'LEFT JOIN')->where($where);
        $total = $user_loan_extend->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $loan_extend_info = $user_loan_extend->offset($i * $limit)->limit($limit)->all();
            if (!empty($loan_extend_info)) {
                $res = $this->addRenew($loan_extend_info);
                echo "成功插入" . $res . "条";
            }
        }
    }

    /**
     * 记录可续期借款表（yi_renew_amount）
     * @param $loan_extend_info
     * @return bool
     */
    private function addRenew($loan_extend_info) {
        $renew_model = new Renew_amount;
        foreach ($loan_extend_info as $k => $v) {
            $renew_info = $renew_model->getRenew($v->loan->loan_id);
            if (empty($renew_info)) {
                $nowTime = date('Y-m-d H:i:s');
                $withdraw_fee = $v->loan->withdraw_fee;
                $fee = ($withdraw_fee>0)?$withdraw_fee:(isset($v->insurance)&&$v->insurance->money>0?$v->insurance->money:$v->loan->amount*0.18);
                $renew_fee = $this->renew * $v->loan->amount + $fee;
                $condition[] = [
                    'loan_id' => $v->loan->loan_id,
                    'renew_fee' => $renew_fee,
                    'user_id' => $v->loan->user_id,
                    'parent_loan_id' => $v->loan->parent_loan_id,
                    'mark' => 1,
                    'type' => 1,
                    'renew' => $this->renew,
                    'start_time' => date("Y-m-d H:i:s",strtotime("-6 day",strtotime($v->loan->end_date))),
                    'end_time' => $v->loan->end_date,
                    'create_time' => $nowTime
                ];
            }
        }
        $res = null;
        if (!empty($condition)) {
            $res = $renew_model->insertBatch($condition);
        }
        return $res;
    }

}
