<?php

namespace app\models;

use app\common\Http;
use app\common\Logger;
use app\models\SmsRecord;
use app\common\sms\CSms;
use app\common\sms\CSmsNew;
/**
 */
class PaySms {
    /**
     * 定义出错数据
     */
    public $errinfo;
    private $smsLimitCount = 10;
    /**
     * 短信模板
     */
    public function sendSmsOld($phone, $smscode, $amount, $aid = 1) {
        $amount = $amount / 100;
        $channel = 'yunxin'; //yunxin | chuanglan
        $smsRecord = new SmsRecord;
        $sendCount = $smsRecord->findOneHourCount($phone,$aid);
        if($sendCount > $this->smsLimitCount){
            Logger::dayLog('sms/outlimit','超限','phone:'.$phone,'aid:'.$aid,'channel:'.$channel);
            return false;
        }
        switch ($aid) {
        case 4:
            $sign = '【花生米富】';
            $content = "{$smscode}支付验证码.即将支付金额{$amount}元，如有问题请联系花生米富微信客服。";
            $result = $this->sendCodeBySign($phone, $content, $sign, $channel, $smscode);
            break;
        case 1:
            $channel = 'chuanglan'; //yunxin | chuanglan
            $sign = '【先花一亿元】';
            $content = "{$smscode}，验证码 （为了资金安全，请勿将验证码告知他人）。支付金额{$amount}元，如有问题请联系先花一亿元微信客服。";
            $result = $this->sendCodeBySign($phone, $content, $sign, $channel, $smscode);
            break;
        default:
            # code...
            $result = false;
            break;
        }
        Logger::dayLog(
            'sms/yptztind',
            $result ? 'success' : 'error',
            'phone', $phone,
            'smscode', $smscode
        );
        $data = [
            'aid'=>$aid,
            'code'=>(string)$smscode,
            'mobile'=>$phone,
            'channel_type'=>$channel,
            'status'=> $result ? 1 : 2,
        ];
        $res = $smsRecord->createData($data);
        return $result ? true : false;
    }
    /**
     * 发送验证码
     * @param  str $phone
     * @param  str $content
     * @param  str $sign         签名
     * @param  str $channel
     * @return  bool
     */
    private function sendCodeBySign($phone, $content, $sign, $channel, $smscode) {
        $channel = strtolower($channel);
        switch ($channel) {
        case 'yunxin':
            $result = Http::sendByMobile($phone, $content, $sign);
            break;
        case 'chuanglan':
            $content = $sign . $content;

            $data = [
                'mobile' => $phone,
                'content' =>$content, 
                'sms_type' => 1,
                'code' => $smscode,
                'channel_type' => 2,
            ];

            $oApi = new \app\common\ApiClientCrypt;
            $res = $oApi->sent('sms/sendchuanglansmstouser', $data);
            $response = $oApi->parseResponse($res);
            $result = is_array($response) && isset($response['res_code']) && $response['res_code'] === 0;

            //$result = Http::sendSmsByChuanglan($phone, $content, 2);
            break;
        default:
            # code...
            $result = false;
            break;
        }
        return $result ? true : false;
    }
    /**
     * 短信模板
     */
    public function sendSms($phone, $smscode, $amount, $aid = 1) {
        $amount = $amount / 100;
        $channel = 'xinda'; //yunxin | chuanglan | xinda
        $smsRecord = new SmsRecord;
        $sendCount = $smsRecord->findOneHourCount($phone,$aid);
        if($sendCount > $this->smsLimitCount){
            Logger::dayLog('sms/outlimit','超限','phone:'.$phone,'aid:'.$aid,'channel:'.$channel);
            return $this->returnError(false,"获取短信次数超限");
        }
        switch ($aid) {
        case 4:
            $msgSign = "1";//消息签名1花生米富2先花一亿元
            $sendChannel = "3";//1创蓝2云信3信达优创
            $sign = '【花生米富】';
            $content = "{$smscode}支付验证码.即将支付金额{$amount}元，如有问题请联系花生米富微信客服。";
            $msgContent = $content . $sign;
            $result = $this->sendCode($phone, $msgContent, $msgSign, $sendChannel);
            break;
        case 1:
            $msgSign = "2";//消息签名1花生米富2先花一亿元
            $sendChannel = "1";//1创蓝2云信3信达优创
            $channel = 'chuanglan'; //yunxin | chuanglan
            $sign = '【先花一亿元】';
            $content = "{$smscode}，验证码 （为了资金安全，请勿将验证码告知他人）。支付金额{$amount}元，如有问题请联系先花一亿元微信客服。";
            $msgContent = $sign . $content;
            $result = $this->sendCode($phone, $msgContent, $msgSign, $sendChannel);
            break;
        case 8:
            $msgSign = "3";//消息签名1花生米富2先花一亿元 3豆荚贷
            $sendChannel = "1";//1创蓝2云信3信达优创
            $channel = 'chuanglan'; //yunxin | chuanglan
            $sign = '【豆荚贷】';
            $content = "{$smscode}，验证码 （为了资金安全，请勿将验证码告知他人）。支付金额{$amount}元。";
            $msgContent = $sign . $content;
            $result = $this->sendCode($phone, $msgContent, $msgSign, $sendChannel);
            break;
        case 9:
            $msgSign = "4";//消息签名1花生米富2先花一亿元 3豆荚贷 4 米花花
            $sendChannel = "1";//1创蓝2云信3信达优创
            $channel = 'chuanglan'; //yunxin | chuanglan
            $sign = '【有米花】';
            $content = "{$smscode}，验证码 （为了资金安全，请勿将验证码告知他人）。支付金额{$amount}元。";
            $msgContent = $sign . $content;
            $result = $this->sendCode($phone, $msgContent, $msgSign, $sendChannel);
            break;
        case 10:
            $sendChannel = "3";//1创蓝2云信3信达优创
            $sign = '【有信令】';
            $content = "{$smscode}，验证码 （为了资金安全，请勿将验证码告知他人）。支付金额{$amount}元。";
            $msgContent = $sign . $content;
            $result = $this->sendMsg($phone, $msgContent,$sendChannel,$aid);
            break;
		case 16:
			$sendChannel = "3";//1创蓝2云信3信达优创
            $sign = '【七天乐】';
            $content = "{$smscode}，验证码 （为了资金安全，请勿将验证码告知他人）。支付金额{$amount}元。";
            $msgContent = $sign . $content;
            $result = $this->sendMsg($phone, $msgContent,$sendChannel,$aid);
            break;
        case 17:
            $sendChannel = "3";//1创蓝2云信3信达优创
            $sign = '【先花一个亿】';
            $content = "{$smscode}，验证码 （为了资金安全，请勿将验证码告知他人）。支付金额{$amount}元。";
            $msgContent = $sign . $content;
            $result = $this->sendMsg($phone, $msgContent,$sendChannel,$aid);
            break;
        default:
            # code...
            $result = false;
            break;
        }
        Logger::dayLog(
            'sms/yptztind',
            $result ? 'success' : 'error',
            'phone', $phone,
            'smscode', $smscode
        );
        $data = [
            'aid'=>$aid,
            'code'=>(string)$smscode,
            'mobile'=>$phone,
            'channel_type'=>$channel,
            'status'=> $result ? 1 : 2,
        ];
        $res = $smsRecord->createData($data);
        return $result ? true : false;
    }
    /**
     * Undocumented function
     * 发送短信
     * @param [type] $phone
     * @param [type] $msgContent
     * @param [type] $msgSign 消息签名1花生米富2先花一亿元
     * @param [type] $sendChannel消息通道1创蓝2云信3信达优创
     * @return void
     */
    private function sendCode($phone, $msgContent, $msgSign, $sendChannel){
        $data = [
            'phone' => $phone,
            'msgContent' =>$msgContent, 
            'msgSign' => $msgSign,
            'sendChannel' => $sendChannel,
            'ip' => \app\common\Func::get_client_ip(),
        ];
        $sms = new CSms;
        $response = $sms->sendAuth($data);
        Logger::dayLog('sms', 'sendcode', $data, $response);
        $result = is_array($response) && isset($response['rsp_code']) && $response['rsp_code'] === '0000';
        $rsp_msg = is_array($response) && isset($response['rsp_msg'])?$response['rsp_msg']:'';
        return $this->returnError($result,$rsp_msg);
    }
    /**
     * Undocumented function
     * 新版短信发送接口
     * @param [type] $phone
     * @param [type] $content
     * @param [type] $sendChannel
     * @param [type] $aid
     * @return void
     */
    private function sendMsg($phone, $content,$sendChannel,$aid){
        $data = [
            'phone'         => $phone,
            'content'       => $content, 
            'aid'           => $aid,
            'channel_code'  => $sendChannel,
            'ip'            => \app\common\Func::get_client_ip(),
        ];
        $sms = new CSmsNew;
        $response = $sms->sendAuth($data);
        Logger::dayLog('sms/sendmsg', 'sendMsg', $data, $response);
        $result = is_array($response) && isset($response['rsp_code']) && $response['rsp_code'] === '0000';
        $rsp_msg = is_array($response) && isset($response['rsp_msg'])?$response['rsp_msg']:'';
        return $this->returnError($result,$rsp_msg);
    }
     /**
     * 返回错误信息
     * @param  false | null $result 错误信息
     * @param  str $errinfo 错误信息
     * @return false | null 同参数$result
     */
    public function returnError($result, $errinfo) {
        $this->errinfo = $errinfo;
        return $result;
    }
}
