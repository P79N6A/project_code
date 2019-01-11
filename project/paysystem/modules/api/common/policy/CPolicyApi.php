<?php

namespace app\modules\api\common\policy;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\common\CryptAES;
use app\modules\api\common\policy\Util;
/**
 * 众安保险api
 */
class CPolicyApi {

    public $config;
    private $oUtil;
    private $_timestamp;
    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
        $this->_timestamp = $this->withMicrosecond();
    }
    
    /**
     * 获取配置文件
     * @param  str $cfg
     * @return   []
     */
    private function getConfig($cfg) {
        $configPath = dirname(__DIR__) . "/policy/config/{$cfg}.php";
        Logger::dayLog('policy/api',$cfg,$configPath);
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }
    /**
     * Undocumented function
     * 核保接口
     * @param [type] $bizParams
     * @return void
     */
    public function call($bizParams){      
        $bizParams['campaignDefId']  = $this->config['campaignDefId'];
        $bizParams['packageDefId']   = $this->config['packageDefId'];
        $serviceName = $this->config['checkServiceName'];
		return $this->handlePolicyParams($serviceName,$bizParams);
    }
    /**
     * Undocumented function
     * 出单接口
     * @param [type] $bizParams
     * @return void
     */
    public function apply($bizParams){
        $serviceName = $this->config['applyServiceName'];
		return $this->handlePolicyParams($serviceName,$bizParams);
    }
    /**
     * Undocumented function
     * 退单接口
     * @param [type] $bizParams
     * @return void
     */
    public function cancel($bizParams){
        $serviceName = $this->config['cancelServiceName'];
		return $this->handlePolicyParams($serviceName,$bizParams);
    }
    /**
     * Undocumented function
     * 收银台支付接口
     * @param [type] $postdata
     * @return void
     */
    public function pay($postdata){
        $postdata['request_charset']    = 'UTF-8';
        $postdata['sign_type']          = 'MD5';
        $postdata['src_type']           = 'mobile';
        $postdata['subject']            = '借款人意外保险';
        $postdata['pay_channel']        = 'alipay^wxpay';
        $postdata['merchant_code']      = $this->config['merchant_code'];
        $postdata['notify_url']         = $this->config['notify_url'];
        $postdata['return_url']         = $this->config['return_url'];
        $postdata['merchant_code']      = $this->config['merchant_code'];
        $redirectPayUrl = $this->createPayUrl($postdata);
        Logger::dayLog('policy/redirectPayUrl','跳转地址',$redirectPayUrl,$postdata);
        return $redirectPayUrl;
    }
    /**
     * Undocumented function
     * 支付订单查询
     * @param [type] $orderNo
     * @return void
     */
    public function payQuery($orderNo){
        if(empty($orderNo)) return false;
        $bizParams = [
            'orderNo'   => $orderNo
        ];
        $serviceName = $this->config['queryPayServiceName'];
		return $this->handlePolicyParams($serviceName,$bizParams);
    }
    /**
     * 获取支付url（支付前使用）
     * @param $params
     * @return string
     */
    private function createPayUrl($params){
        if(empty($this->config)){
            return false;
        }
        $params['sign']=Util::_paramsToSign($params,$this->config['app_key']);

        $queryStr=Util::_myHttpBuildQuery($params);
        $_apiUrl = $this->config['pay_url'];
        return $_apiUrl.'?'.$queryStr;
    }
    /**
     * Undocumented function
     * 处理请求数据以及签名验签
     * @param [type] $serviceName
     * @param [type] $bizParams
     * @return void
     */
    private function handlePolicyParams($serviceName,$bizParams){
        $_bizContent = Util::encrypt ( $bizParams, $this->config['_publicKey'] );
        $_allParams = array (
            "serviceName"   => $serviceName,
            "appKey"        => $this->config['_appKey'],
            "format"        => $this->config['_format'],
            "signType"      => $this->config['_signType'],
            "charset"       => $this->config['_charset'],
            "version"       => $this->config['_version'],
            "timestamp"     => $this->_timestamp,
            "bizContent"    => $_bizContent 
        );
        $_signRequest = Util::sign ( $_allParams, $this->config['_privateKey'] );
        $_allParams ["sign"] = $_signRequest;
		$_result = $this->doCurlPost ( $this->config['_url'], $_allParams, true );
        Logger::dayLog('policy/api','响应结果',$_result,'请求参数',$bizParams,$_allParams);
        if(empty($_result)){
            return false;
        }
		$_signResponse = $_result ["sign"];
		unset ( $_result ["sign"] );
		
		$_signCheckRst = Util::checkSign ( $_result, $_signResponse, $this->config['_publicKey'] );
		if ($_signCheckRst != 1) {
            Logger::dayLog('policy/api','本地验签失败','响应结果',$_result,'sign',$_signResponse,'请求参数',$bizParams,$_allParams);
		}
		
		$_decryptedData = Util::decrypt ( $_result ["bizContent"], $this->config['_privateKey'] );
        $_result ["bizContent"] = isset ( $_result ["bizContent"] ) ? json_decode ( $_decryptedData, true ) : false;
        Logger::dayLog('policy/api',$serviceName,'众安接口响应结果',$_result,'请求参数',$bizParams,$_allParams);

        //var_dump($_result);die;
		return $_result;
    }
    /**
	 * 获取当前时间戳：yyyyMMddhhmmssSSS
	 *
	 * @return string
	 */
	private function withMicrosecond() {
		list ( $usec, $sec ) = explode ( " ", microtime () );
		return date ( "YmdHis" ) . sprintf ( "%03d", intval ( $usec * 1000 ) );
    }
    public  function doCurlPost($url, $params, $resultToJson = false) {
		$_curl = curl_init ();
		if (stripos ( $url, "https://" ) !== FALSE) {
			curl_setopt ( $_curl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt ( $_curl, CURLOPT_SSL_VERIFYHOST, false );
		}
		
		if (is_string ( $params )) {
			$_strPOST = $params;
		} else {
			$_strPOST = http_build_query ( $params );
		}
		
		curl_setopt ( $_curl, CURLOPT_URL, $url );
		curl_setopt ( $_curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $_curl, CURLOPT_POST, true );
		curl_setopt ( $_curl, CURLOPT_POSTFIELDS, $_strPOST );
		curl_setopt ( $_curl, CURLOPT_TIMEOUT, 5 );
		
		$_content = curl_exec ( $_curl );
		$_status = curl_getinfo ( $_curl );
		curl_close ( $_curl );
		
		if (intval ( $_status ["http_code"] ) == 200) {
			return $resultToJson ? json_decode ( $_content, true ) : $_content;
		}
		
		return false;
    }
    /**
     * Undocumented function
     * 推送 验签
     * @param [type] $data
     * @param [type] $sign
     * @return void
     */
    public function notifyVerify($data,$sign){
        $signKey = $this->config['signKey'];
        $my_sign = Util::md5Sign($data,$signKey);
        return $my_sign==$sign;
    }
    public function decodeAes($data){
        $secretKey = $this->config['secretKey'];
        $aes = new CryptAES();
        $aes->set_key($secretKey);
        $aes->require_pkcs5();
        $_data = $aes->decrypt($data);
        return $_data;
    }
    /**
     * 验证签名（支付回调使用）
     * @param $params
     * @return bool
     */
    public  function validationSign($params){
        $requestSign=$params['sign'];
        $mySign=Util::_paramsToSign($params,$this->config['app_key']);
        if($requestSign!==$mySign){
            return false;
        }
        return true;
    }
}
