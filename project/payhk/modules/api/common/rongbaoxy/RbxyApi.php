<?php

namespace app\modules\api\common\rongbaoxy;
use app\common\Logger;

/**
 *京东接口类
 */
class RbxyApi {

    private $config;
    private $oUtil;
    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
        $this->oUtil = new Util();
    }

    /**
     * 获取配置文件
     * @param  str $env
     * @param  str $aid
     * @return   []
     */
    private function getConfig($cfg) {
        $configPath = __DIR__ . "/config/{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }


    /**
     * 预签约接口---发送短信验证码
     * @param $paramArr
     * @return mixed
     */
    public function getSignSms($paramArr) {
        //访问储蓄卡签约服务
        $paramArr['version']  = $this->config['version'];
        $paramArr['merchant_id']  = $this->config['merchant_id'];
        $paramArr['sign_type']  = $this->config['sign_type'];
        $paramArr['cert_type']    = $this->config['cert_type'];
        $url = $this->config['apiUrl'] . '/agreement/delivery/authentication';
        Logger::dayLog('rbxy/rbxyApi','getSignSms', '预签约获取验证码请求信息：',$paramArr,$url);

        $resultData = $this -> sendRequest($url , $paramArr);
        Logger::dayLog('rbxy/rbxyApi','getSignSms', '预签约获取验证码请求结果：',$resultData);

        $result =  $this->decryptData($resultData);
        Logger::dayLog('rbxy/rbxyApi','getSignSms', '融宝协议预签约获取验证码返回结果解密：',$result);

        return $result;
    }


    /**
     *  校验签约----签约完成
     * @param $paramArr
     * @return mixed
     */
    public function checkSigning($paramArr) {
        $paramArr['version']  = $this->config['version'];
        $paramArr['sign_type']  = $this->config['sign_type'];
        $paramArr['merchant_id']  = $this->config['merchant_id'];
        $url  = $this->config['apiUrl'] . '/agreement/delivery/sign';
        Logger::dayLog('rbxy/rbxyApi','checkSigning', '校验签约请求信息：',$paramArr,$url);

        $resultData = $this -> sendRequest($url , $paramArr);
        Logger::dayLog('rbxy/rbxyApi', 'checkSigning','校验签约请求结果：',$resultData);

        $result =  $this->decryptData($resultData);
        Logger::dayLog('rbxy/rbxyApi','checkSigning', '融宝协议校验签约返回结果解密：',$result);

        return $result;

    }

    /**
     * 融宝协议支付
     * @param $paramArr
     * @return mixed
     */
    public function rbxyPay($paramArr){
        //融宝协议支付
        $paramArr['version']  = $this->config['version'];
        $paramArr['merchant_id']  = $this->config['merchant_id'];
        $paramArr['sign_type']  = $this->config['sign_type'];
        $paramArr['currency']  = $this->config['currency'];
        $url = $this->config['apiUrl'] . '/agreement/delivery/pay';
        Logger::dayLog('rbxy/rbxyApi','rbxyPay', '融宝协议支付请求信息：',$paramArr,$url);

        $resultData = $this -> sendRequest($url , $paramArr);
        Logger::dayLog('rbxy/rbxyApi','rbxyPay', '融宝协议支付请求结果：',$resultData);

        $result =  $this->decryptData($resultData);
        Logger::dayLog('rbxy/rbxyApi','rbxyPay', '融宝协议支付返回结果解密：',$result);
        return $result;

    }

    /**
     * 融宝协议主动查询订单状态
     * @param $paramArr
     * @return mixed
     */
    public function rbxyOrderQuery($paramArr){
        //融宝协议主动查询结果
        $paramArr['version']  = $this->config['version'];
        $paramArr['sign_type']  = $this->config['sign_type'];
        $paramArr['merchant_id']  = $this->config['merchant_id'];
        $url = $this->config['apiUrl'] . '/fast/search';
        Logger::dayLog('rbxy/rbxyApiQuery','rbxyQuery', '融宝协议查询请求信息：',$paramArr,$url);

        $resultData = $this -> sendRequest($url , $paramArr);
        Logger::dayLog('rbxy/rbxyApiQuery','rbxyQuery', '融宝协议查询请求结果：',$resultData);

        $result =  $this->decryptData($resultData);
        Logger::dayLog('rbxy/rbxyApiQuery','rbxyQuery', '融宝协议查询返回结果解密：',$result);

        return $result;
    }


    /**
     * 统一请求返回结果方法
     * @param $url
     * @param $paramArr
     * @return mixed
     */
    private function sendRequest($url , $paramArr){
        $result = $this->oUtil->send_rsa($paramArr, $url, $this->config['merchantPrivateKey'], $this->config['reapalPublicKey'], $this->config['merchant_id']);
        return $result;
    }

    /**
     *  解密验签
     * @param $resultData
     * @return mixed
     */
    public function decryptData($resultData){
        $response = json_decode($resultData, true);
        $encryptkey = $this->oUtil->RSADecryptkey($response['encryptkey'], $this->config['merchantPrivateKey']);
        $decryData = $this->oUtil->AESDecryptResponse($encryptkey, $response['data']);
        $jsonObject  = json_decode($decryData, true);
        return $jsonObject;
    }




}
