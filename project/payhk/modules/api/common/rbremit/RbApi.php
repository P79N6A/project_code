<?php

namespace app\modules\api\common\rbremit;

use app\common\Logger;
use Exception;

/**
 * 融宝代付api接口;
 * 这个是这个包对外开放的唯一接口.
 * 流程如下
 * 新用户: 签约授权-支付 4.2-4.5
 * 老用户: 授权-支付 4.3-4.5
 */
class RbApi {

    private $config;
    private $oUtil;

    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
        $this->oUtil = new Util($this->config);
    }

    /**
     * 1.1 代付提交
     * 加密描述
     * 首先使用原始数据进行MD5加密，然后加上sign、sign_type进入原始数组里面，在进行rsa加密
     * rsa加密时候返回数据包括merchant_id、encryptkey、data、version
     * encryptkey是随机生成一个字符串，使用对方公钥进行加密，
     * data是使用随机的那个字符串作为key进行加密
     * @param  [] $payData
     * @return [res_code, res_data]
     */
    public function payApply($data) {
        //加密签名
        $res_data = $this->buildSign($data);
        $data['sign'] = $res_data;
        $data['sign_type'] = 'MD5';

        //发送请求请求rsa加密
        $post_data = $this->buildRsa($data);

        $return_data = $this->sendUrl($post_data, 'agentpay/pay');

        $re_back_data = '';
        try {
            $result = json_decode($return_data, true);
            $response = $this->decResult($result);
            $re_back_data = json_decode($response);
        } catch (\Exception $e) {
            
        }

        Logger::dayLog('rongbao/pay', '响应结果', $data, $return_data, $re_back_data);

        return $re_back_data;
    }

    /**
     * 1.2 批次查询
     * @param  [] $payData
     * @return [res_code, res_data]
     */
    private function batchpayqueryApply($payData) {
        //组装数据
        $data = $this->getbatchpayqueryApply($payData);
        //加密签名
        $res_data = $this->buildSign($data);
        $data['sign'] = $res_data;
        $data['sign_type'] = 'MD5';
        Logger::dayLog('rongbao_select_batch', $data);
        //请求ras加密
        $post_data = $this->buildRsa($data);
        //发送请求
        $return_data = $this->sendUrl($post_data, 'agentpay/batchpayquery');
        $result = json_decode($return_data, true);
        Logger::dayLog('ttt', $result);
        //结果解密
        $response = $this->decResult($result);
        $re_back_data = json_decode($response);

        return $re_back_data;
    }

    /**
     * 1.3 单笔查询
     * @param  [] $payData
     * @return [res_code, res_data]
     */
    public function singlepayqueryApply($data) {
        //加密签名
        $res_data = $this->buildSign($data);
        $data['sign'] = $res_data;
        $data['sign_type'] = 'MD5';
        //请求ras加密
        $post_data = $this->buildRsa($data);
        //发送请求
        $return_data = $this->sendUrl($post_data, 'agentpay/singlepayquery');

        $re_back_data = '';
        try {
            $result = json_decode($return_data, true);
            $response = $this->decResult($result);
            $re_back_data = json_decode($response);
        } catch (\Exception $e) {
            
        }

        Logger::dayLog('rongbao/query', '响应结果', $data, $return_data, $re_back_data);

        return $re_back_data;
    }

    /**
     * 1.2 批量查询数据格式
     * @param  [] $payData
     * @return []
     */
    private function getbatchpayqueryApply($payData) {
        $data = [
            'trans_time' => date('Y-m-d', strtotime($payData['create_time'])), //交易时间
            //            'notify_url' => $this->config['batchpay_notify_url'], //异步地址
            'batch_no' => $payData['batch_no'],
            "next_tag" => 1,
            "charset" => "UTF-8",
        ];
        return $data;
    }

    /**
     * 1.3 单笔查询数据格式
     * @param  [] $payData
     * @return []
     */
    private function getsinglepayqueryApply($payData) {
        $data = [
            'trans_time' => date('Y-m-d', strtotime($payData['remit_time'])), //交易时间
            //            'notify_url' => $this->config['batchpay_notify_url'], //异步地址
            'batch_no' => $payData['batch_no'],
            "charset" => "UTF-8",
            "detail_no" => $payData['id'],
        ];
        return $data;
    }

    public function decResult($data) {
        return $this->oUtil->decryRequest($data);
    }

    private function sendUrl($data, $url) {
        return $this->oUtil->sendHttpRequest($data, $this->config['dsfUrl'] . $url);
    }

    private function buildRsa($data) {
        return $this->oUtil->buildRequestParaToString($data);
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
            throw new Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }

    public function getConf() {
        return $this->config;
    }

    /**
     * 生成要请求给连连支付的参数数组
     * @param $data 请求前的参数数组
     * @return 要请求的参数数组
     */
    public function buildSign($data) {
        return $this->oUtil->createSign($data);
    }

}
