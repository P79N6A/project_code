<?php

namespace app\modules\api\common\rongbao;
use app\common\Curl;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\modules\api\common\rongbao\Util;
/**
 * 融宝支付类
 * @author YangJinlong
 */
class RongbaoApi {

    private $config;
    private $object;

    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
        $this->object = new Util();
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
    

    /*
     * 储蓄卡签约接口
     */

    public function debit($paramArr) {
        //访问储蓄卡签约服务
        $paramArr['merchant_id']  = $this->config['merchant_id'];
        $paramArr['seller_email'] = $this->config['seller_email'];
        $paramArr['currency']     = $this->config['currency'];
        $paramArr['cert_type']    = $this->config['cert_type'];
        $url                      = $this->config['apiUrl'] . '/fast/debit/portal';
        return $this -> sendRequest($url , $paramArr);
    }

    /*
     * 招商储蓄卡卡密验证接口（只有招商储蓄卡需要调用此接口）
     */

    public function certificate($paramArr) {
        //访问储蓄卡签约服务
        $paramArr['merchant_id'] = $this->config['merchant_id'];
        $url                     = $this->config['apiUrl'] . '/fast/certificate';
        $result                  = $this->object->send($paramArr, $url, $this->config['apiKey'], $this->config['reapalPublicKey'], $this->config['merchant_id']);
        return $result;
    }
    
    /*
     * 信用卡签约接口
     */
    public function cebit($paramArr) {
        //访问储蓄卡签约服务
        $paramArr['merchant_id']  = $this->config['merchant_id'];
        $paramArr['seller_email'] = $this->config['seller_email'];
        $paramArr['currency']     = $this->config['currency'];
        $paramArr['cert_type']    = $this->config['cert_type'];
        $url                      = $this->config['apiUrl'] . '/fast/credit/portal';
        return $this -> sendRequest($url , $paramArr);
    }

    /*
     * 绑卡签约接口
     */

    public function bindcard($paramArr) {
        $paramArr['merchant_id']  = $this->config['merchant_id'];
        $paramArr['seller_email'] = $this->config['seller_email'];
        $paramArr['currency']     = $this->config['currency'];
        //访问储蓄卡签约服务
        $url                      = $this->config['apiUrl'] . '/fast/bindcard/portal';
        return $this -> sendRequest($url , $paramArr);
    }

    /*
     * 确认支付接口
     */

    public function pay($paramArr) {
        $paramArr['merchant_id'] = $this->config['merchant_id'];
//访问储蓄卡签约服务
        $url                     = $this->config['apiUrl'] . '/fast/pay';
        return $this -> sendRequest($url , $paramArr);
    }

    /*
     * 支付结果查询
     */

    public function searchResult($paramArr) {
        $paramArr['merchant_id'] = $this->config['merchant_id'];
        //访问储蓄卡签约服务
        $url = $this->config['apiUrl'] . '/fast/search';
        return $this -> sendRequest($url , $paramArr);
    }

    /*
     * 重发短信
     */

    public function reSendSms($paramArr) {
        $paramArr['merchant_id'] = $this->config['merchant_id'];
        //访问储蓄卡签约服务
        $url                     = $this->config['apiUrl'] . '/fast/sms';
        return $this -> sendRequest($url , $paramArr);
    }
    
    /*
     * 支付异步回调
     */
    public function payNority($dataArr){
        $res = $this -> pubCheck($dataArr);
        $obj  = $res['obj'];
        $status = isset($obj['status']) ? $obj['status'] : false;
        if ($res['code']=='9000') {
            if ($status === "TRADE_FINISHED") {
                $verifyStatus = ['code' => '10000' , 'status' => 'success' , '支付成功'];
            } else {
                $verifyStatus = ['code' => $obj['result_code'] , 'status' => 'success' , 'msg' => $obj['result_msg']];
            }
        } else {
            $verifyStatus = ['code' => '10002' , 'status' => 'fail' , '数据来源有误'];
        }
        return $verifyStatus;
    }
    
    /*
     * 卡密异步回调
     */
    public function cardNotify($dataArr){
        $res = $this -> pubCheck($dataArr , 'cardNotify');
        $obj  = $res['obj'];
        $result_code = isset($obj['result_code']) ? $obj['result_code'] : false;
        if ($res['code']=='9000') {
            if ($result_code === "0000") {
                $verifyStatus = ['code' => '10000' , 'status' => 'success','msg' => $obj['result_msg']];
            } else {
                $verifyStatus = ['code' => $obj['result_code'] , 'status' => 'success','msg' => $obj['result_msg']];
            }
        } else {
            $verifyStatus = ['code' => '10002' , 'status' => 'fail' , 'msg'=> '数据来源有误'];
        }
        return $verifyStatus;
    }
    
    /*
     * 异步回调
     */
    public function notify($dataArr , $verify){
        if($verify=='status'){
            $type = 'rongbaopay';
            $value = 'TRADE_FINISHED';
        }else{
            $type = 'cardNotify';
            $value = '0000';
        }
        $merchant_id = $dataArr['merchant_id'];
        $data        = $dataArr['data'];
        $encryptkey  = $dataArr['encryptkey'];
        $encryptkey  = $this->object->RSADecryptkey($encryptkey, $this -> config['merchantPrivateKey']);
        $decryData   = $this->object->AESDecryptResponse($encryptkey, $data);
        $jsonObject  = json_decode($decryData, true);
        $paramarr    = [];
        $sign        = $jsonObject['sign'];
        foreach ($jsonObject as $k => $v) {
            if ($k == 'sign' || $k == 'sign_type') {
                continue;
            }
            $paramarr[$k] = $v;
        }

        $mysign = $this->object->createSign($paramarr, $this -> config['apiKey']);
        if ($mysign === $sign) {
            if ($jsonObject[$verify] === $value) {
                $verifyStatus = ['code' => '10000' , 'status' => 'success','msg' => '操作成功' , 'data' => $jsonObject];
            } else {
                $verifyStatus = ['code' => $jsonObject['result_code'] , 'status' => 'success','msg' => $jsonObject['result_msg'],'data' => $jsonObject];
            }
        } else {
            $verifyStatus = ['code' => '10002' , 'status' => 'fail' , 'msg'=> '数据来源有误','data' => $jsonObject];
        }
        return $verifyStatus;
    }


    /*
     * 统一请求返回结果方法
     */
    private function sendRequest($url , $paramArr){
        $result                   = $this->object->send($paramArr, $url, $this->config['apiKey'], $this->config['reapalPublicKey'], $this->config['merchant_id']);
        $response                 = json_decode($result, true);
        $encryptkey               = $this->object->RSADecryptkey($response['encryptkey'], $this->config['merchantPrivateKey']);
        $decryData = $this->object->AESDecryptResponse($encryptkey, $response['data']);
        $jsonObject  = json_decode($decryData, true);
        Logger::dayLog('rongpay/api',$url , $paramArr , $jsonObject);
        return $jsonObject;
    }
    /**
     * Undocumented function
     * 融宝余额查询
     * @return void
     */
    public function acctquery(){
        $paramArr = [
            'charset'   => 'UTF-8',
        ];
        $url = "https://agentpay.reapal.com/agentpay/balancequery";//余额请求地址
        $result                   = $this->object->sendquery($paramArr, $url, $this->config['apiKey'], $this->config['reapalPublicKey'], $this->config['merchant_id']);
        $response                 = json_decode($result, true);
        $encryptkey               = $this->object->RSADecryptkey($response['encryptkey'], $this->config['merchantPrivateKey']);
        $decryData = $this->object->AESDecryptResponse($encryptkey, $response['data']);
        $jsonObject  = json_decode($decryData, true);
        Logger::dayLog('rongpay/api',$url , $paramArr , $jsonObject);
        return $jsonObject;
    }
}
