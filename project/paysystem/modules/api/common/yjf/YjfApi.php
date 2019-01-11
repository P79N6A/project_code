<?php
namespace app\modules\api\common\yjf;
use app\common\Curl;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii;
/**
 * 易极付支付类
 */
class YjfApi {

    private $config;
    private $object;

    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
        //$this->object = new Util();
    }
    
    /**
     * 获取配置文件
     * @param  str $env
     * @param  str $aid
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
    *易极付公共参数
    *@$service 服务码
    */
    private function commonParams($service){
        $paramArr['service'] = $service;
        $paramArr['partnerId'] = $this->config['partnerId'];
        $paramArr['orderNo'] = date('YmdHis').rand(100000,999999);
        $paramArr['signType'] = $this->config['signType'];
        return $paramArr;
    }
    /**
    *四要素鉴权
    */
    public function verifyBankCard($postData){
        $paramArr['merchOrderNo'] = date('YmdHis').rand(100000,999999);
        $paramArr['outOrderNo'] = $postData['bind_no'];
        $paramArr['name'] = $postData['name'];
        $paramArr['certNo'] = $postData['idcard'];
        $paramArr['bankCardNo'] = $postData['cardno'];
        $paramArr['mobileNo'] = $postData['phone'];
        $paramArr['verifyCardType'] = 'FOUR_CAPTCHA_ELEMENT';
        $commonParamArr = $this->commonParams('verifyBankCard');
        $paramArr = array_merge($paramArr,$commonParamArr);
        
        $sign = $this->buildRequestMysign($paramArr);
        $paramArr['sign'] = $sign;
        $url = $this->config['apiUrl'];
        //var_dump($paramArr);DIE;
        $res = $this->HttpClientPost($url,$paramArr);
        $res = json_decode($res, true);
        //var_dump($res);DIE;
        // 返回结果
        Logger::dayLog('yjf', 'yjfapi/verifyBankCard', $url, $paramArr, $res);
        $response = $this->parseResult($res);
        return $response;
    }
    /**
     * 确认验证码
     *
     * @param $postData
     * @return void
     */
    public function smsVerifyCodeCheck($postData){
        $paramArr['merchOrderNo'] = date('YmdHis').rand(100000,999999);
        $paramArr['outOrderNo'] = $postData['bind_no'];
        $paramArr['smsVerifyCode'] = $postData['smscode'];
        $commonParamArr = $this->commonParams('smsVerifyCodeCheck');
        $paramArr = array_merge($paramArr,$commonParamArr);
        
        $sign = $this->buildRequestMysign($paramArr);
        $paramArr['sign'] = $sign;
        $url = $this->config['apiUrl'];
        $res = $this->HttpClientPost($url,$paramArr);
        $res = json_decode($res, true);
        //var_dump($res);die;
        // 返回结果
        Logger::dayLog('yjf', 'yjfapi/smsVerifyCodeCheck', $url, $paramArr, $res);
        $response = $this->parseResult($res);
        return $response;
    }
     /**
     * 查询四要素验卡结果
     *
     * @return void
     */
    public function queryCardVerify($postData){
        $paramArr['outOrderNo'] = $postData['outOrderNo'];
        $commonParamArr = $this->commonParams('verifyBankCardQuery');
        $paramArr = array_merge($paramArr,$commonParamArr);
        
        $sign = $this->buildRequestMysign($paramArr);
        $paramArr['sign'] = $sign;
        $url = $this->config['apiUrl'];
        $res = $this->HttpClientPost($url,$paramArr);
        $res = json_decode($res, true);
        //var_dump($res);die;
        // 返回结果
        Logger::dayLog('yjf', 'yjfapi/queryCardVerify', $url, $paramArr, $res);
        $response = $this->parseResult($res);
        return $response;
    }
    /**
     * 生成签名结果
     * @param $paramArr 要签名的数组
     * return 签名结果字符串
     */
    public function buildRequestMysign($paramArr) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($paramArr);
        ksort($para_filter);
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_filter);
        $prestr = stripslashes($prestr); 
        $mysign = $this->md5Sign($prestr, $this->config['apiKey']);
       // var_dump($mysign);die;
        return $mysign;
    }
    /**
    * 除去数组中的空值和签名参数
    * @param $para 签名参数组
    * return 去掉空值与签名参数后的新签名参数组
    */
    function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key === "sign" || $val === "")continue;
            else	$para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }
    /**
    * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
    * @param $para 需要拼接的数组
    * return 拼接完成以后的字符串
    */
    function createLinkstring($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            if($val===true) $val = "true";
            if($val===false) $val = "false";
            $arg.=$key."=".(string)$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        //file_put_contents("log.txt","转义前:".$arg."\n", FILE_APPEND);
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        //file_put_contents("log.txt","转义后:".$arg."\n", FILE_APPEND);
        return $arg;
    }
   /**
    * 签名字符串
    * @param $prestr 需要签名的字符串
    * @param $key 私钥
    * return 签名结果
    */
    function md5Sign($prestr, $key) {
        $prestr = $prestr.$key;
        return md5($prestr);
    }
    /*
     * 确认支付接口
     */

    public function pay($postData) {
        $paramArr['merchOrderNo'] = $postData['cli_orderid'];
        $paramArr['transScenario'] = 'CASH';
        $paramArr['transAmount'] = $postData['amount']/100;
        $paramArr['name'] = $postData['name'];
        $paramArr['certNo'] = $postData['idcard'];
        $paramArr['bankCardNo'] = $postData['cardno'];
        $paramArr['mobileNo'] = $postData['phone'];
        $paramArr['notifyUrl'] = $this->config['notifyUrl'];
        $commonParamArr = $this->commonParams('repay');
        $paramArr = array_merge($paramArr,$commonParamArr);
        
        $sign = $this->buildRequestMysign($paramArr);
        $paramArr['sign'] = $sign;
        $url = $this->config['apiUrl'];
        $res = $this->HttpClientPost($url,$paramArr);
        $res = json_decode($res, true);
        // 返回结果
        Logger::dayLog('yjf', 'yjfapi/pay', $url, $paramArr, $res);
        $response = $this->parseResult($res);
        return $response;
    }

    /**
     * @desc 提交数据
     * @param string $url
     * @param array $data
     * @return string
     */
    private function HttpClientPost($url,$data) {
        $timeLog = new \app\common\TimeLog();
        //$jsonString = json_encode($data);
        $postDataString = http_build_query($data);
        $curl = new Curl();
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->setOption(CURLOPT_TIMEOUT, 30);
        $content = '';
        $content = $curl->post($url, $postDataString);
        $status = $curl->getStatus();
        $timeLog->save('yjf', ['api', 'POST', $status, $url, $postDataString, $content]);
        if ($status != 200) {
            Logger::dayLog(
                "baofoo",
                "请求信息", $url, $data,
                "http状态", $status,
                "响应内容", $content
            );
        }
        return $content;
    }
   
    private function parseResult($res) {
        if(!$res)  return ['res_code' => "100401", 'res_data' => "请求出错，请检查网络"];
        $result = $this->verify($res);
        if (!$result) {
            return ['res_code' => 'sign_error', 'res_data' => "签名不正确"];
        }
        $is_success = ArrayHelper::getValue($res, 'success');
        $ret_code = ArrayHelper::getValue($res, 'resultCode');
        $ret_msg = ArrayHelper::getValue($res, 'resultMessage', '未知错误');
        if ($is_success == 'true') {           
            return ['res_code' => 0, 'res_data' => $res];
        } else {
            return ['res_code' => $ret_code, 'res_data' => $ret_msg];
        }
    }
    /*
     * 验证数据来源签名
     */
    public function verify($data) {
        if (!is_array($data) || !isset($data['sign'])) {
            return false;
        }
        $sign1 = $data['sign'];
        $sign = $this->buildRequestMysign($data);
        return $sign1 === $sign;
    }
}
