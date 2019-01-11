<?php

namespace app\commands\claim;

use app\commands\BaseController;
use app\commonapi\ApiSign;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\dev\User_loan_new;
use app\models\news\Claim_loan_user;
use app\models\news\Difference_amount;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;

/**
 * 第三方债权推送
 */

/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */
class ThirdloanController extends BaseController {

    // 命令行入口文件
    public function actionIndex() {
        $loan_diff = new Difference_amount();
        $target_amount = $loan_diff->getRemainmoney();
        if ($target_amount <= 0) {
//            exit;
        }
        //该时间范围内不能有成功的借款
        $start_time = date('Y-m-d 00:00:00', strtotime("-1 days"));
        $end_time = date('Y-m-d 00:00:00');
        //总金额
        $total_amount = 0;
        $condition = [
            'AND',
            ['status' => [3, 7]],
            ['>=', 'create_time', $start_time],
            ['<', 'create_time', $end_time]
        ];

        $total = User_loan::find()->where($condition)->count();
        $limit = 100;
        $pages = ceil($total / $limit);

        for ($i = 0; $i < $pages; $i++) {
            $loan_list = User_loan::find()->where($condition)->offset($i * $limit)->limit($limit)->all();
            if (empty($loan_list)) {
                break;
            }
            if ($total_amount >= $target_amount) {
                break;
            }
            $this->log("处理范围" . ($i * $limit) . ' -- ' . ($i * $limit + $limit));
            foreach ($loan_list as $key => $value) {

                if ($total_amount >= $target_amount) {
                    break;
                }
                $claimLoanModel = new Claim_loan_user();
                $is_claim = $claimLoanModel->getUserInMonth($value->user_id);
                if (!$is_claim) {
                    continue;
                }
                $conditions = [
                    'user_id' => $value['user_id'],
                    'loan_id' => $value['loan_id'],
                    'amount' => $value['amount'],
                    'loan_time' => $value['create_time'],
                ];
                $result = $claimLoanModel->addRecord($conditions);
                $data = [];
                if ($result) {
                    $data[] = [
                        'loan_id' => $value->loan_id,
                        'user_id' => $value->user_id,
                        'amount' => $value->amount,
                        'days' => $value->days,
                        'fee_day' => $value->create_time,
                        'fee' => $value->interest_fee,
                        'repay_day' => date('Y-m-d 00:00:00', strtotime($value->create_time) + $value->days * 24 * 60 * 60),
                        'repay_type' => 1,
                        'username' => $value->user->realname,
                        'mobile' => $value->user->mobile,
                        'identity' => $value->user->identity,
                        'company' => $value->user->extend->company,
                        'desc' => $value->desc,
                        'yield' => '0.0005',
                        'tag_type' => 3,
                    ];
                    $signData = (new ApiSign)->signData($data);
                    $signData['_sign'] = base64_encode($signData['_sign']);
                    //线上开放平台
                   $url = "http://eros.yaoyuefu.com/api/loan";
                    //测试债匹平台
//                    $url = "http://testeros.yaoyuefu.com/api/loan";
                    //测试开放平台
//                    $url = "http://182.92.80.211:8009/api/loan";
                    $result = Http::interface_post($url, $signData);
                    Logger::dayLog('testClaim/thirdloan', $data, $result);
                    $res_arr = json_decode($result, TRUE);
                    $res_data = json_decode($res_arr['data'], TRUE);
                    $res_data = json_decode($res_arr['data'], TRUE);
                    if ($res_data[0]['rsp_code'] == '0000') {
                        $total_amount += $value->amount;
                        $claimLoanModel->setSendStatus(1);
                    } else {
                        $claimLoanModel->setSendStatus(2);
                    }
                } else {
                    continue;
                }
            }
        }
        $diff_claim = $loan_diff->getRecord();
        $diff_condition = [
            'loan_expire_amount' => $diff_claim->loan_expire_amount + $total_amount,
        ];
        $diff_claim->updateRecord($diff_condition);
    }

    private function randomDate($begintime, $endtime = "", $now = true) {
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
            $open_end_date = date('Y-m-d H:i:s', strtotime($open_start_date) + 6 * 60 * 60);
        } else {
            $open_end_date = date('Y-m-d H:i:s', strtotime($open_start_date) + 15 * 60 * 60);
        }
        return $open_end_date;
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
