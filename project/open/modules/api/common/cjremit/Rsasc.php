<?php
namespace app\modules\api\common\cjremit;

class Rsasc{


    /**
     * 功能：  签名
     * author:
     * $args 签名字符串数组
     * return 签名结果
     */
    function rsaSign($args,$private_key_path) {
//        $args=array_filter($args);//过滤掉空值
        ksort($args);
        $query  =   '';
        foreach($args as $k=>$v){
            if($k=='SignType' ){
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
        //      $path   =   $this->rsa_private_key;  //私钥地址   需要改成你自己的私钥位置
        $path   =  $private_key_path;  //私钥地址   需要改成你自己的私钥位置
        $private_key= file_get_contents($path);
        $pkeyid = openssl_get_privatekey($private_key);
        openssl_sign($query, $sign, $pkeyid);
        openssl_free_key($pkeyid);
        $sign = base64_encode($sign);
        return $sign;
    }
    /**
     * 功能：加密
     * @param $args 加密原文数组
     * return 密文数组
     */
    function publicRsaSign($args,$public_key_path) {
//        $args=array_filter($args);//过滤掉空值
//        $path   =   $this->rsa_public_key;  //私钥地址   需要改成你自己的私钥位置
//        $path   =  "./rsa_public_key.pem";  //公钥地址
//        $path   =  "./rsa_public_key.pem";  //私钥地址
        $public_key= file_get_contents($public_key_path);
        foreach($args as $k=>$v){
            openssl_public_encrypt($v,$encryptStr,$public_key);
            $args[$k] = base64_encode($encryptStr);
        }
        return $args;
    }

    /**
     * 功能： 验证签名
     * @param $args 需要签名的数组
     * @param $sign 签名结果
     * @param $public_key_path 公钥地址
     * return 验证是否成功
     */
    function rsaVerify($args, $sign ,$public_key_path) {
        $args=array_filter($args);//过滤掉空值
        ksort($args);
        $query  =   '';
        foreach($args as $k=>$v ){
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
 //       $path   =   $this->rsa_public_key;  //公钥地址      需要改成你自己的公钥位置
//        $path   =  "./rsa_public_key.pem";  //公钥地址

        $public_key= file_get_contents($public_key_path);
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
}
?>
