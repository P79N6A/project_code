<?php

namespace app\commonapi\policy;

use app\common\ApiSign;
use app\common\Curl;
use app\commonapi\Logger;

/**
 * 决策
 */
class policyApi {

    private $host = "http://strategy.xianhuahua.com/";
    private $host_test = "http://182.92.80.211:8122/";

    private function getHost() {
        $is_prod = SYSTEM_ENV == 'prod' ? true : false;
        $host = $is_prod ? $this->host : $this->host_test;
        return $host;
    }

    /**
     * 获取用户分期额度、分期周期、是否可以分期
     * @param $postData
     * @return array|bool|mixed
     */
    public function antiperiod($postData) {
        if (empty($postData) || !is_array($postData))
            return false;
        $apiSignModel = new ApiSign();
        $sign = $apiSignModel->signData($postData);
        $host = $this->getHost();
        $url = $host . 'sfapi/periods/antiperiod';
        $curl = new Curl();
        $ret = $curl->post($url, $sign);
        Logger::dayLog('antiperiod', print_r(array($postData['user_id'] => $ret), true));
        $result = json_decode($ret, true);
        if (!$result) {
            return '{"rsp_code":"404","rsp_msg":"service error"}';
        }
        $isVerify = $apiSignModel->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return '{"rsp_code":"200","rsp_msg":"sign error"}';
        }
        return $result['data'];
    }

    /**
     * 获取用户续期额度
     * @param $postData
     * @return array|bool|mixed
     */
    public function overbefore($postData) {
        if (empty($postData) || !is_array($postData))
            return false;
        $apiSignModel = new ApiSign();
        $sign = $apiSignModel->signData($postData);
        $host = $this->getHost();
        $url = $host . 'sysapi/sysloan/overbefore';
        $curl = new Curl();
        $ret = $curl->post($url, $sign);
        Logger::dayLog('overbefore', print_r(array($postData['user_id'] => $ret), true));
        $result = json_decode($ret, true);
        if (!$result) {
            return '{"rsp_code":"404","rsp_msg":"service error"}';
        }
        $isVerify = $apiSignModel->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return '{"rsp_code":"200","rsp_msg":"sign error"}';
        }
        return $result['data'];
    }

    /**
     * 反欺诈信息查询
     * @param $postData
     * @return bool|string
     * @author 王新龙
     * @date 2018/9/4 16:48
     */
    public function antiFraud($postData)
    {
        if(empty($postData) || !is_array($postData)){
            return false;
        }
        $apiSignModel = new ApiSign();
        $sign = $apiSignModel->signData($postData);
        $host = $this->getHost();
        $url = $host.'api/service/credit-data';
        $curl = new Curl();
        $ret = $curl->post($url, $sign);
        Logger::dayLog('api/policy/antifraud', $postData['user_id'], $url, $sign, $ret);
        $result = json_decode($ret, true);
        if(!$result){
            return '{"res_code":"404","res_msg":"service error"}';
        }
        $isVerify = $apiSignModel->verifyData($result['data'], $result['_sign']);
        if(!$isVerify){
            return '{"res_code":"200","res_msg":"sign error"}';
        }
        return $result['data'];
    }
}
