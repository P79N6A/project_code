<?php
namespace app\commonapi;
/**
 * AES 算法类
 */
class AES {
    // CRYPTO_CIPHER_BLOCK_SIZE 32

    public static function encode($data, $key='defv',$mcrypt_rijndael = MCRYPT_RIJNDAEL_256) {
        $td = mcrypt_module_open($mcrypt_rijndael,'',MCRYPT_MODE_CBC,'');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);
        mcrypt_generic_init($td,$key,$iv);
        $encrypted = mcrypt_generic($td,$data);
        mcrypt_generic_deinit($td);
         
        $str =  $iv . $encrypted;
		return base64_encode($str);//自加有问题再去掉
		//return $str;
    }
     
    public static function decode($data, $key='defv',$mcrypt_rijndael = MCRYPT_RIJNDAEL_256) {//
    	$data = base64_decode($data);//自加有问题再去掉
        $td = mcrypt_module_open($mcrypt_rijndael,'',MCRYPT_MODE_CBC,'');
        if($mcrypt_rijndael == MCRYPT_RIJNDAEL_256){
            $iv = mb_substr($data,0,32,'latin1');
        }else{
            $iv = mb_substr($data,0,16,'latin1');
        }        
        mcrypt_generic_init($td,$key,$iv);
        if($mcrypt_rijndael == MCRYPT_RIJNDAEL_256){
            $data = mb_substr($data,32,mb_strlen($data,'latin1'),'latin1');
        }else{
            $data = mb_substr($data,16,mb_strlen($data,'latin1'),'latin1');
        }
        $data = mdecrypt_generic($td,$data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
         
        return trim($data);
    }

}
