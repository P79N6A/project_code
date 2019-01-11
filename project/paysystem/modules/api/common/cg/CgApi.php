<?php

namespace app\modules\api\common\cg;
use app\common\Curl;
use app\common\ApiSign;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
/**
 *存管支付类
 */
class CgApi {

    private $config;
    private $object;

    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
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
     * 存管发短信接口
     * @param [type] $oPayorder
     * @return void
     */
    public function sendSms($data){
        if(!$this->config) return [];
        $apiSign = new ApiSign();
        $signRes = $apiSign->signData($data);
        $curl = new Curl();
        $response = $curl->post($this->config['send_url'],$signRes);
        Logger::dayLog("cg/sms","请求信息", $this->config['send_url'], $data,"响应内容", $response);
        return $response;
    }

    /**
     * 存管支付
     *
     * @return void
     */
    public function confirmPay($data){
        if(!$this->config) return [];
        $apiSign = new ApiSign();
        $data['notifyUrl'] = $this->config['callback_url'];
        $signRes = $apiSign->signData($data);
        $curl = new Curl();
        $response = $curl->post($this->config['action_url'],$signRes);
        Logger::dayLog("cg/pay","请求信息", $this->config['action_url'], $data,"响应内容", $response);
        $response = json_decode($response, true);//转为数组
        return $response;
        
    }

 /**
     * 存管支付-new
     *
     * @return void
     */
    public function confirmPaynew($data){
        if(!$this->config) return [];
        $orderid = base64_encode('x'.$data['orderId'].'h');
        $data['notifyUrl'] = $this->config['callback_url'];
        $data['retUrl'] = $this->config['getback_url'].'?id='.$orderid.'&aid='.$data['source'];
        $apiSign = new ApiSign();
        $signRes = $apiSign->signData($data);
        $curl = new Curl();
        $response = $curl->post($this->config['action_url_new'],$signRes);
        Logger::dayLog("cg/pay_new","请求信息", $this->config['action_url_new'], $data,"响应内容", $response);
        $response = json_decode($response, true);//转为数组
        return $response;
        
    }
    
    
}
