<?php

namespace app\commonapi;

use app\commonapi\sms\SmsApi;
use app\models\news\Sms;
use Yii;

class ApiSmsShop {
    private $platform_host = "http://msg.xianhuahua.com/push-platform/";
    private $platform_host_test = "http://47.93.121.86:8090/push-platform";

    private function getHost() {
        $is_prod = SYSTEM_ENV == 'prod' ? true : false;
        $platform_host = $is_prod ? $this->platform_host : $this->platform_host_test;
        return $platform_host;
    }
    /**
     * 额度申请通过
     * @param $mobile
     * @param $money
     * @param $type
     * @return array|bool|mixed|void
     */
    public function sendApplySuccess($mobile, $money, $type){
        if (empty($mobile)) {
            return false;
        }
        $content = "尊敬的用户，恭喜您获得了{$money}元额度，快来先花商城购物吧！额度仅限本人使用，切勿向他人泄露个人信息。";
        return $this->choiceChannelShop($mobile, $content, $type, '', 3, 14);
    }

    /**
     * 展期，待支付短信
     * @param $mobile
     * @param $date
     * @return array|bool|mixed|void
     */
    public function sendRenewalWait($mobile,$date){
        if (empty($mobile)) {
            return false;
        }
        $day = empty($date['days']) ? 0 : $date['days'];
        $time = empty($date['time']) ? 0 : $date['time'];
        $content = "尊敬的用户，您的续期申请已通过，请在{$day}天内完成支付即可续期成功，剩余支付时间{$time}。";
        return $this->choiceChannelShop($mobile, $content, 45, '', 3, 14);
    }

    public function choiceChannelShop($mobile, $content, $type, $code, $sendchannel,$source = 14) {
        $data = ['mobile' => $mobile, 'content' => $content, 'sms_type' => $type, 'code' => $code];
        if (empty($code) || strlen($code) < 4) {
            $res = $this->sendMarketingSms([['phone' => $mobile, 'content' => $content, 'channel_code' => $sendchannel]],$source);
            $this->saveSms($mobile, $content, $type, 2, $sendchannel, 1, $code);
        } else {
            $res = $this->sendIndustrySms($mobile, $content, $sendchannel,$source);
            $this->saveSms($mobile, $content, $type, 2, $sendchannel, 1, $code);
        }
        return $res;
    }
    
     /**
     * 续期成功 【先花商城】恭喜您，您的商城账单续期成功，最后还款日变更为2018-08-30！请留意最后还款日按时还款
     * @param $mobile
     * @param $money
     * @param $type 46
     * @return array|bool|mixed|void
     */
    public function sendXuqiSuccess($mobile, $date, $type){
        if (empty($mobile)) {
            return false;
        }
        $content = "恭喜您，您的商城账单续期成功，最后还款日变更为{$date}！请留意最后还款日按时还款。";
        return $this->choiceChannelShop($mobile, $content, $type, '', 3, 14);
    }
    /**
     * 续期失败
     * @param $mobile
     * @param $money 【先花商城】很抱歉，由于您续期费用支付失败，本次续期失败，您可在1天23:00:08 内进行重新支付 
     * @param $type 47  【先花商城】很抱歉，由于您续期费用支付失败，本次续期失败。请留意最后还款日进行还款
     * @return array|bool|mixed|void
     */
    public function sendXuqiFail($mobile,$date, $type){
        if (empty($mobile)) {
            return false;
        }
        $content = "很抱歉，由于您续期费用支付失败，本次续期失败。请留意最后还款日进行还款。";
        if(!empty($date)){
            $content = "很抱歉，由于您续期费用支付失败，本次续期失败，您可在{$date}内进行重新支付。";
        }
        return $this->choiceChannelShop($mobile, $content, $type, '', 3, 14);
    }
    /**
     * Undocumented function
     * 批量发送营销类短信-New
     * @return void
     */
    public function sendMarketingSms($postData,$source = 14) {
        if (empty($postData) || !is_array($postData))
            return false;
        $smsList = json_encode($postData, JSON_UNESCAPED_UNICODE);
        $oApi = new SmsApi;
        $data = [
            'business_code' => $source, //14商城 15转售
            'channel_code' => $postData[0]['channel_code'],
            'smsList' => $smsList, //json
        ];
        $host = $this->getHost();
        $url = $host . '/api/sms/v2/send_marketing_sms';
        $result = $oApi->send($url, $data);
        Logger::dayLog('psms', $data, $result);
        if (empty($result)) {
            return ['rsp_code' => '-1', 'rsp_msg' => '响应超时'];
        }
        return json_decode($result, true);
    }

    /**
     * 发送短信记录存储
     * @param array $data 数据集合
     * @return boolean
     */
    private function saveSms($mobile, $content, $sms_type, $status = 0, $channel = 3, $type = 1, $code = '') {
        $condition = [
            'content' =>$content,
            'recive_mobile' => $mobile,
            'sms_type' => $sms_type,
            'code' => isset($code) ? (string) $code : '1',
            'send_mobile' => '',
        ];
        $ret = (new Sms())->save_sms($condition);
        return true;
    }

    /**
     * 发送触发类短信-new
     * @param $phone    手机号
     * @param $content  短信内容
     * @param $channelCode  通道
     * @return array|bool|mixed
     */
    public function sendIndustrySms($mobile, $content, $channelCode, $source) {
        if (empty($mobile) || empty($content) || empty($channelCode)) {
            return false;
        }
        $host = $this->getHost();
        $url = $host . '/api/sms/v2/send_industry_sms';
        $data = [
            'phone' => $mobile,
            'content' => $content,
            'channel_code' => $channelCode,
            'aid' => $source, //14商城  15二转
            'ip' => Common::get_client_ip(),
        ];
        $oApi = new SmsApi;
        $result = $oApi->send($url, $data);
        Logger::dayLog('psms', $data, $result, $url);
        if (empty($result)) {
            return ['rsp_code' => '-1', 'rsp_msg' => '响应超时'];
        }
        return json_decode($result, true);
    }
}
