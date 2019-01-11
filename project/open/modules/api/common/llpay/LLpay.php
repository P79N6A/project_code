<?php
namespace app\modules\api\common\llpay;
use app\common\Logger;
use yii\helpers\ArrayHelper;

/**
 * 连连代付api接口;
 */
class LLpay {
    private $config;
    private $oLianService;
    private $oLianNotify;
    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
        $this->oLianService = new LLpaySubmit($this->config);
        $this->oLianNotify = new LLpayNotify($this->config);

    }
    //申请出款
    public function payApply($payData){
        $data = [
            "oid_partner"=>trim($this->config['oid_partner']),
            "sign_type" => trim($this->config['sign_type']),
            "api_version"=>$this->config['api_version'],
            "no_order"=>$payData['no_order'],
            "dt_order"=>$payData['dt_order'],
            "money_order"=>$payData['money_order'],
            "acct_name"=>$payData['acct_name'],
            "card_no"=>$payData['card_no'],
            "flag_card"=>$payData['flag_card'],
            "notify_url"=>$this->config['notify_url'],

        ];
        //var_dump($data);die;
        //对参数排序加签名
        $sortPara = $this->oLianService->buildRequestPara($data);
        //传json字符串
        $json = json_encode($sortPara);
        $pay_load = $this->oLianService->llpay_encrypt($json,$this->config['LIANLIAN_PUBLICK_KEY']);
        $parameterRequest = array (
            "oid_partner" => trim($this->config['oid_partner']),
            "pay_load" => $pay_load
        );
        $res = $this->oLianService->buildRequestJSON($parameterRequest,$this->config['pay_url']);
        $res = json_decode($res, true);

        // 返回结果
        Logger::dayLog('llpay', 'llpay/payApply', $this->config['pay_url'], 'sortPara',$sortPara, 'parameterRequest',$parameterRequest,$res);
        return $res;
    }

    /**
     * @param $no_order 订单流水号
     */
    public function queryPay($no_order){
        $data = [
            "oid_partner"=>trim($this->config['oid_partner']),
            "sign_type" => trim($this->config['sign_type']),
            "api_version"=>$this->config['api_version'],
            "no_order"=>$no_order,
        ];
        //对参数排序加签名
        $sortPara = $this->oLianService->buildRequestPara($data);
        $res = $this->oLianService->buildRequestJSON($sortPara,$this->config['query_url']);
        $res = json_decode($res, true);
        // 返回结果
        Logger::dayLog('llpay', 'llpay/queryPay', $this->config['query_url'], 'sortPara',$sortPara,$res);
        return $res;
    }

    /**
     * @param $no_order 订单流水号
     * @param $confirm_code 确认码
     */
    public function confirmPay($no_order,$confirm_code){
        $data = [
            "oid_partner"=>trim($this->config['oid_partner']),
            "sign_type" => trim($this->config['sign_type']),
            "api_version"=>$this->config['api_version'],
            "no_order"=>$no_order,
            "confirm_code"=>$confirm_code,
            "notify_url"=>$this->config['notify_url'],
        ];
        //对参数排序加签名
        $sortPara = $this->oLianService->buildRequestPara($data);
        //传json字符串
        $json = json_encode($sortPara);
        $pay_load = $this->oLianService->llpay_encrypt($json,$this->config['LIANLIAN_PUBLICK_KEY']);
        $parameterRequest = array (
            "oid_partner" => trim($this->config['oid_partner']),
            "pay_load" => $pay_load
        );
        $res = $this->oLianService->buildRequestJSON($parameterRequest,$this->config['confirm_url']);
        $res = json_decode($res, true);

        // 返回结果
        Logger::dayLog('llpay', 'llpay/confirmPay', $this->config['confirm_url'], 'sortPara',$sortPara, 'parameterRequest',$parameterRequest,$res);
        return $res;
    }
    /**
     * @return mixed验证 签名是否正确
     */
    public function verifyNotify($str){
        $result = $this->oLianNotify->verifyNotify($str);
        return $result;
    }

    /**
     * @return mixed返回异步通知参数
     */
    public function getNotifyResp(){
        $res = $this->oLianNotify->notifyResp;
        return $res;
    }
    public function buildRequestPara($data){
        //对参数排序加签名
        $sortPara = $this->oLianService->buildRequestPara($data);
        return $sortPara;
    }
    public function testNotify($data){
        $res = $this->oLianService->buildRequestJSON($data,$this->config['notify_url']);
        $res = json_decode($res, true);
        // 返回结果
        Logger::dayLog('llpay', 'llpay/testNotify', $this->config['notify_url'], 'data',$data,$res);
        return $res;
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



}