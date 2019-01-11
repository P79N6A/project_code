<?php

namespace app\commands;

use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\Loan_repay;
use app\models\news\User_loan;
use app\models\news\YiLoanNotify;
use app\models\news\Loan_mapping;
use Yii;
use yii\console\Controller;
use yii\web\User;

/**
 *
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class LoanauditednotifyController extends Controller {

    private $appId = 1419;
    private $pid = 1002;
    private $http_url = 'https://openapi.rongshu.cn:443';

    // 命令行入口文件
    public function actionIndex() {
        $limit = 500;
        $modefy_start_time = date("Y-m-d H:i:s", strtotime("-15 minutes"));
        $modefy_end_time = date("Y-m-d H:i:s", strtotime("-5 minutes"));
        $whereconfig = [
            'AND',
            ['channel' => $this->appId],
//            ['BETWEEN', 'last_modify_time', $modefy_start_time, $modefy_end_time],
            ['IN', 'status', array(6, 7, 8, 9)],
            ['notify_status' => 1]
        ];
        $sql = YiLoanNotify::find()->where($whereconfig)->orderBy("create_time ASC");

        $notify_data = $sql->limit($limit)->asArray()->all();
        if (!empty($notify_data)) {
            $notify_id_data = Common::ArrayToString($notify_data, 'id');
            YiLoanNotify::updateAll(['notify_status' => 2], ['notify_status' => 1, 'id' => explode(',', $notify_id_data)]);
            foreach ($notify_data as $key => $value) {
                $htt_resutl = $this->sendApiInterface($value);
                if (!empty($htt_resutl)) {
                    $this->setLoanNotifyByDB($value, $htt_resutl);
                }
            }
        }
    }

    /**
     * 放款格式请求数据
     * @param $loaninfo
     * @param $status_code
     * @param $notify_info
     * @return string
     */
    private function formateLoanQueryData($loaninfo, $status_code, $notify_info) {
        //还款计划
        $repay_plan_data = [];
        $amount = $loaninfo->getRepaymentAmount($loaninfo);
        if ($loaninfo->status == 9) {
            //还款金额
            $repay_plan_data['amount'] = $amount;
            //期数
            $repay_plan_data['periodNo'] = $loaninfo->days;
            //可还款时间
            $repay_plan_data['canRepayTime'] = strtotime($loaninfo->start_date) . '000';
            //到期还款时间
            $repay_plan_data['dueTime'] = strtotime($loaninfo->end_date) . '000';
            //支持还款类型
            $repay_plan_data['payType'] = '1';
        }
        if (!empty($loaninfo->is_calculation) && $loaninfo->is_calculation == 1) {
            $loanAmount = sprintf('%.2f', $loaninfo->amount - $loaninfo->withdraw_fee);
        } else {
            $loanAmount = sprintf('%.2f', $loaninfo->amount);
        }
        $query_data = [
            'appId' => $this->pid,
            'orderId' => $notify_info['channel_loan_id'],
//            'orderId'=>271,
            'contractId' => $loaninfo->loan_id,
            //实际放款金额
            'loanAmount' => $loanAmount,
            //总还款金额
            'refundAmount' => $amount,
            'status' => ($notify_info['remit_status'] == 'SUCCESS') ? 50008 : 50009, //50008:放款成功 , 50009:放款失败
            'remark' => ($notify_info['remit_status'] == 'SUCCESS') ? '放款成功' : '50009',
            'repayPlan' => '[' . json_encode(Common::ksortArray($repay_plan_data)) . ']',
            'timestamp' => time() . '000',
        ];
        Logger::errorLog(print_r($query_data, true), 'loanresnotice', 'crontab');
        $http_url = http_build_query(Common::ksortArray($query_data)) . "&prepub_front=43623425735346&sign=" . Common::autographSign($query_data);
        return $http_url;
    }

    /**
     * 审核格式数据
     * @param $loaninfo
     * @param $status_code
     * @param $order_id
     * @return string
     */
    private function formatAudiData($loaninfo, $status_code, $order_id) {
        $amount = $loaninfo->getRepaymentAmount($loaninfo);
        if (!empty($loaninfo->is_calculation) && $loaninfo->is_calculation == 1) {
            $loanAmount = sprintf('%.2f', $loaninfo->amount - $loaninfo->withdraw_fee);
        } else {
            $loanAmount = sprintf('%.2f', $loaninfo->amount);
        }
        $remark = [
            '50004' => '审批通过',
            '50005' => '审批拒绝',
        ];
        $query_data = [
            'appId' => $this->pid,
            'orderId' => $order_id,
//            'orderId'=>271,
            //实际放款金额
            'loanAmount' => $loanAmount,
            //总还款金额
            'refundAmount' => $amount,
            'status' => $status_code,
            'remark' => empty($remark[$status_code]) ? '审批拒绝' : $remark[$status_code],
            'timestamp' => time() . '000',
        ];
        Logger::errorLog(print_r($query_data, true), 'formataudidata', 'crontab');
        $http_url = http_build_query(Common::ksortArray($query_data)) . "&prepub_front=43623425735346&sign=" . Common::autographSign($query_data);
        return $http_url;
    }

    /**
     * 请求接口
     * @param $notify_info
     * @return bool
     */
    private function sendApiInterface($notify_info) {
        if (empty($notify_info))
            return false;
        //还款
        if ($notify_info['status'] == 8) {
            $loan_repay_info = Loan_repay::find()->where(['loan_id' => $notify_info['loan_id']])->one();
            $format_data = $this->formateRepayQueryData($loan_repay_info, $notify_info);
            Logger::errorLog(print_r($format_data, true), 'sendApiInterface＿url', 'crontab');
            if (!empty($format_data)) {
                $http_ret = $this->sendRepayApiInterface($format_data);
                Logger::errorLog(print_r($format_data, true), 'repayresnotice', 'crontab');
                return $http_ret;
            }
        }
        //借款
        $rongshu_url = $this->statusInterfactUrl($notify_info['status']);
        $loaninfo = (new User_loan())->find()->where(['loan_id' => $notify_info['loan_id']])->one();
        if (empty($loaninfo))
            return false;
        if (preg_match('/hermes\/api\/loan/', $rongshu_url[1])) {
            $api_data = $this->formateLoanQueryData($loaninfo, $rongshu_url[0], $notify_info);
        } else {
            $api_data = $this->formatAudiData($loaninfo, $rongshu_url[0], $notify_info['channel_loan_id']);
        }
        $url = $rongshu_url[1] . $api_data;
        $result = Http::getCurl($url);
        $result = json_decode($result, true);
        Logger::errorLog(print_r($url, true), 'sendApiInterface＿url', 'crontab');
        Logger::errorLog(print_r($result, true), 'sendApiInterface＿result', 'crontab');
        return $result;
    }

    /**
     * 记录借款结果通知表（yi_loan_notify）
     * @param $notify_info
     * @param $http_result
     * @return bool
     */
    private function setLoanNotifyByDB($notify_info, $http_result) {
        if (empty($notify_info) || empty($http_result))
            return false;
        $get_where_config = [
            'id' => $notify_info['id'],
            'status' => $notify_info['status'],
            'channel' => strval($this->appId),
        ];
        $notify_info = YiLoanNotify::find()->where($get_where_config)->one();
        if (empty($notify_info))
            return false;
        $data_set = [
            'mark' => $http_result['message'],
            'result' => $http_result['code'],
            'notify_num' => $notify_info['notify_num'] + 1,
            'notify_status' => 3,
        ];
        $ret = $notify_info->updateNotify($data_set);
        return $ret;
    }

    /**
     * @param $notify_status
     * @return array
     */
    private function statusInterfactUrl($notify_status) {
        if ($notify_status == 7) {
            return [50005, $this->http_url . '/hermes/api/order/feedback.do?'];
        }
        if ($notify_status == 6) {
            return [50004, $this->http_url . '/hermes/api/order/feedback.do?'];
        }
        if ($notify_status == 9) {
            return [50008, $this->http_url . '/hermes/api/loan/feedback.do?'];
        }
        return ['50009', $this->http_url . '/hermes/api/loan/feedback.do?'];
    }

    /**
     * 格式请求数据
     * @param $repay_info
     * @param $notify_info
     * @return string
     */
    private function formateRepayQueryData($repay_info, $notify_info) {
        if (!empty($repay_info)) {
            $loan_info = User_loan::find()->where(['loan_id' => $repay_info['loan_id']])->one();
            if (empty($loan_info)) {
                return "";
            } else {
                $order_mapping_info = (new Loan_mapping())->newestLoanmapping($repay_info['loan_id']);
                $day_num = 0;
                if (!empty($loan_info['repay_time'])) {
                    $repay_time = strtotime($loan_info['repay_time']);
                    $end_date = strtotime($loan_info['end_date']);
                    $day_num = $this->calculationTime($repay_time, $end_date);
                }
                if ($notify_info['remit_status'] == "FAIL") {
                    $status = 50020;
                    $remark = "还款失败";
                } elseif ($notify_info['remit_status'] == "SUCCESS") {
                    $status = 50010;
                    $remark = "还款成功";
                } else {
                    return false;
                }
                $loan_info->chase_amount = $loan_info->getChaseamount($loan_info['loan_id']);
                $query_data = [
                    'appId' => $this->pid,
                    'orderId' => !empty($order_mapping_info) ? $order_mapping_info['order_id'] : "",
                    'contractId' => $repay_info->loan_id,
                    'period' => ($loan_info['days'] != 0) ? $loan_info['days'] : "", //期数
                    'loanAmount' => ($loan_info['amount'] != 0) ? $loan_info['amount'] : "", //本金
                    'refundAmount' => !empty($repay_info['actual_money']) ? $repay_info['actual_money'] : "", //还款金额
                    'serviceFee' => ($loan_info['withdraw_fee'] != 0) ? $loan_info['withdraw_fee'] : "", //服务费
                    'overdueFee' => !empty($loan_info['chase_amount']) ? $loan_info['chase_amount'] : "", //逾期费用
                    'status' => $status, //状态
                    'remark' => $remark, //备注
                    'timestamp' => time() . "000", //时间戳
                ];
                Logger::errorLog(print_r($query_data, true), 'sendApiInterface＿querydata', 'crontab');
                $http_url = http_build_query(Common::ksortArray($query_data)) . "&prepub_front=43623425735346&sign=" . Common::autographSign($query_data);
                return $http_url;
            }
        } else {
            return "";
        }
    }

    /**
     * @param $repay_time  还款时间
     * @param $end_date   到期时间
     * @return int
     */
    private function calculationTime($repay_time, $end_date) {
        if (empty($repay_time) || empty($end_data))
            return 0;
        if ($repay_time > $end_date) {
            $day_num = floor(($repay_time - $end_date) / 60 / 60 / 24);
            return $day_num;
        }
        return 0;
    }

    /**
     * 请求接口
     * @param $api_data
     * @return mixed
     */
    private function sendRepayApiInterface($api_data) {
        $rongshu_url = "https://openapi.rongshu.cn:443/hermes/api/refund/feedback.do?";
        $url = $rongshu_url . $api_data;
        Logger::errorLog(print_r($url, true), 'sendApiInterface＿sendurl', 'crontab');
        $result = Http::getCurl($url);
        Logger::errorLog(print_r($result, true), 'sendApiInterface＿result', 'crontab');
        return json_decode($result, true);
    }

}
