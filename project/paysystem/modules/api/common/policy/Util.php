<?php

namespace app\modules\api\common\policy;
class Util {
    /**
	 * 对参数进行加密
	 *
	 * @param $params 待加密参数        	
	 * @param $publicKey 对端的公钥        	
	 */
	public static function encrypt($params, $publicKey) {
		$public_key = self::getPublicKey($publicKey);
		$_rawData = json_encode ( self::filterParam ( $params ) );
		
		$_encryptedList = array ();
		$_step = 117;
		
		for($_i = 0, $_len = strlen ( $_rawData ); $_i < $_len; $_i += $_step) {
			$_data = substr ( $_rawData, $_i, $_step );
			$_encrypted = '';
			
			openssl_public_encrypt ( $_data, $_encrypted, $public_key );
			$_encryptedList [] = ($_encrypted);
		}
		$_data = base64_encode ( join ( '', $_encryptedList ) );
		return $_data;
	}
	
	/**
	 * 对参数进行加签
	 *
	 * @param $params 待加签参数        	
	 * @param $privateKey 自己的私钥        	
	 */
	public static function sign($params, $privateKey) {
		ksort ( $params );
		$_signStr = json_encode ( $params );
		$_signStr = stripslashes ( $_signStr );
		$privateKey = self::getPrivateKey($privateKey);
		$_privateKeyId = openssl_get_privatekey ( $privateKey );
		openssl_sign ( $_signStr, $_data, $_privateKeyId );
		openssl_free_key ( $_privateKeyId );
		$_data = base64_encode ( $_data );		
		return $_data;
	}
	
	/**
	 *
	 * @param unknown $params        	
	 * @param unknown $sign        	
	 * @param unknown $publicKey        	
	 */
	public static function checkSign($params, $sign, $publicKey) {
		$public_key = self::getPublicKey($publicKey);
		$_params = self::filterParam ( $params );
		ksort ( $_params );
		
		$_publicKeyId = openssl_get_publickey ( $public_key );
		
		$_data = json_encode ( $_params, JSON_UNESCAPED_UNICODE );
		$_data = stripslashes ( $_data );
		$_result = openssl_verify ( $_data, base64_decode ( $sign ), $_publicKeyId, "sha1WithRSAEncryption" );
		openssl_free_key ( $_publicKeyId );
		return $_result;
	}
	
	/**
	 * 对参数进行解密
	 *
	 * @param $encryptedData 待解密参数        	
	 * @param $privateKey 自己的私钥        	
	 */
	public static function decrypt($encryptedData, $privateKey) {
		$privateKey = self::getPrivateKey($privateKey);
		$_encryptedData = base64_decode ( $encryptedData );
		
		$_decryptedList = array ();
		$_step = 128;
		if (strlen ( $privateKey ) > 1000) {
				$_step = 256;
		}
		for($_i = 0, $_len = strlen ( $_encryptedData ); $_i < $_len; $_i += $_step) {
			$_data = substr ( $_encryptedData, $_i, $_step );
			$_decrypted = '';
			openssl_private_decrypt ( $_data, $_decrypted, $privateKey );
			$_decryptedList [] = $_decrypted;
		}
		
		return join ( '', $_decryptedList );
	}
	
	/**
	 * 保证只传有值的参数
	 *
	 * @param unknown $param        	
	 */
	public static function filterParam($params) {
		$_result = array ();
		foreach ( $params as $_key => $_value ) {
			// 没有值的
			if (empty ( $_value ) && $_value != 0) {
				continue;
			}
			
			if (is_array ( $_value )) {
				$_result [$_key] = json_encode ( $_value );
			} else {
				$_result [$_key] = $_value ? $_value : '';
			}
		}
		return $_result;
	}
	public static function getPublicKey($cert_path) {
        $pkcs12 = file_get_contents($cert_path);
        return $pkcs12;
	}
	public static function getPrivateKey($cert_path) {
		$private_key = file_get_contents($cert_path);
        return $private_key;
	}
	public static function md5Sign($data,$signKey){
		$sign = md5($data.$signKey);
		return $sign;
	}
	/**
     * 合成sign
     * @param $params   参数数组
     * @return string
     */
    public static function _paramsToSign($params,$_appKey){
        if(isset($params['sign'])){
            unset($params['sign']);
        }
        if(isset($params['sign_type'])){
            unset($params['sign_type']);
        }
        ksort($params);
        $queryStr=self::_myHttpBuildQuery($params);
        $queryStr=$queryStr . $_appKey;
        $queryStr=md5($queryStr);
        return $queryStr;
	}
	/**
     * 合成queryString
     * @param $params
     * @return string
     */
    public static function _myHttpBuildQuery($params){
        $queryStr='';
        foreach($params as $paramsK=>$paramsV){
            $queryStr.=$paramsK.'='.$paramsV.'&';
        }
        $queryStr=trim($queryStr,'&');

        return $queryStr;
    }
}