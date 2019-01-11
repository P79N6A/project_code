<?php
/**
 * 畅捷支付常用函数，如果RAS加密，签名加密等等
 * @author gaolian
 *
 */
use app\common\Curl;
use app\common\Logger;

class Chanpay {
	/**
	 * 签名
	 * $args 签名字符串数组
	 *
	 * return 签名结果
	 */
	function rsaSign($args) {
		$args=array_filter($args);//过滤掉空值
		ksort($args);
		$query  =   '';
		foreach($args as $k=>$v){
			if($k=='sign_type'){
				continue;
			}
			if($query){
				$query  .=  '&'.$k.'='.$v;
			}else{
				$query  =  $k.'='.$v;
			}
		}
		//这地方不能用 http_build_query  否则会urlencode
		//$query=http_build_query($args);
		$path   =   chanpay_rsa_private_key;  //私钥地址
		$private_key= file_get_contents($path);
		$pkeyid = openssl_get_privatekey($private_key);
		openssl_sign($query, $sign, $pkeyid);
		openssl_free_key($pkeyid);
		$sign = base64_encode($sign);
		return $sign;
	}
	
	
	/**
	 * 验证签名
	 *
	 * @param $args 需要签名的数组
	 * @param $sign 签名结果
	 * return 验证是否成功
	 */
	function rsaVerify($args, $sign) {
		$args=array_filter($args);//过滤掉空值
		ksort($args);
		$query  =   '';
		foreach($args as $k=>$v){
			if($k=='sign_type' || $k=='sign'){
				continue;
			}
			if($query){
				$query  .=  '&'.$k.'='.$v;
			}else{
				$query  =  $k.'='.$v;
			}
		}
		//这地方不能用 http_build_query  否则会urlencode
		$sign = base64_decode($sign);
		$path   =   chanpay_rsa_public_key;  //公钥地址
		$public_key= file_get_contents($path);
		$pkeyid = openssl_get_publickey($public_key);
		if ($pkeyid) {
			$verify = openssl_verify($query, $sign, $pkeyid);
			openssl_free_key($pkeyid);
		}
		if($verify == 1){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * GET请求接口
	 * @param unknown $url
	 * @param unknown $params
	 * @return unknown|NULL
	 */
	function curlGet($url, $params = array()){
		$curl = new Curl();
		$curl -> setOption(CURLOPT_CONNECTTIMEOUT,10);
		$curl -> setOption(CURLOPT_TIMEOUT,10);
		$content = $curl -> get($url, $params);
		$status  = $curl -> getStatus();
		if( $status == 200 ){
			return $content;
		}else{
			Logger::dayLog(
			"risk",
			"请求信息",$url,$params,
			"http状态",$status,
			"响应内容",$content
			);
			return null;
		}
	
	}
	
	/**
	 * 通过公钥进行rsa加密
	 *
	 * @param type $name
	 *        	Descriptiondata
	 *        	$data 进行rsa公钥加密的数必传
	 *        	$pu_key 加密用的公钥 必传
	 *          $_input_charset 字符集编码
	 * @return 加密好的密文
	 */
	function Rsa_encrypt($data) {
		$encrypted = "";
		$public_key = chanpay_rsa_public_key;
		$cert = file_get_contents ($public_key );
		$pu_key = openssl_pkey_get_public ( $cert );
		openssl_public_encrypt ( $data, $encrypted, $pu_key ); // 公钥加密
		$encrypted = base64_encode ( $encrypted ); // 进行编码
		return $encrypted;
	}
}
?>