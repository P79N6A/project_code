<?php
namespace app\common;
/**
* PHP版DES加解密类
* 可与java的DES(DESede/CBC/PKCS5Padding)加密方式兼容
*
*/
class CryptDes {
	/**
	 * des加密
	 *
	 * @param unknown_type $str
	 * @param unknown_type $key
	 * @param unknown_type $code
	 * @return unknown
	 */
    static public function encrypt($str,$key,$code='base64'){
    	$str = self::pkcs5Pad($str);
    	
    	$key = self::hex2bin($key) ;
    	$mode = 'cbc';
    	$iv = "\x01\x02\x03\x04\x05\x06\x07\x08";
    	$result = mcrypt_encrypt(MCRYPT_DES, $key, $str, $mode, $iv);
    	switch ($code){  
        case 'base64':  
            $ret = base64_encode($result);  
            break;  
        case 'hex':  
            $ret = bin2hex($result);  
            break;  
        case 'bin':  
        default:  
            $ret = $result;  
        }  
    	return strtoupper( $ret );
    }
    
    /**
     * des解密
     *
     * @param unknown_type $str
     * @param unknown_type $key
     * @param unknown_type $code
     * @return unknown
     */
    static public function decrypt($str,$key,$code='base64'){
    	switch ($code){  
        case 'base64':  
            $str = base64_decode($str);  
            break;  
        case 'hex':  
            $str = self::hex2bin($str);  
            break;  
        case 'bin':  
        default:  
        }  
        
    	$key = self::hex2bin($key) ;
    	$mode = 'cbc';
    	$iv = "\x01\x02\x03\x04\x05\x06\x07\x08";
    	$ret = @mcrypt_decrypt(MCRYPT_DES,$key,$str,$mode,$iv);
    	$ret = self::pkcs5Unpad($ret);
    	return $ret;
    	}
    
    	
    static private function pkcs5Unpad($text){
    	$pad = ord($text{strlen($text) - 1});
    	if ($pad > strlen($text)) return false;
    	if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
    	$ret = substr($text, 0, -1 * $pad);
    	return $ret;
    }
    
    
    static private function pkcs5Pad($text){
    	$blocksize = mcrypt_get_block_size(MCRYPT_DES, 'cbc');
    	$pad = $blocksize - (strlen($text) % $blocksize);
    	return $text . str_repeat(chr($pad), $pad);
    }
    
    
    static private function hex2bin($hex = false){
    	$ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
    	return $ret;
    }
  
}
?>