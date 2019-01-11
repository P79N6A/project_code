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
class Wechatpay {

    private $resHandler;
    private $reqHandler;
    private $pay;
    private $type;
    private $config;

    /*
     * 初始化数据
     */

    public function __construct($cfg, $type) {
        $this->type = $type;
        $this->config = include __dir__ . '/weixinapi/' . "{$cfg}.php";
        $this->resHandler = new ClientResponseHandler();
        $this->reqHandler = new RequestHandler();
        $this->pay = new PayHttpClient();
        $this->reqHandler->setGateUrl($this->config[$type . '_url']);
        $this->reqHandler->setKey($this->config[$type . '_key']);
    }

    private function getSubmitParams($params) {
        $params['service'] = $this->config[$this->type . '_service'];
        $params['mch_id'] = $this->config[$this->type . '_mchid'];
        $params['version'] = $this->config[$this->type . '_version'];
        $params['nonce_str'] = mt_rand(time(), time() + rand());
        $params['notify_url'] = $this->config[$this->type . '_notify_url'] . '?type=' . $this->type; //sdk
        if ($this->type == 'scan') {
            
        } else if ($this->type == 'sdk') {
            $params['limit_credit_pay'] = 0; //是否支持信用卡，1为不支持，0为支持
        } else if ($this->type == 'gzh') {
            $params['callback_url'] = $this->config['gzh_callback_url'];
        } else if($this->type == 'wrenewal'){
            $params['callback_url'] = $this->config['wrenewal_callback_url'];
        }
            
        return $params;
    }

    private function getQueryParams($params) {
        $params['service'] = $this->config[$this->type . '_service'];
        $params['mch_id'] = $this->config[$this->type . '_mchid'];
        $params['version'] = $this->config[$this->type . '_version'];
        $params['nonce_str'] = mt_rand(time(), time() + rand());
        return $params;
    }

    /**
     * 提交订单信息
     * $params array 参数数组
     * $type  支付类型 ，scan  扫码 sdk移动端 gzh 公众号支付 
     */
    public function submitOrderInfo($params) {
        $params = $this->getSubmitParams($params);
        $this->reqHandler->setReqParams($params);
        $this->reqHandler->createSign(); //创建签名
        //转成xml格式
        $data = Utils::toXml($this->reqHandler->getAllParameters());
        $this->pay->setReqContent($this->reqHandler->getGateURL(), $data);
        if ($this->pay->call()) {
            $this->resHandler->setContent($this->pay->getResContent());
            $this->resHandler->setKey($this->reqHandler->getKey());
            Logger::dayLog('wechatpay', 'submitOrderInfo', $this->type, $params, $this->resHandler->getAllParameters());
            $mm = $this->resHandler->isTenpaySign();
            if ($mm) {
//                    Logger::dayLog('wechatpay1', 'submitOrderInfo', 1111);
                //当返回状态与业务结果都为0时才返回支付二维码，其它结果请查看接口文档
                if ($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0) {
                    if ($this->type == 'scan') {
                        return ['status' => 0, 'url' => $this->resHandler->getParameter('code_img_url')];
                    } elseif ($this->type == 'gzh') {
                        return ['status' => 0, 'url' => $this->config['gzh_url_return'] . '?token_id=' . $this->resHandler->getParameter('token_id')];
                    } elseif ($this->type == 'sdk') {
                        return ['status' => 0, 'token_id' => $this->resHandler->getParameter('token_id')];
                    } elseif ($this->type == 'renewal') {
                        return ['status' => 0, 'token_id' => $this->resHandler->getParameter('token_id')];
                    } elseif ($this->type == 'wrenewal') {
                        return ['status' => 0, 'url' => $this->config['wrenewal_url_return'] . '?token_id=' . $this->resHandler->getParameter('token_id')];
                    }
                } else {
//                    Logger::dayLog('wechatpay1', 'submitOrderInfo', 2222);
                    return ['status' => 1000, 'msg' => $this->resHandler->getParameter('err_msg')];
                }
            }
            return ['status' => 1001, 'msg' => $this->resHandler->getParameter('message')];
        } else {
            return ['status' => 1002, 'msg' => ' Error Info:' . $this->pay->getErrInfo()];
        }
    }

    /**
     * 查询订单
     * $params array 参数数组
     * $type  支付类型 ，scan  扫码 sdk移动端 gzh 公众号支付 
     */
    public function queryOrder($params) {
        $params = $this->getQueryParams($params);
        $this->reqHandler->setReqParams($params);
        $reqParam = $this->reqHandler->getAllParameters();
        if (empty($reqParam['transaction_id']) && empty($reqParam['out_trade_no'])) {
            return ['status' => 500, 'msg' => '请输入商户订单号,威富通订单号!'];
        }
        $this->reqHandler->createSign(); //创建签名
        $data = Utils::toXml($this->reqHandler->getAllParameters());
        $this->pay->setReqContent($this->reqHandler->getGateURL(), $data);
        if ($this->pay->call()) {
            $this->resHandler->setContent($this->pay->getResContent());
            $this->resHandler->setKey($this->reqHandler->getKey());
            if ($this->resHandler->isTenpaySign()) {
                $res = $this->resHandler->getAllParameters();
                //支付成功会输出更多参数，详情请查看文档中的7.1.4返回结果
                return ['status' => 0, 'data' => $res];
            }
            return ['status' => 1003, 'msg' => $this->resHandler->getParameter('message')];
        } else {
            return ['status' => 1004, 'msg' => ' Error Info:' . $this->pay->getErrInfo(),];
        }
    }

    /**
     * 提供给威富通的回调方法
     * $xml xml 微信端返回的参数
     * $type  支付类型 ，scan  扫码 sdk移动端 gzh 公众号支付 renewal app微信续期  wrenewal公众号微信续期
     */
    public function notify($xml) {
        $this->resHandler->setContent($xml);
        $this->resHandler->setKey($this->config[$this->type . '_key']);
        if (!$this->resHandler->isTenpaySign()) {
            return 'verifyFaild';
        }
        if ($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0) {
            Logger::errorLog(print_r($xml, true), $this->type . 'notifysuccess', 'yyywechatpay');
            return 'success';
        } else {
            return 'faild';
        }
    }

}

?>