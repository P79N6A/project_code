<?php
namespace app\modules\api\common\cjxy;
class Util {
    private $config;
    function __construct($config){
        $this->config = $config;
    }
	
    //对于某些特殊参数的加密
    public function rsaPublicEncrypt($data){
		$server_key = $this->config['server_key'];
        $public_key = '-----BEGIN PUBLIC KEY-----'.PHP_EOL
        .chunk_split(($server_key), 64, PHP_EOL)
        .'-----END PUBLIC KEY-----'.PHP_EOL;
        openssl_public_encrypt($data,$encrypted,$public_key);
        $encrypted =  base64_encode($encrypted);
        return $encrypted;
    }
    /**
     * rsa签名
     * $args 签名字符串数组
     * return 签名结果
     */
    function rsaSign($args) {
        $args=array_filter($args);//过滤掉空值
        ksort($args);
        $query =   '';
        foreach($args as $k=>$v){
            if($k=='SignType'){
                continue;
            }
            if($query){
                $query  .=  '&'.$k.'='.$v;
            }else{
                $query =  $k.'='.$v;
            }
        }
        //这地方不能用 http_build_query  否则会urlencode
        //$query=http_build_query($args);
        $private_key= $this->config['private_key'];  //私钥
        $pkeyid = openssl_get_privatekey($private_key);
        openssl_sign($query, $sign, $pkeyid);
        openssl_free_key($pkeyid);
        //var_dump($sign);
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 验证签名
	 * @param $args 需要签名的数组
	 * @param $sign 签名结果
	 * return 验证是否成功
	 */
    function rsaVerify($args, $sign) {
        $args=array_filter($args);//过滤掉空值
        ksort($args);
        $query =   '';
        foreach($args as $k=>$v){
            if($k=='SignType' || $k=='Sign' || $k=='sign_type' || $k=='sign'){
                continue;
            }
            if($query){
                $query  .=  '&'.$k.'='.$v;
            }else{
                $query =  $k.'='.$v;
            }
        }
        //这地方不能用 http_build_query  否则会urlencode
        $sign = base64_decode($sign);
        $server_key= $this->config['server_key'];
		$public_key =  '-----BEGIN PUBLIC KEY-----'.PHP_EOL
        .chunk_split(($server_key), 64, PHP_EOL)
        .'-----END PUBLIC KEY-----'.PHP_EOL;
        $pkeyid = openssl_get_publickey($public_key);
        $verify = "";
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
}
