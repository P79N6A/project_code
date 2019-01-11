<?php
namespace app\modules\api\common\bbf;
/*
* 邦宝付代付款加解密
*/
 class bbfCrypt{
    
    private $private_key;
	private $public_key;
    
    /**
     * 
     * @Param  $private_key_path 商户证书路径（p12）
	 * @Param  $public_key_path 邦宝付公钥证书路径（cer）
     * @Param  $private_key_password 证书密码
     */
     function __construct($private_key_path,$public_key_path,$private_key_password){
        
         // 初始化商户私钥
        $pkcs12 = file_get_contents($private_key_path);
        $private_key = array();
        openssl_pkcs12_read($pkcs12, $private_key, $private_key_password);
        $this->private_key = $private_key["pkey"];
		///邦宝付公钥
		$keyFile = file_get_contents($public_key_path);
        $certificateCApemContent =  '-----BEGIN CERTIFICATE-----'.PHP_EOL
        .chunk_split(($keyFile), 64, PHP_EOL)
        .'-----END CERTIFICATE-----'.PHP_EOL;
        $this->public_key = openssl_get_publickey($certificateCApemContent);
        }

    
     // 私钥加密
    function encryptedByPrivateKey($data_content){
        //$data_content1 = sha1($data_content);
        $digest = openssl_digest($data_content,"sha1");
        $encrypted = "";
        openssl_sign($digest, $encryptData, $this->private_key,OPENSSL_ALGO_SHA1);
        $encrypted = base64_encode($encryptData);
        //$encrypted = strtoupper(bin2hex($encryptData));
        return $encrypted;
		}
		
	// 公钥解密
    function decryptByPublicKey($sign,$encryptedString){
        $res = openssl_verify($encryptedString,$sign,$this->public_key,OPENSSL_ALGO_SHA1);
        return $res;
    }

 }
?>