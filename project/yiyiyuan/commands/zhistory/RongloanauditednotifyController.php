<?php
/**
 * 融360借款推送
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
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\news\YiLoanNotify;
use app\models\news\Loan_mapping;
use Yii;
use yii\console\Controller;
use yii\web\User;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class RongloanauditednotifyController extends Controller {

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
    private $appId =  3300063;
    private $pid =  1002;
    // 命令行入口文件
    public function actionIndex()
    {
        $limit = 500;
        $modify_start_time = date("Y-m-d H:i:s", strtotime("-10 minutes"));
        $modify_end_time = date("Y-m-d H:i:s", time());
        $where = [
            'AND',
            ['channel'=>$this->appId],
            //['BETWEEN', 'last_modify_time', $modify_start_time, $modify_end_time],
            ['IN', 'status', array(6,7,8,9,10)],
            ['notify_status'=>1]
        ];
        $sql = YiLoanNotify::find()->where($where)->orderBy("create_time ASC");

        $notify_data = $sql->limit($limit)->asArray()->all();
        if (!empty($notify_data)){
            $notify_id_data = Common::ArrayToString($notify_data, 'id');
            YiLoanNotify::updateAll(['notify_status' => 2], ['notify_status' => 1, 'id' => explode(',', $notify_id_data)]);
            foreach($notify_data as $key => $value){
                $htt_resutl = $this->sendApiInterface($value);
            }
        }
    }


    /**
     * 请求接口
     * @param $notify_info
     * @return bool
     */
    private function sendApiInterface($notify_info)
    {
        $user_loan = User_loan::find()->where(['loan_id'=>$notify_info['loan_id']])->one();
        if (empty($user_loan)) return false;
        $format_data = $format_plan = '';
        //审核通过
        if ($notify_info['status'] == 6){
            $format_data = $this->formatLoanSix($user_loan, $notify_info);
        }
        //审核未通过
        if ($notify_info['status'] == 7){
            $format_data = $this->formatLoanpassSix($notify_info, $user_loan);
        }
        //放款成功
        if ($notify_info['status'] == 9){
            $format_data = $this->formatLoansuccess($user_loan, $notify_info);
            $format_plan = $this->formatLoanplan($user_loan, $notify_info);
        }
        //放款失败
        if ($notify_info['status'] == 10){
            $format_data = $this->formatLoanfail($user_loan, $notify_info);
        }
        $format_repay = [];
        //还款
        if ($notify_info['status'] == 8){
            if ($notify_info['remit_status'] == 'SUCCESS') {
                $format_data = $this->formatLoanover($user_loan, $notify_info);
            }
            $format_repay = $this->formatRepayData($user_loan, $notify_info);
            //$format_plan = $this->formatLoanplan($user_loan, $notify_info);
        }

        if (SYSTEM_ENV == 'prod'){
            $htt_url = "https://openapi.rong360.com/gateway";
        }else{
            $htt_url = "https://openapi-test.rong360.com/gateway";
        }
        $data = [];
        $cur_time = time();
        //支付成功通知
        if (!empty($format_repay)){
            $repay_data = [
                "app_id"=> $this->appId,
                "method"=> "is.api.v3.order.repayfeedback",
                "sign_type"=> "RSA",
                "timestamp"=>  "$cur_time",
                "version"=>"1.0",
                "format" => "json",
                "biz_data"=> json_encode($format_repay),
            ];
            $sign = $this->saveRsa($this->shortData($repay_data));
            $repay_data['sign'] = $sign;
            $repay_data = json_encode($repay_data);
            Logger::errorLog(print_r(array($repay_data), true), 'rong_prepay__r360_notify', 'r360');
            $repay_ret = Http::interface_post_json_rong($htt_url, $repay_data);
            if (!empty($repay_ret)) {
                $this->setLoanNotifyByDB($notify_info, $repay_ret);
            }
            Logger::errorLog(print_r(array($repay_ret), true), 'rrong_prepay__r360_notifyreturn', 'r360');
        }
        if (!empty($format_data)) {
            if (in_array($notify_info['status'], [6, 7])) {
                $data = [
                    "app_id" => $this->appId,
                    "method" => "is.api.v3.order.approvefeedback",
                    "sign_type" => "RSA",
                    "timestamp" => "$cur_time",
                    "version" => "1.0",
                    "format" => "json",
                    "biz_data" => json_encode($format_data),
                ];

            }
            if (in_array($notify_info['status'], [8, 9, 10])) {
                $data = [
                    "app_id" => $this->appId,
                    "method" => "is.api.v3.order.orderfeedback",
                    "sign_type" => "RSA",
                    "timestamp" => "$cur_time",
                    "version" => "1.0",
                    "format" => "json",
                    "biz_data" => json_encode($format_data),
                ];
            }
            if (empty($data)) return false;
            $sign = $this->saveRsa($this->shortData($data));
            $data['sign'] = $sign;
            $data = json_encode($data);
            Logger::errorLog(print_r(array($data), true), 'rong__r360_notify', 'r360');
            $htt_resutl = Http::interface_post_json_rong($htt_url, $data);
            if (!empty($htt_resutl)) {
                $this->setLoanNotifyByDB($notify_info, $htt_resutl);
            }
            Logger::errorLog(print_r(array($htt_resutl), true), 'rong__r360_notify_return', 'r360');
        }
        //还款计划
        if (!empty($format_plan)){
            $plan_data = [
                "app_id" => $this->appId,
                "method" => "is.api.v3.order.pushrepayment",
                "sign_type" => "RSA",
                "timestamp" => "$cur_time",
                "version" => "1.0",
                "format" => "json",
                "biz_data" => json_encode($format_plan),
            ];
            $sign = $this->saveRsa($this->shortData($plan_data));
            $plan_data['sign'] = $sign;
            $plan_data = json_encode($plan_data);
            Logger::errorLog(print_r(array($plan_data), true), 'rong_plan__r360_notify', 'r360');
            $ret = Http::interface_post_json_rong($htt_url, $plan_data);
            if (!empty($ret)) {
                $this->setLoanNotifyByDB($notify_info, $ret);
            }
            Logger::errorLog(print_r(array($ret), true), 'rrong_plan__r360_notifyreturn', 'r360');
        }
    }

    /**
     * 格式审核通过数据
     * @param $user_loan
     * @param $notify_info
     * @return array
     */
    private function formatLoanSix($user_loan, $notify_info)
    {
        $uesr_loan_object = new User_loan();
        //获取应还款的金额
        //$pay_amount = $uesr_loan_object->getRepaymentAmount($user_loan->loan_id);
        $pay_amount = $uesr_loan_object->getRepaymentAmount($user_loan);
        /*
        $pay_amount = $uesr_loan_object->getRepaymentAmount(
            $user_loan->loan_id,
            $user_loan->status,
            $user_loan->chase_amount,
            $user_loan->collection_amount,
            $user_loan->like_amount,
            $user_loan->amount,
            $user_loan->current_amount,
            $user_loan->interest_fee,
            $user_loan->coupon_amount,
            $user_loan->withdraw_fee);
        */
        $biz_data = [
            "order_no"=>$notify_info['channel_loan_id'],
            "conclusion"=>10,
            "service_fee"=>number_format($user_loan->withdraw_fee,4),
            "amount_type"=>"0",
            "approval_amount"=>number_format($user_loan->amount,4),
            "approval_term"=>"7",
            "approval_time"=>strtotime($user_loan->last_modify_time),
            "month_fee_rate"=>0,
            "month_interest_rate"=>0,
            "pay_amount"=>number_format($pay_amount,4),
            "pay_extra_amount"=>0,
            "receive_amount"=>number_format(round($uesr_loan_object->getActualAmount($user_loan->is_calculation, $user_loan->amount), 4), 4),
            "term_type"=>"0",
            "term_unit"=>"1"
        ];
        return $biz_data;
    }
    /**
     * 格式未审核通过数据
     * @param $notify_info
     * @return mixed
     */
    private function formatLoanpassSix($notify_info,$user_loan)
    {
        $biz_data = [
            "order_no"=>$notify_info['channel_loan_id'],
            "conclusion"=>40,
            "remark"=>"信用评分过低",
            "refuse_time" => strtotime($user_loan->last_modify_time),
        ];
        return $biz_data;
    }

    /**
     * 放款成功
     * @param $notify_info
     * @return array
     */
    private function formatLoansuccess($user_loan, $notify_info)
    {
        $biz_data = [
            "order_no"=>$notify_info['channel_loan_id'],
            "order_status"=> 170,
            "update_time"=> strtotime($user_loan->last_modify_time),
        ];
        return $biz_data;
    }

    /**
     * 放款失败
     * @param $notify_info
     * @return array
     */
    private function formatLoanfail($user_loan, $notify_info)
    {
        $biz_data = [
            "order_no"=>$notify_info['channel_loan_id'],
            "order_status"=> 169,
            "update_time"=> strtotime($user_loan->last_modify_time)
        ];
        return $biz_data;
    }

    /**
     * 贷款结清
     * @param $user_loan
     * @param $notify_info
     * @return array
     */
    private function formatLoanover($user_loan, $notify_info)
    {
        $biz_data = [
            "order_no"=>$notify_info['channel_loan_id'],
            "order_status"=> 200,
            "update_time"=> strtotime($user_loan->last_modify_time)
        ];
        return $biz_data;
    }

    /**
     * 出款成功计划
     * @param $user_loan
     * @param $notify_info
     * @return array|bool
     */
    private function formatLoanplan($user_loan, $notify_info)
    {
        $user_bank_object = new User_bank();
        $uesr_loan_object = new User_loan();
        $bank_info = $user_bank_object->getDepositCardInfo($user_loan->user_id);
        $loan_repay_info = Loan_repay::find()->where(['loan_id'=>$user_loan->loan_id])->one();
        if (empty($bank_info)) return false;
        //获取应还款的金额
        //$pay_amount = number_format(round($uesr_loan_object->getRepaymentAmount($user_loan->loan_id),4 ), 4);
        $pay_amount = number_format(round($uesr_loan_object->getRepaymentAmount($user_loan),4 ), 4);
        /*
        $pay_amount = $uesr_loan_object->getRepaymentAmount(
            $user_loan->loan_id,
            $user_loan->status,
            $user_loan->chase_amount,
            $user_loan->collection_amount,
            $user_loan->like_amount,
            $user_loan->amount,
            $user_loan->current_amount,
            $user_loan->interest_fee,
            $user_loan->coupon_amount,
            $user_loan->withdraw_fee);
        */
        if (strtotime($user_loan->end_date) > time()){
            $bill_status = 1;
        }else{
            $bill_status = 3;
        }
        if ($notify_info['status'] == 8){
            $bill_status = 2;
        }
        $cost = number_format(round($user_loan->interest_fee + $user_loan->withdraw_fee,4),4);
        $biz_data = [
            "order_no"=>$notify_info['channel_loan_id'],
            "open_bank"=>$bank_info->bank_name,
            "bank_card"=>$bank_info->card,
            "repayment_plan" => [
                [
                    "period_no"=>"1",
                    "due_time"=>strtotime($user_loan->end_date),
                    "amount"=>$pay_amount,
                    "paid_amount"=>empty($loan_repay_info)? 0.0000 : number_format(round($loan_repay_info->actual_money,4 ), 4),
                    "pay_type"=>"2",
                    "bill_status"=>$bill_status,
                    "can_repay_time"=>strtotime($user_loan->start_date),
                    "remark"=>'含本金'.number_format(round(($user_loan->amount-$user_loan->withdraw_fee),4),4).'元，利息&手续费'.$cost.'元',
                    "is_able_defer"=>0,
                    "overdue_fee" => 0,
                    "success_time" => 0,
                ],
            ],
        ];
        return $biz_data;
    }

    //支付成功数据
    public function formatRepayData($user_loan, $notify_info)
    {
        $repay_status = $notify_info['remit_status'] == "SUCCESS"?1:2;
        if ($repay_status == 2){
            $remark = "还款失败";
        }else{
            $remark = '本金:'.$user_loan->amount.'元，利息&手续费'.$user_loan->interest_fee.'元';
        }
        $biz_data = [
            "order_no"=>$notify_info['channel_loan_id'],
            "period_nos" => "1",
            "repay_status" => $repay_status ,
            "repay_place"=>1,
            "success_time"=>strtotime($user_loan->repay_time),
            "remark" => $remark,
        ];
        return $biz_data;
    }

    /**
     * 记录借款结果通知表（yi_loan_notify）
     * @param $notify_info
     * @param $http_result
     * @return bool
     */
    private function setLoanNotifyByDB($notify_info, $http_result)
    {
        if (empty($notify_info) || empty($http_result)) return false;
        $get_where_config = [
            'id'=>$notify_info['id'],
            'status' => $notify_info['status'],
            'channel' => strval($this->appId),
        ];
        $http_result = json_decode($http_result, true);
        $notify_info = YiLoanNotify::find()->where($get_where_config)->one();
        if (empty($notify_info)) return false;
        $data_set = [
            'mark' => $http_result['msg'],
            'result' => $http_result['error'],
            'notify_num' => $notify_info['notify_num'] + 1,
            'notify_status'=>3,
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
}
