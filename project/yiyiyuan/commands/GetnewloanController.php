<?php

/**
 * 逾期罚息
 */

namespace app\commands;

use app\commonapi\Logger;
use app\models\dev\User_loan;
use app\models\dev\User;
use app\models\dev\User_bank;
use app\models\dev\User_loan_new;
use Yii;
use yii\console\Controller;
/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */

class GetnewloanController extends Controller {

    private $desc = [
        '1' => '购买原材料',
        '2' => '进货',
        '3' => '购买设备',
        '4' => '购买家具或家电',
        '5' => '学习',
        '6' => '个人或家庭消费',
        '7' => '资金周转',
        '8' => '租房',
        '9' => '物流运输',
        '10' => '其他',
        '11' => '个人或家庭消费资金周转',
    ];
    // 命令行入口文件
    public function actionIndex() {
        $start_date = '2016-12-26 00:00:00';
        $end_date = '2016-12-27 00:00:00';
        
        //目标总额
        $target_amount = 600000;
        $target_repay_amount = 260000;
        $target_chase_amount = 340000;
        //开始注册时间
        $register_time = '2016-09-15 00:00:00';


         //该时间范围内不能有成功的借款
        $start_time = date('Y-m-d 00:00:00', strtotime($start_date)-28*24*60*60);
        $end_time = date('Y-m-d 00:00:00', strtotime($start_date)+29*24*60*60);


        //按时还款总额
        $repay_amount = 0;
        //逾期还款金额
        $chase_amount = 0;
        //总金额
        $total_amount = 0;
        $condition = [
            'AND',
            ['status' => 3],
            ['<','create_time',$start_date],
            ['>','create_time',$register_time]
        ];


        $total = User::find()->where($condition)->count();
        $limit = 100;
        $pages = ceil($total / $limit);

        $this->log( "\n". date('Y-m-d H:i:s') . "......................");
        $this->log("共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");

        for ($i = 0; $i < $pages; $i++) {
                $user_list = User::find()->where($condition)->offset($i * $limit)->limit($limit)->all();
                if( empty($user_list) ){
                    break;
                }

                if($total_amount >= $target_amount ){
                    break;
                    exit;
                }
                $this->log("处理范围" . ($i * $limit). ' -- ' . ($i * $limit + $limit) );
                foreach($user_list as $key=>$value) {
                    $where = [
                    'AND',
                    ['user_id'=>$value->user_id],
                    ['>','create_time',$start_time],
                    ['<','create_time',$end_time]
                ];
               
                if($total_amount >= $target_amount ){
                    break;
                    exit;
                }

                $loan_count = User_loan::find()->where($where)->count();
                
                $loan_new_count = User_loan_new::find()->where(['user_id'=>$value->user_id,'type'=>5])->count();
                $result = true;
                if($loan_count == 0 && $loan_new_count == 0){
                    $this->log("用户ID为{$value->user_id}\n");
                    $user_bank = User_bank::find()->where(['user_id'=>$value->user_id,'type'=>0,'status'=>1])->one();
                    if(!empty($user_bank)){
                            //随机生成的当天时间
                            $create_time = $this->randomDate($start_date, $end_date);
                            //随机生成还款时间
                            $repay_time = $this->randomDate(date('Y-m-d 00:00:00', strtotime($start_date)+28*24*60*60), date('Y-m-d 00:00:00', strtotime($start_date)+29*24*60*60));
                            //随机生成逾期时间
                            $chase_time = $this->randomDate(date('Y-m-d 00:00:00', strtotime($start_date)+29*24*60*60), date('Y-m-d 00:00:00', strtotime($start_date)+30*24*60*60));
                            $loanModel = new User_loan_new();
                            $suffix = $value->user_id . rand(100000, 999999);
                            $loan_no = date('YmdHis', strtotime($create_time)) . $suffix;
                            $amount = 1000;
                            $interest_fee = $amount*0.0005*28;
                            $withdraw_fee = $amount*0.1;
                            $number_desc = rand(1,11);

                            //生成新的借款
                            $array = array(
                                'user_id' => $value->user_id,
                                'loan_no' => $loan_no,
                                'number' => 0,
                                'real_amount' => $amount,
                                'amount' => $amount,
                                'credit_amount' => 0,
                                'recharge_amount' => 0,
                                'current_amount' => $amount,
                                'days' => 28,
                                'type' => 5,
                                'status' => 8,
                                'prome_status' => 5,
                                'interest_fee' => $interest_fee,
                                'withdraw_fee' => $withdraw_fee,
                                'desc' => $this->desc[$number_desc],
                                'bank_id' => $user_bank->id,
                                'withdraw_time' => $create_time,
                                'is_calculation' => 1,
                                'open_start_date' => $create_time,
                                'open_end_date' => $this->getOpenEndTime($create_time),
                                'start_date' => date('Y-m-d',  strtotime($create_time)),
                                'end_date' => $end_time,
                                'create_time' => $create_time,
                                'last_modify_time' => $create_time,
                                'final_score' => '-1',
                                'repay_type' => 2,
                                'coupon_amount' => 0,
                            );

                            if($repay_amount < $target_repay_amount){
                                $array['repay_time'] = $repay_time;
                                $ret = $loanModel->addUserLoan($array);
                                if($ret){
                                    $repay_amount += $amount;
                                    $total_amount  += $amount;
                                }
                            }else{
                                $array['repay_time'] = $chase_time;
                                $array['chase_amount' ] = '1024.14';
                                $ret = $loanModel->addUserLoan($array);
                                if($ret){
                                    $chase_amount  += $amount;
                                    $total_amount  += $amount;
                                }
                            }   
                    }
                }
            }
        }

        $this->log("\n金额:总额{$total_amount}元,正常{$repay_amount}元, 逾期{$chase_amount}元");
    }

     private function randomDate($begintime, $endtime="", $now = true) {
        $begin = strtotime($begintime);  
        $end = $endtime == "" ? mktime() : strtotime($endtime);
        $timestamp = rand($begin, $end);
        // d($timestamp);
        return $now ? date("Y-m-d H:i:s", $timestamp) : $timestamp;          
    }

    private function getOpenEndTime($open_start_date = '') {
        if (empty($open_start_date)) {
            $hour = date('H');
        } else {
            $hour = date('H', strtotime($open_start_date));
        }
        if ($hour >= 0 && $hour < 9) {
            $open_end_date = date('Y-m-d 15:00:00', strtotime($open_start_date));
        } else if ($hour < 18) {
            $open_end_date = date('Y-m-d H:i:s', strtotime($open_start_date)+6*60*60);
        } else {
            $open_end_date = date('Y-m-d H:i:s', strtotime($open_start_date)+15*60*60);
        }
        return $open_end_date;
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
