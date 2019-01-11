<?php
namespace app\modules\api\common\bank;
class Rsa {
	private $config;
	public function __construct($env){
		/**
		 * 账号配置文件
		 */
		$configPath = __DIR__ . "/config/config.{$env}.php";
		if( !file_exists($configPath) ){
			throw new \Exception($configPath."配置文件不存在",6000);
		}
		$this->config = include( $configPath );
	}
	
	public  function encode($data){
		$priKey = file_get_contents($this->config['privateKey']);
		$res = openssl_get_privatekey($priKey);
		openssl_sign($data, $sign, $res, OPENSSL_ALGO_MD5);
		openssl_free_key($res);
		$sign = base64_encode($sign);
		return $sign;
	}

	public  function decode($data, $sign){
		$pubKey = file_get_contents($this->config['publicKey']);
		$res = openssl_get_publickey($pubKey);
		$result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_MD5);
		openssl_free_key($res);
		return $result;
	}

}


