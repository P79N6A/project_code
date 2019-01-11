<?php

/**
 * 借点钱借款推送
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/18
 * Time: 20:41
 */

namespace app\commands;

use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\commonapi\RSA;
use app\models\news\Loan_repay;
use app\models\news\User_loan;
use app\models\news\YiLoanNotify;
use yii\console\Controller;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class JdqnotifyController extends Controller {

    protected $service_rate = [7=>0.07,14=>0.1]; //服务费率
    protected $interest_rate = 0.0005; //利率
    protected $jdq_pub_test = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCi673Y3gmyfCySgaJub13Zo3mA
eqMcjNsjGzWV8scbN+XAp3uO3yQcmgaRE+Xro+VRfj/r4Vo18sUfMAxl3Pc3eSB/
VinGKCPyT9YZID7I4qy3YYlQM7ypSP8WcEWsTQzt9RpyrdK/hZYv4GATgwXTAkjQ
zQoRev9YofuLkyhL+wIDAQAB
-----END PUBLIC KEY-----';
    protected $jdq_pub = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC4N9jrYsSl99xi+tTAlB6Lwroi
hPvH/rmkl1lzae+Z/xQnOZzicuEk5Dtnx2cildSpTXIudjvPd5s7w2NcuuRttFwo
kxTdSUpj6I3j8VWYCTSXThbBJKYa1o+NbgEIZ+1Bink6sCLA7mml/ka7RO3a7cWD
Y/PwjxkjsV9NE4ZlGQIDAQAB
-----END PUBLIC KEY-----'; //生产环境
    protected $priv = '-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAKBbZ0OoOkZsTq3s
xpVbDvPfmFrSB5ENISAmSfxbYcdbOB/apNbRXKW+JiVj5Hv5Mz3DzlXUmM8Y7bbP
DpYjxsNDQrbd1DFVrcfFSs9JLB5zlD62fILKuSjZV89OKgdwg7GGqp8vcNZEuEgu
9ALuTWNCNQMDT4W8OQvDD5LaTrQZAgMBAAECgYAWLE1dF5fnQPaoKgNTh6HLqvFA
LaaKMgyQi3rTgDdG/6AFF5CPe6eZ628O4H8pfU3OjpKrX5g5mrLUAlF8BTpocYLY
Kpy9Oy2eGBI9ca9zaTup1aItGMiw9o4KnEzVb+KSy1lHsXY6SW1VigysotZunxYU
ZvC2KCCBnwcdXEUh2QJBANLXpycddBCY415mpgUqUy7txkGeMrjp8/FOLP1KbRkE
C8WjI54EX4AjXc2cSclIShAezMK8Na6F8jlTrGW7T7MCQQDCs6wtOXvm7d8ZiKU6
YHTcYMa6ecd7lTBLctwpc88XmOI1+z/TszVoVBVH6WqftP9GogGtwgHHHN/O+1af
5acDAkEAifbbRdkcDZA9l5QLpu2fKOImDOH7xswv+AJzpfqBkRD4swahU9EAvNRn
mRdfoPpQnGPLENIfPmgfrCt4b8k1yQJAGZjVgfyUtX+AXTMBxfL4aiCu/8US3MR4
XPL0zt5S059d3gryETr2QokLYzDku6poBTk3T0i6QxsgsW2JrevbUQJBAMAk32Z2
RfmVIeMl73fY0JRzkVv0uWqPShfP0qrIKNdkDXmUrImN2G4klkF8oD/4Aza+AGe2
ERnMnyFZOLfhqQU=
-----END PRIVATE KEY-----';
    private $channel_code = "xhyyy";
    private $http_url_test = 'http://apitest.jiedianqian.com/channel/commonCallback';
    private $http_url = 'http://api.jiedianqian.com/channel/commonCallback'; //生产环境

    // 命令行入口文件

    public function actionIndex() {
        $limit = 500;
        $modefy_start_time = date("Y-m-d H:i:s", strtotime("-10 minutes"));
        $modefy_end_time = date("Y-m-d H:i:s", time());
        $whereconfig = [
            'AND',
            ['channel' => $this->channel_code],
            //['BETWEEN', 'last_modify_time', $modefy_start_time, $modefy_end_time],
            ['IN', 'status', array(6, 7, 8, 9, 10, 12, 13)],
            ['notify_status' => 1]
        ];
        $sql = YiLoanNotify::find()->where($whereconfig)->orderBy("create_time ASC");

        $notify_data = $sql->limit($limit)->asArray()->all();
        if (!empty($notify_data)) {
            $notify_id_data = Common::ArrayToString($notify_data, 'id');
            YiLoanNotify::updateAll(['notify_status' => 2], ['notify_status' => 1, 'id' => explode(',', $notify_id_data)]);
            foreach ($notify_data as $key => $value) {
                $htt_resutl = $this->sendApiInterface($value);
            }
        }
    }

    /**
     * 请求接口
     * @param $notify_info
     * @return bool
     */
    private function sendApiInterface($notify_info) {
        $user_loan = User_loan::find()->where(['loan_id' => $notify_info['loan_id']])->one();
        if (empty($user_loan))
            return false;
        $format_data = $format_plan = '';
        //审核通过
        if ($notify_info['status'] == 6) {
            $format_data = $this->formatLoanSix($user_loan);
        }
        //审核未通过
        if ($notify_info['status'] == 7) {
            $format_data = $this->formatLoanpassSix($user_loan);
        }
        //逾期
        if ($notify_info['status'] == 12 || $notify_info['status'] == 13) {
            $format_data = $this->formatLoanoverdue($user_loan);
        }
        //放款成功
        if ($notify_info['status'] == 9) {
            $format_data = $this->formatLoansuccess($user_loan);
        }
        //放款失败
        if ($notify_info['status'] == 10) {
            $format_data = $this->formatLoanfail($user_loan);
        }
        //还款成功
        if ($notify_info['status'] == 8) {
            if ($notify_info['remit_status'] == 'SUCCESS') {
                $format_data = $this->formatLoanover($user_loan);
            }
        }


        if (empty($format_data))
            return false;

        Logger::errorLog(print_r(array($format_data), true), 'jdq_data_notify', 'jiedianqian');
        $format_data = $this->encryptByPub(json_encode($format_data));
        Logger::errorLog(print_r(array($format_data), true), 'jdq_endata_notify', 'jiedianqian');
        $data = [
            "channel_code" => $this->channel_code,
            "data" => $format_data,
        ];
        if (empty($data))
            return false;
        $sign = $this->saveRsa($this->shortData($data));
        $data['sign'] = $sign;
        $data = json_encode($data);
        Logger::errorLog(print_r(array($data), true), 'jdq_notify', 'jiedianqian');
        if (SYSTEM_ENV == 'prod') {
            $http = $this->http_url;
        } else {
            $http = $this->http_url_test;
        }

        $htt_resutl = Http::post_json($http, $data);
        Logger::errorLog(print_r(array($htt_resutl), true), 'jdq_notify_return', 'jiedianqian');
        $res = $htt_resutl[1];
        if (!empty($res)) {
            $this->setLoanNotifyByDB($notify_info, $res);
        }
    }

    /**
     * 审核通过
     * @param $user_loan
     * @param $user_loan
     * @return array
     */
    private function formatLoanSix($user_loan) {
        //逾期天数判断逾期费率
        $date=time();
        $yuqi = floor(($date - strtotime($user_loan->end_date)) / 60 /60 /24);
        $days = $user_loan->days;
        $biz_data = [
            "order_id" => $user_loan->loan_id,
            'status' => '3',
            'approval_amount' => ceil(($user_loan->amount) * 100) / 100,
            'approval_periods' => 1,

            'approval_period_days' => $days,
            'approval_days' => $days,
            'interest_rate' => (string) $this->interest_rate,//借款利率
            'service_rate' => (string) $this->service_rate[$days],//提现手续费
            'overdue_rate' => $yuqi < 90 ? '0.01' : '0.005',//逾期费率
            "repayment_plan" => [null],
        ];
        return $biz_data;
    }

    /**
     * 审核失败
     * @param $user_loan
     * @return mixed
     */
    private function formatLoanpassSix($user_loan) {
        //逾期天数判断逾期费率
        $date=time();
        $yuqi = floor(($date - strtotime($user_loan->end_date)) / 60 /60 /24);
        $days = $user_loan->days;
        $biz_data = [
            "order_id" => $user_loan->loan_id,
            'status' => '2',
            'approval_amount' => ceil(($user_loan->amount) * 100) / 100,
            'approval_periods' => 1,
            'approval_period_days' => $days,
            'approval_days' => $days,
            'interest_rate' => (string) $this->interest_rate,//借款利率
            'service_rate' => (string) $this->service_rate[$days],//提现手续费
            'overdue_rate' => $yuqi < 90 ? '0.01' : '0.005',//逾期费率
            "repayment_plan" => [null],
        ];
        return $biz_data;
    }

    /**
     * 放款成功
     * @param $user_loan
     * @return array
     */
    private function formatLoansuccess($user_loan) {
        //逾期天数判断逾期费率
        $date=time();
        $yuqi = floor(($date - strtotime($user_loan->end_date)) / 60 /60 /24);
        $days = $user_loan->days;
        $biz_data = [
            "order_id" => $user_loan->loan_id,
            'status' => '7',
            'approval_amount' => ceil(($user_loan->amount) * 100) / 100,
            'approval_periods' => 1,
            'approval_period_days' => $days,
            'approval_days' => $days,
            'interest_rate' => (string) $this->interest_rate,//借款利息
            'service_rate' => (string) $this->service_rate[$days],//提现手续费
            'overdue_rate' => $yuqi < 90 ? '0.01' : '0.005',//逾期费率
            "repayment_plan" => [$this->repaymentSucc($user_loan)],
        ];
        return $biz_data;
    }

    /**
     * 放款失败
     * @param $user_loan
     * @return array
     */
    private function formatLoanfail($user_loan) {
        //逾期天数判断逾期费率
        $date=time();
        $yuqi = floor(($date - strtotime($user_loan->end_date)) / 60 /60 /24);
        $days = $user_loan->days;
        $biz_data = [
            "order_id" => $user_loan->loan_id,
            'status' => '2',
            'approval_amount' => ceil(($user_loan->amount) * 100) / 100,
            'approval_periods' => 1,
            'approval_period_days' => $days,
            'approval_days' => $days,
            'interest_rate' => (string) $this->interest_rate,//借款利息
            'service_rate' => (string) $this->service_rate[$days],//提现手续费
            'overdue_rate' => $yuqi < 90 ? '0.01' : '0.005',//逾期费率
            "repayment_plan" => [null],
        ];
        return $biz_data;
    }

    /**
     * 还款成功
     * @param $user_loan
     * @return array
     */
    private function formatLoanover($user_loan) {
        //逾期天数判断逾期费率
        $date=time();
        $yuqi = floor(($date - strtotime($user_loan->end_date)) / 60 /60 /24);
        $days = $user_loan->days;
        $chase_amount = $user_loan->getChaseamount($user_loan->loan_id);
        $biz_data = [
            "order_id" => $user_loan->loan_id,
            'status' => $chase_amount > 0 ? '10' : "8",
            'approval_amount' => ceil(($user_loan->amount) * 100) / 100,
            'approval_periods' => 1,
            'approval_period_days' => $days,
            'approval_days' => $days,
            'interest_rate' => (string) $this->interest_rate,//借款利息
            'service_rate' => (string) $this->service_rate[$days],//提现手续费
            'overdue_rate' => $yuqi < 90 ? '0.01' : '0.005',//逾期费率
            "repayment_plan" => [$this->repaymentRepaySucc($user_loan)],
        ];
        return $biz_data;
    }

    /**
     * 逾期
     * @param $user_loan
     * @return array
     */
    private function formatLoanoverdue($user_loan) {
        //逾期天数判断逾期费率
        $date=time();
        $yuqi = floor(($date - strtotime($user_loan->end_date)) / 60 /60 /24);
        $days = $user_loan->days;
        $biz_data = [
            "order_id" => $user_loan->loan_id,
            'status' => '13',
            'approval_amount' => ceil(($user_loan->amount) * 100) / 100,
            'approval_periods' => 1,
            'approval_period_days' => $days,
            'approval_days' => $days,
            'interest_rate' => (string) $this->interest_rate,//借款利息
            'service_rate' => (string) $this->service_rate[$days],//提现手续费
            'overdue_rate' => $yuqi < 90 ? '0.01' : '0.005',//逾期费率
            "repayment_plan" => [$this->repaymentOverRepay($user_loan)],
        ];
        return $biz_data;
    }

    /**
     * 出款成功还款计划
     * @param $user_loan
     * @return array|bool
     */
    private function repaymentSucc($user_loan) {
        $biz_data = [
            "true_repayment_time" => '',
            "plan_repayment_time" => date('Y-m-d H:i:s', (strtotime($user_loan->end_date) - (3600 * 24))), //计划还款日期
            "amount" => ceil(($user_loan->amount) * 100) / 100, //本期还款本金
            "period_fee" => ceil(($user_loan->interest_fee) * 100) / 100, //本期手续（利息）费
            "period" => 1, //本期期数
            "status" => '1', //待还款
            "overdue_fee" => "0", //逾期罚款
            "overdue_day" => 0, //逾期天数
            "overdue" => 0, //是否逾期
        ];
        return $biz_data;
    }

    /**
     * 还款成功还款计划
     * @param $user_loan
     * @return array|bool
     */
    public function repaymentRepaySucc($user_loan) {
        $loan_repay_info = Loan_repay::find()->where(['loan_id' => $user_loan->loan_id])->one();
        //逾期天数
        $yqdays = floor((strtotime($loan_repay_info->last_modify_time) - strtotime($user_loan->end_date)) / 60 / 60 / 24);
        $chase_amount = $user_loan->getChaseamount($user_loan->loan_id);
        $biz_data = [
            "true_repayment_time" => $loan_repay_info->last_modify_time, //实际还款日期
            "plan_repayment_time" => date('Y-m-d H:i:s', (strtotime($user_loan->end_date) - (3600 * 24))), //计划还款日期
            "amount" => ceil(($user_loan->amount) * 100) / 100, //本期还款本金
            "period_fee" => ceil(($user_loan->interest_fee) * 100) / 100, //本期手续（利息）费
            "period" => 1, //本期期数
            "status" => $chase_amount > 0 ? "3" : '2', //3:逾期还清，2已还清
            "overdue_fee" => $chase_amount == NULL ? '0' : ceil(($chase_amount - $user_loan->amount - $user_loan->interest_fee) * 100) / 100, //逾期罚款
            "overdue_day" => $yqdays < 0 ? 0 : $yqdays, //逾期天数
            "overdue" => $chase_amount > 0 ? 1 : 0, //是否逾期
        ];

        return $biz_data;
    }

    /**
     * 逾期还款计划
     * @param $user_loan
     * @return array|bool
     */
    public function repaymentOverRepay($user_loan) {
        //逾期天数
        $yqdays = floor((time() - strtotime($user_loan->end_date)) / 60 / 60 / 24);
        $chase_amount = $user_loan->getChaseamount($user_loan->loan_id);
        $biz_data = [
            "true_repayment_time" => "", //实际还款日期
            "plan_repayment_time" => date('Y-m-d H:i:s', (strtotime($user_loan->end_date) - (3600 * 24))), //计划还款日期
            "amount" => ceil(($user_loan->amount) * 100) / 100, //本期还款本金
            "period_fee" => ceil(($user_loan->interest_fee) * 100) / 100, //本期手续（利息）费
            "period" => 1, //本期期数
            "status" => '4', //3:逾期还清，2已还清
            "overdue_fee" => $chase_amount == null ? '0' : ceil(($chase_amount - $user_loan->amount - $user_loan->interest_fee) * 100) / 100, //逾期罚款
            "overdue_day" => $yqdays, //逾期天数
            "overdue" => 1, //是否逾期
        ];

        return $biz_data;
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
            'channel' => strval($this->channel_code),
        ];
        $http_result = json_decode($http_result, true);
        $notify_info = YiLoanNotify::find()->where($get_where_config)->one();
        if (empty($notify_info))
            return false;
        $data_set = [
            'mark' => $http_result['desc'],
            'result' => $http_result['code'],
            'notify_num' => $notify_info['notify_num'] + 1,
            'notify_status' => 3,
        ];
        $ret = $notify_info->updateNotify($data_set);
        return $ret;
    }

    /**
     * 数据排序返回字符串
     * @param $sortedParams
     * @param string $type
     * @return string
     */
    protected function shortData($sortedParams, $type = "") {
        if ($type != "") {
            $sortedParams['type'] = $type;
        }
        unset($sortedParams['sign']);
        ksort($sortedParams);
        $string = '';
        $index = 0;
        foreach ($sortedParams as $key => $val) {
            $font = $index == 0 ? '' : '&';
            if (!empty($key) && !empty($val)) {
                $string .= $font . $key . '=' . $val;
                $index ++;
            }
        }
        return $string;
    }

    public function saveRsa($str) {
        $rsa = new RSA();
        // 签名的使用
//        $sign = $rsa->sign($str, $this->priv, 'base64', OPENSSL_ALGO_SHA256);
        $sign = $rsa->sign($str, $this->priv, 'base64');
        return $sign;
    }

    public function encryptByPub($string) {
        $rsa = new RSA();
        if (SYSTEM_ENV == 'prod') {
            $jdq_pub = $this->jdq_pub;
        } else {
            $jdq_pub = $this->jdq_pub_test;
        }
        $data = $rsa->encrypt128ByPublic($string, $jdq_pub);
        return $data;
    }

    public function encryptByPrev($string) {
        $rsa = new RSA();
        $data = $rsa->encrypt128ByPrivate($string, $this->priv);
        return $data;
    }

}
