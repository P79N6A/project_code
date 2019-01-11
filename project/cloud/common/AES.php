<?php
namespace app\common;
/**
 * AES 算法类
 */
class AES {
    // CRYPTO_CIPHER_BLOCK_SIZE 32

    public static function encode($data, $key='defv') {
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256,'',MCRYPT_MODE_CBC,'');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);
        mcrypt_generic_init($td,$key,$iv);
        $encrypted = mcrypt_generic($td,$data);
        mcrypt_generic_deinit($td);
         
        $str =  $iv . $encrypted;
		return base64_encode($str);//自加有问题再去掉
		//return $str;
    }
     
    public static function decode($data, $key='defv') {
    	$data = base64_decode($data);//自加有问题再去掉
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256,'',MCRYPT_MODE_CBC,'');
        $iv = mb_substr($data,0,32,'latin1');
        mcrypt_generic_init($td,$key,$iv);
        $data = mb_substr($data,32,mb_strlen($data,'latin1'),'latin1');
        $data = mdecrypt_generic($td,$data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
         
        return trim($data);
    }

}
