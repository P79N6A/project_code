<?php

namespace app\commonapi\sms;

use app\commonapi\Common;

/**
 * 短信
 */
class CSms {

    private $platform_host = "http://msg.xianhuahua.com/push-platform/";
    private $platform_host_test = "http://47.93.121.86:8090/push-platform";

    private function getHost() {
        $is_prod = SYSTEM_ENV == 'prod' ? true : false;
        $platform_host = $is_prod ? $this->platform_host : $this->platform_host_test;
        return $platform_host;
    }

    /**
     * Undocumented function
     * 批量发送营销类短信
     * @return void
     */
    public function sendMarketing($postData) {
        if (empty($postData) || !is_array($postData))
            return false;
        $msgSendDetails = json_encode($postData, JSON_UNESCAPED_UNICODE);
        $oApi = new SmsApi;
        $data = [
            'msgSendDetails' => $msgSendDetails//json
        ];
        $host = $this->getHost();
        $url = $host . '/api/sms/send_marketing';
        $result = $oApi->send($url, $data);
        if (empty($result)) {
            return ['rsp_code' => '-1', 'rsp_msg' => '响应超时'];
        }
        return json_decode($result, true);
    }

    /**
     * Undocumented function
     * 批量发送营销类短信-New
     * @return void
     */
    public function sendMarketingSms($postData) {
        if (empty($postData) || !is_array($postData))
            return false;
        $smsList = json_encode($postData, JSON_UNESCAPED_UNICODE);
        $oApi = new SmsApi;
        $data = [
            'business_code' => 1, //一亿元
            'channel_code' => $postData[0]['channel_code'],
            'smsList' => $smsList, //json
        ];
        $host = $this->getHost();
        $url = $host . '/api/sms/v2/send_marketing_sms';
        $result = $oApi->send($url, $data);
        \app\commonapi\Logger::dayLog('psms', $data, $result);
        if (empty($result)) {
            return ['rsp_code' => '-1', 'rsp_msg' => '响应超时'];
        }
        return json_decode($result, true);
    }

    /**
     * 发送触发类短信-new
     * @param $phone    手机号
     * @param $content  短信内容
     * @param $channelCode  通道
     * @return array|bool|mixed
     */
    public function sendIndustrySms($mobile, $content, $channelCode) {
        if (empty($mobile) || empty($content) || empty($channelCode)) {
            return false;
        }
        $host = $this->getHost();
        $url = $host . '/api/sms/v2/send_industry_sms';
        $data = [
            'phone' => $mobile,
            'content' => $content,
            'channel_code' => $channelCode,
            'aid' => 1, //一亿元
            'ip' => Common::get_client_ip(),
        ];
        $oApi = new SmsApi;
        $result = $oApi->send($url, $data);
        \app\commonapi\Logger::dayLog('psms', $data, $result);
        if (empty($result)) {
            return ['rsp_code' => '-1', 'rsp_msg' => '响应超时'];
        }
        return json_decode($result, true);
    }

    /**
     * Undocumented function
     * 发送触发类短信
     * @return void
     */
    public function sendAuth($postData) {
        $oApi = new SmsApi;
        $host = $this->getHost();
        $url = $host . '/api/sms/send_auth';
        $result = $oApi->send($url, $postData);
        if (empty($result)) {
            return ['rsp_code' => '-1', 'rsp_msg' => '响应超时'];
        }
        return json_decode($result, true);
    }

    /**
     * app消息推送单播
     * @param $postData
     * @return array|bool|mixed
     */
    public function unicast($postData) {
        if (empty($postData) || !is_array($postData))
            return false;
        $oApi = new SmsApi();
        $host = $this->getHost();
        $url = $host . '/api/msg/send_msg';
        $result = $oApi->send($url, $postData);
        \app\commonapi\Logger::dayLog('um', $postData, $result);
        if (empty($result)) {
            return ['rsp_code' => '-1', 'rsp_msg' => '响应超时'];
        }
        return json_decode($result, true);
    }

}
