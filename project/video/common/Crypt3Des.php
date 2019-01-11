<?php
namespace app\common;
class Crypt3Des {
//    static public $key = "9964DYByKL967c3308imytCB"; // 非先花商户的key
//    static public $xianhuakey = "PSCQtU2egwedbhYXTU9IrZ0O"; // 先花商户的key
    
    //数据加密
    static public function encrypt($input=null,$key=null) {
        $size = mcrypt_get_block_size ( MCRYPT_3DES, 'ecb' );
        $input = self::pkcs5_pad ( $input, $size );
        $key = str_pad ( $key, 24, '0' );
        $td = mcrypt_module_open ( MCRYPT_3DES, '', 'ecb', '' );
        $iv = @mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
        @mcrypt_generic_init ( $td, $key, $iv );
        $data = mcrypt_generic ( $td, $input );
        mcrypt_generic_deinit ( $td );
        mcrypt_module_close ( $td );
        $data = base64_encode ( $data );
        return $data;
    }
    
    //数据解密
    static public function decrypt($encrypted=null,$key=null) {
        $encrypted = base64_decode ( $encrypted );
        $key = str_pad ( $key, 24, '0' );
        $td = mcrypt_module_open ( MCRYPT_3DES, '', 'ecb', '' );
        $iv = @mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
        $ks = mcrypt_enc_get_key_size ( $td );
        @mcrypt_generic_init ( $td, $key, $iv );
        $decrypted = mdecrypt_generic ( $td, $encrypted );
        mcrypt_generic_deinit ( $td );
        mcrypt_module_close ( $td );
        $y = self::pkcs5_unpad ( $decrypted );
        return $y;
    } 
    
   static public function pkcs5_pad($text, $blocksize) {
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }
     
   static public function pkcs5_unpad($text) {
        $pad = ord ( $text {strlen ( $text ) - 1} );
        if ($pad > strlen ( $text )) {
            return false;
        }
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad) {
            return false;
        }
        return substr ( $text, 0, - 1 * $pad );
    }

    static function PaddingPKCS7($data) {
        $block_size = mcrypt_get_block_size ( MCRYPT_3DES, MCRYPT_MODE_CBC );
        $padding_char = $block_size - (strlen ( $data ) % $block_size);
        $data .= str_repeat ( chr ( $padding_char ), $padding_char );
        return $data;
    }
    
}
?>