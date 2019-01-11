<?php

namespace app\commonapi;

use Yii;
use app\commonapi\Logger;
//use app\commonapi\weixinapi\Config;
use app\commonapi\weixinapi\ClientResponseHandler;
use app\commonapi\weixinapi\PayHttpClient;
use app\commonapi\weixinapi\RequestHandler;
use app\commonapi\weixinapi\Utils;

/**
 * ================================================================
 * submitOrderInfo 提交订单信息
 * queryOrder 查询订单
 * 
 * ================================================================
 */
class Weixinpay {

    private $url;
    private $key;
    private $res_url;
    private $merid;

    /*
     * 初始化数据
     */
    public function __construct($cfg) {
        $this->config = include __dir__ . '/weixinapi/' . "{$cfg}.php";
        $this->key = $this->config['grb_key'];
        $this->merid = $this->config['grb_merid'];
        $this->res_url = $this->config['grb_res_url'];
        $this->url = $this->config['grb_url'];
    }

    private function getSubmitParams($params) {
        $params['merchantId'] = $this->merid;//商户号
        $params['timestamp'] = time();//时间戳
        $params['productName'] = "测试商品";//商品名称
        $params['businessType'] = "BT_114271207826925";
        $params['returnUrl'] = $this->config['grb_callback_url'];//返回页面 @TODO
        $params['serverUrl'] = $this->config['grb_notify_url'];//异步回调地址 @TODO 
        $params['privateKey'] = $this->key;
        $params['signature'] = md5($params['merchantId'].$params['timestamp'].$params['privateKey'].$params['merOrderId']);//签名
           
        return $params;
    }


    /**
     * 提交订单信息
     * $params array 参数数组
     * $type  支付类型 ，scan  扫码 sdk移动端 gzh 公众号支付 
     */
    public function submitOrderInfo($params) {
        $params = $this->getSubmitParams($params);//公共可提取的参数值
        Logger::dayLog('weixinpay', 'submitOrderInfo', $params);
//        Logger::errorLog(print_r($params, true), 'weixinpay');
        
        $result = Http::sendTemplatePost($params, $this->url);
        Logger::dayLog('weixinpay', 'submitOrderInfo_result', $result);
//        Logger::errorLog(print_r($result, true), 'result_alipay', 'alipay_notify');
        $result_arr = json_decode($result,true);
//        Logger::errorLog(print_r($result_arr, true), 'result_weixinpay', 'weixinpay_notify');
        if($result_arr['resultCode'] == 000000 && $result_arr['payStatus'] == 1){
            if($params['channel'] == "WX_APP"){
                return ['status' => 0, 'app_id' => $result_arr['pay']['app_id'],'partner_id' => $result_arr['pay']['partner_id'],'nonce_str' => $result_arr['pay']['nonce_str'],'package_info' => $result_arr['pay']['package'],'timestamp' => $result_arr['pay']['timestamp'],'pay_sign' => $result_arr['pay']['pay_sign'],'prepay_id' => $result_arr['pay']['prepay_id']];
            }else{
                return ['status' => 0, 'url' => $result_arr['pay']['url']];
            }
        }else{
            return ['status' => $result_arr['resultCode'], 'msg' => $result_arr['errorDetail']];
            
        }
        
    }
    


    /**
     * 查询订单
     * $params array 参数数组
     */
    public function queryOrder($params) {
        $params['merchantId'] = $this->merid;//商户号
        $params['timestamp'] = time();
        $key = $this->key;
        $signature = md5($params['merchantId'].$params['timestamp'].$key.$params['repay_id'].$params['paybill']);
        
        $info_arr = array(
            'merchantId' => $params['merchantId'],
            'timestamp' => $params['timestamp'],
            'signature' => $signature,
            'merOrderId' => $params['repay_id'],
            'orderId' => $params['paybill']
        );
        $check_json = json_encode($info_arr);
        $res_url = "http://gplus.treespaper.com/gplus-api/rest/payQuery?para=".$check_json;
        Logger::errorLog(print_r($res_url, true), 'result_weixinpay_return', 'weixinpay_notify');
        $result = Http::getCurl($res_url);
//        $result =  file_get_contents($res_url);
        Logger::errorLog(print_r($result, true), 'result_weixinpay_return', 'weixinpay_notify');
        return $result;
    }
    
    //回调验签
    public function getSign($msg_arr){
        if(empty($msg_arr)){
            return NULL;
        }
        $sign = md5($this->merid.$msg_arr['totalFee'].$msg_arr['merOrderId'].$msg_arr['orderId'].$msg_arr['payStatus']."".$this->key);
        return $sign;
    }

}

?>