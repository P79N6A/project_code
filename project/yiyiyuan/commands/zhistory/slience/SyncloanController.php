<?php

namespace app\commands\slience;

use Yii;
use yii\console\Controller;
use app\models\slience\UserLoan;
use app\models\slience\Sloan;
use app\models\news\Loan_repay;
use app\commonapi\Logger;

/**
 * 同步借款数据和状态
 *   linux : /data/wwwroot/yiyiyuan/yii slience/syncloan/loan
 *   window : d:\xampp\php\php.exe d:\www\yiyiyuan\yii slience/syncloan/loan
 */
set_time_limit(0);
ini_set('memory_limit', '-1');
class SyncloanController extends Controller {
    /**
     * 同步前一天的成功借款数据
     * 命令 slience/syncloan/status
     * @param  string $theday 某一天 2017-07-07
     * @return 
     */
    public function actionLoan($theday = null) {
        if (empty($theday)) {
            $theday = date('Y-m-d', (time() - 24 * 3600));
        }
        
        $thetime = strtotime($theday);
        $start_date = date('Y-m-d', $thetime);
        $end_date = date('Y-m-d', $thetime + 86400);
        $result = $this->syncLoan($start_date, $end_date);
    }

    private function syncLoan($start_date, $end_date){
        $condition = [
            'AND',
            ['in',"status",[8,9]],
            ['between', 'create_time', $start_date, $end_date]
        ];

        $total = UserLoan::find()->where($condition)->count();

        //每100条处理一次
        $limit = 100;
        $pages = ceil($total / $limit);

        Logger::dayLog('syncLoan', "共获取".$total."条数据","每次处理".$limit,"需要要处理".$pages."次");

        for ($i = 0; $i < $pages; $i++) {
            $loan_list = UserLoan::find()->where($condition)->offset($i * $limit)->limit($limit)->all();
             //如果没有出款，则直接结束
            if (empty($loan_list)) {
                return true;
            }

            foreach ($loan_list as $key => $value) {
                $data = [
                    'loan_id' => $value->loan_id,
                    'user_id' => $value->user_id,
                    'amount' => $value->amount,
                    'chase_amount' => isset($value->chase_amount)  ? $value->chase_amount : 0,
                    'coupon_amount' => 0,
                    'days' => $value->days,
                    'start_date' => $value->start_date,
                    'end_date' => $value->end_date,
                    'status' => $value->status,
                    'ostatus' => $value->status,
                    'last_modify_time' => $value->last_modify_time,
                    'create_time' => $value->create_time,
                    'version' => 1,
                    'repay_time' => $value->repay_time
                ];

                $sloan = new Sloan();
                $result = $sloan->createSuccessLoan($data);
            }

        }

        return true;

    }
    
    /**
     * 同步借款状态
     * 命令 slience/syncloan/status
     * @param  string $theday 日期 2017-07-07
     * @return [type] [description]
     */
    public function actionStatus($theday = null) {
        if (empty($theday)) {
            $theday = date('Y-m-d', (time() - 24 * 3600));
        }
        $thetime = strtotime($theday);
        $start_date = date('Y-m-d', $thetime);
        $end_date = date('Y-m-d', $thetime + 86400);
        $result = $this->syncStatus($start_date, $end_date);
    }
    private function syncStatus($start_date, $end_date){
        $condition = [
            'AND',
            ['status' => 1],
            ['between', 'last_modify_time', $start_date, $end_date]
        ];

        $total = Loan_repay::find()->where($condition)->count();
        //每100条处理一次
        $limit = 100;
        $pages = ceil($total / $limit);

        Logger::dayLog('syncStatus', "共获取".$total."条数据","每次处理".$limit,"需要要处理".$pages."次");
        for ($i = 0; $i < $pages; $i++) {
            $repay_list = Loan_repay::find()->where($condition)->offset($i * $limit)->limit($limit)->all();
             //如果没有还款，则直接结束
            if (empty($repay_list)) {
                return true;
            }

            foreach ($repay_list as $key => $value) {
                $user_loan = UserLoan::findOne($value->loan_id);
                
                $sloan = Sloan::find()->where(['loan_id'=>$value->loan_id, 'slient_number' => 0])->one();
                if(!empty($sloan)) {
                    // 只有结清时才去改状态
                    if($user_loan->status == 8){
                        $result = $sloan->saveLoanstatus($user_loan->status, $user_loan->last_modify_time, $user_loan->repay_time);
                    }
                }else{
                    $data = [
                        'loan_id' => $user_loan->loan_id,
                        'user_id' => $user_loan->user_id,
                        'amount' => $user_loan->amount,
                        'chase_amount' => $user_loan->chase_amount,
                        'coupon_amount' => 0,
                        'days' => $user_loan->days,
                        'start_date' => $user_loan->start_date,
                        'end_date' => $user_loan->end_date,
                        'status' => $user_loan->status,
                        'ostatus' => $user_loan->status,
                        'last_modify_time' => $user_loan->last_modify_time,
                        'create_time' => $user_loan->create_time,
                        'version' => 1,
                        'orepay_time' => $user_loan->repay_time
                    ];
                    
                    $sloan = new Sloan();
                    $result = $sloan->createSuccessLoan($data);
                }
            }
        }

        return true;
    }

    /**
     * 同步当天的的逾期数据
     * 命令 slience/syncloan/overdue
     * @param  string $theday 日期 2017-07-07
     * @return [type] [description]
     */
    public function actionOverdue($theday = null) {
        if (empty($theday)) {
            $theday = date('Y-m-d');
        }
        $thetime = strtotime($theday);
        $start_date = date('Y-m-d', $thetime);
        $result = $this->syncOverdue($start_date);
    }
    private function syncOverdue($start_date){
        $month = 13;
        for ($j = 0;$j< 13;$j++) {
            $startDate = date('Y-m-d H:i:00', strtotime("-$month month",strtotime($start_date)));
            $month--;
            $endDate = date('Y-m-d H:i:00', strtotime("-$month month",strtotime($start_date)));
            $condition = [
                'and',
                ['>=', 'end_date', $startDate],
                ['<', 'end_date', $endDate],
                ['in', 'status', [11, 12, 13]],
                ['in', 'business_type', [1, 4]],
                ['in', 'is_push', [0, -1]],
                ['>', 'chase_amount', 0],
            ];
            $total = UserLoan::find()->where($condition)->count();

            //每100条处理一次
            $limit = 100;
            $pages = ceil($total / $limit);

            Logger::dayLog('syncOverdue', "共获取".$total."条数据","每次处理".$limit,"需要要处理".$pages."次");
            for ($i = 0; $i < $pages; $i++) {
                $loan_list = UserLoan::find()->where($condition)->offset($i * $limit)->limit($limit)->all();
                //如果没有借款，则直接结束
                if (empty($loan_list)) {
                    return true;
                }

                foreach ($loan_list as $key => $value) {
                    $sloan = Sloan::find()->where(['loan_id'=>$value->loan_id, 'slient_number'=>0])->one();
                    if(!empty($sloan)) {
                        $result = $sloan->saveOverduestatus($value->status, $value->last_modify_time);
                    }else{
                        $data = [
                            'loan_id' => $value->loan_id,
                            'user_id' => $value->user_id,
                            'amount' => $value->amount,
                            'chase_amount' => $value->chase_amount,
                            'coupon_amount' => 0,
                            'days' => $value->days,
                            'start_date' => $value->start_date,
                            'end_date' => $value->end_date,
                            'status' => $value->status,
                            'ostatus' => $value->status,
                            'last_modify_time' => $value->last_modify_time,
                            'create_time' => $value->create_time,
                            'version' => 1,
                            'repay_time' => $value->repay_time
                        ];

                        $sloan = new Sloan();
                        $result = $sloan->createSuccessLoan($data);
                    }
                }
            }
        }
        return true;
    }
}
