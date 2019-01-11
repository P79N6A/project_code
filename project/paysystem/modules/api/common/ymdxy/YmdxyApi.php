<?php

namespace app\modules\api\common\ymdxy;
use app\common\Logger;

/**
 *京东接口类
 */
class YmdxyApi {

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
        //访问储蓄卡签约服务b

        $url = $this->config['apiUrl'] . 'rp/openAgreement';
        Logger::dayLog('ymdxy/ymdxyApi','getSignSms', '获取验证码请求信息：',$paramArr,$url);

        $resultData =$this->oUtil->send_rsa($url , $paramArr,$this->config);
        Logger::dayLog('ymdxy/ymdxyApi','getSignSms', '获取验证码请求结果：',$resultData);

        return $resultData;
    }


    /**
     *  校验签约----签约完成
     * @param $paramArr
     * @return mixed
     */
    public function checkSigning($paramArr) {
        $url = $this->config['apiUrl'] . 'rp/openCard';
        Logger::dayLog('ymdxy/ymdxyApi','checkSigning', '校验签约请求信息：',$paramArr,$url);
        $resultData =$this->oUtil->send_rsa($url , $paramArr,$this->config);
        Logger::dayLog('ymdxy/ymdxyApi', 'checkSigning','校验签约请求结果：',$resultData);

        return $resultData;

    }

    /**
     * 融宝协议支付
     * @param $paramArr
     * @return mixed
     */
    public function ymdxyPay($paramArr){
        //融宝协议支付
        $url = $this->config['apiUrl'] . 'rp/agreementPay';
        Logger::dayLog('ymdxy/ymdxyApi','ymdxyPay', '一麻袋协议支付请求信息：',$paramArr,$url);

        $resultData =$this->oUtil->send_rsa($url , $paramArr,$this->config);
        Logger::dayLog('ymdxy/ymdxyApi','ymdxyPay', '一麻袋协议支付请求结果：',$resultData);

        return $resultData;

    }

    /**
     * 融宝协议主动查询订单状态
     * @param $paramArr
     * @return mixed
     */
    public function ymdxyOrderQuery($paramArr){
        //一麻袋协议主动查询结果
        $url = $this->config['apiUrl'] . 'merchantBatchQueryAPI';
        Logger::dayLog('ymdxy/ymdxyApiQuery','ymdxyQuery', '一麻袋协议查询请求信息：',$paramArr,$url);

        $resultData = $this->oUtil->send_querst($url , $paramArr,$this->config);
        Logger::dayLog('ymdxy/ymdxyApiQuery','ymdxyQuery', '一麻袋协议查询请求结果：',$resultData);

        $result =  $this->xml_parser($resultData);
        if(!$result){
            Logger::dayLog('ymdxy/ymdxyApiQuery','ymdxyQueryERROR', '一麻袋协议查询结果解析失败：',$resultData);
            return false;
        }
        return $result;
    }


    /**
     * 自定义xml验证函数xml_parser()
     * @param $str（XML字符串）
     * 成功返回转好的数组，失败返回false
     * @return bool|mixed
     *
     */
    function xml_parser($str){
        $xml_parser = xml_parser_create();
        if(!xml_parse($xml_parser,$str,true)){
            xml_parser_free($xml_parser);
            return false;
        }else {
            return (json_decode(json_encode(simplexml_load_string($str)),true));
        }
    }




    /**
     *  解密验签
     * @param $resultData
     * @return mixed
     */
    public function decryptData($resultData){
        $resultData = $this->oUtil->rsaVerify($resultData,$this->config);
        return $resultData;
    }




}
