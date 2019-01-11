<?php
namespace app\modules\api\common\bangbf;
	/*
	 * 邦宝付代付款加解密
	 */
 class bbfCrypt{
    
     public $private_key;//私钥
     public $merchantCert;//商户证书
     public $public_key;//邦宝付公钥
    
    /**
     * 
     * @Param  $private_key_path 商户证书路径（p12）
	 * @Param  $public_key 邦宝付公钥16进制格式
     * @Param  $private_key_password 证书密码
     */
     function __construct($private_key_path,$public_key_path,$private_key_password){
        
         // 初始化商户私钥
         $pkcs12 = file_get_contents($private_key_path);
         $private_key = array();
         openssl_pkcs12_read($pkcs12, $private_key, $private_key_password);
         $priKey = empty($private_key['pkey'])?'':$private_key['pkey'];
         $cert = empty($private_key['cert'])?'':$private_key['cert'];
         $cert = $this->getCert($cert);
         $this->priKey = $priKey;
         $this->merchantCert = $cert;
         $public_key = file_get_contents($public_key_path);
         $public_key = $this->getPublicKey($public_key);
         //var_dump($public_key);die;
         //$public_key = strtoupper(bin2hex($public_key));

         //var_dump($public_key);
		 //邦宝付公钥
         $this->public_key = $public_key;
        //  var_dump($this->public_key);
		 
        }

     /**
      * @param $cert
      * @return mixed
      * 把标准化证书格式转成字符串，并转成大写 16进制
      */
     private function getCert($cert){
         $cert = str_replace([
             '-----BEGIN CERTIFICATE-----',
             '-----END CERTIFICATE-----',
             "\n"
         ],'', $cert);
         $cert =base64_decode($cert);
         $cert = strtoupper(bin2hex($cert));
         return $cert;
     }
     private function getPublicKey($public_key){

         //$public_key = base64_encode($public_key);
         $public_key='-----BEGIN CERTIFICATE-----'.PHP_EOL
         .chunk_split(($public_key), 64, PHP_EOL)
         .'-----END CERTIFICATE-----'.PHP_EOL;
         //var_dump($public_key);die;
         return openssl_get_publickey($public_key);
     }
     
     /**RSA签名
      * 签名结果需转成16进制
      * return Sign签名
      */

     public function Rsasign($data) {

         $oRsa = new \app\common\Rsa;
         $sign =  $oRsa -> sign($data,$this->priKey,'hex');
         $sign = strtoupper($sign);
         return $sign;
     }

     /**
      * @param $data
      * @param $sign //邦宝付签名，需转化成二进制
      * @return bool
      */
     public function verify($data,$sign){

         $sign = $this -> _hex2bin($sign);
         $ret = false;
             switch (openssl_verify($data, $sign, $this->public_key,OPENSSL_ALGO_SHA1)) {
                 case 1 :
                     $ret = true;
                     break;
                 case 0 :
                 case -1 :
                 default :
                     $ret = false;
             }
         return $ret;

     }
     private function _hex2bin($hex = false) {
         $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
         return $ret;
     }
 }
?>