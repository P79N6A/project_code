<?php
namespace app\common;
/**
 * RSA算法类
 * 最大支持明文加解密127字节，切记切记!

 * 签名及密文编码：base64字符串/十六进制字符串/二进制字符串流
 * 填充方式: PKCS1Padding（加解密）/NOPadding（解密）
 *
 * 如密钥长度为1024 bit，则加密时数据需小于128字节，加上PKCS1Padding本身的11字节信息，所以明文需小于117字节
 *
 */
class RSA {
    public $pad0 = true;
    /**
     * 生成rsa串
     */
    public function genKeyPair(){
        $configargs = array(
            "config" => __DIR__."/openssl.cnf" ,
        );
    
        $res = openssl_pkey_new($configargs);
        // Get private key
        openssl_pkey_export($res, $privkey, null, $configargs);
        // Get public key
        $pubkey = openssl_pkey_get_details($res);
        $pubkey = $pubkey["key"];
    
        return ['pubKey'=>$pubkey, 'priKey'=>$privkey];
    }
    //private $pubKey = null;
    //private $priKey   = null;
    /**
     * @param string 公钥（验证签名和加密时需要传入）
     */
    public function setPubKey($public_key){
        if( strpos($public_key, "PUBLIC KEY") === false ){
            $public_key = chunk_split($public_key,64,"\n");
            $public_key = "-----BEGIN PUBLIC KEY-----\n".$public_key."-----END PUBLIC KEY-----\n";
        }
        return openssl_get_publickey($public_key);
    }
    /**
     * @param string 私钥文件（签名和解密时传入）
     */
    public function setPrikey($private_key){
        if( strpos($private_key, "PRIVATE KEY") === false ){
            $private_key = chunk_split($private_key,64,"\n");
            $private_key = "-----BEGIN RSA PRIVATE KEY-----\n".$private_key."-----END RSA PRIVATE KEY-----\n";
        }
        return openssl_get_privatekey($private_key);
    }

    /**
     * 签名: 私钥加密，公钥匹配
     * sign verify 配对，即是签名认证： 常用于各种接口，服务端存储公钥，客户端存储私钥
     */ 
    /**
     * 生成签名
     *
     * @param string 签名材料
     * @param string 签名编码（base64/hex/bin）
     * @return 签名值
     */
    public function sign($data, $private_key, $code = 'base64') {
        $private_key = $this->setPrikey($private_key);
        $ret = false;
        if (openssl_sign($data, $ret, $private_key,OPENSSL_ALGO_SHA1)) {
            $ret = $this -> _encode($ret, $code);
        }
        return $ret;
    }

    /**
     * 验证签名
     *
     * @param string 签名材料
     * @param string 签名值
     * @param string 签名编码（base64/hex/bin）
     * @return bool
     */
    public function verify($data, $sign, $public_key, $code = 'base64') {
        $public_key = $this->setPubKey($public_key);
        $ret = false;
        $sign = $this -> _decode($sign, $code);
        if ($sign !== false) {
            switch (openssl_verify($data, $sign, $public_key)) {
                case 1 :
                    $ret = true;
                    break;
                case 0 :
                case -1 :
                default :
                    $ret = false;
            }
        }
        return $ret;
    }

    //***********************************start 私钥加密, 公钥解密*****************************/
    /**
     * 私钥加密
     *
     * @param string 明文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING）
     * @return string 密文
     */
    public function encryptByPrivate($data, $private_key, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING) {
        $private_key = $this->setPrikey($private_key);
        $ret = false;
        if (!$this -> _checkPadding($padding, 'en'))
            $this -> _error('padding error');
        if (openssl_private_encrypt($data, $result, $private_key, $padding)) {
            $ret = $this -> _encode($result, $code);
        }
        return $ret;
    }
    /**
     * 公钥解密
     *
     * @param string 密文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
     * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block）
     * @return string 明文
     */
    public function decryptByPublic($data, $public_key, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING, $rev = false) {
        $public_key = $this->setPubKey($public_key);    
        $ret = false;
        $data = $this -> _decode($data, $code);
        if (!$this -> _checkPadding($padding, 'de'))
            $this -> _error('padding error');
        if ($data !== false) {
            if (openssl_public_decrypt($data, $result, $public_key, $padding)) {
                $ret = $rev ? rtrim(strrev($result), "\0") : '' . $result;
            }
        }
        return $ret;
    }
    
    /**
     * 公钥加密 加密超长
     *
     * @param string 明文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING）
     * @return string 密文
     */
    public function encrypt128ByPrivate($data, $private_key, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING) {
        $private_key = $this->setPrikey($private_key);
        if (!$this -> _checkPadding($padding, 'en')){
            $this -> _error('padding error');
        }
        //117
        $ciphertext = str_split($data, 117);
        
        if($this->pad0){
            // 最后填充\0
            $maxKey = count($ciphertext) - 1;
            $ciphertext[$maxKey] = str_pad($ciphertext[$maxKey], 117, chr(0), STR_PAD_LEFT);
         }

        // 每117位解析
        $plaintext = '';
        foreach ($ciphertext as $c) {
            if (openssl_private_encrypt($c, $result, $private_key, $padding)) {
                $plaintext .= $result;
            }
        }
        $ret = $this -> _encode($plaintext, $code);
        return $ret;
    }
    /**
     * 解密内容超长的字符串。一般都是双方约定的，每段长度限定在128位左右
     *
     * @param string 密文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
            //$padding = OPENSSL_PKCS1_PADDING;
            //$padding = OPENSSL_SSLV23_PADDING;
            //$padding = OPENSSL_PKCS1_OAEP_PADDING;
            //$padding = OPENSSL_NO_PADDING;
     * 
     * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block）
     * @return string 明文
     */
    public function decrypt128ByPublic($data, $public_key, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING, $rev = false) {
        $public_key = $this->setPubKey($public_key);  
        $ret = false;
        $data = $this -> _decode($data, $code);
        if (!$this -> _checkPadding($padding, 'de')){
            $this -> _error('padding error');
        }
        if ($data == false) {
            return false;
        }

        $ciphertext = str_split($data, 128);
        
        if($this->pad0){
            // 最后填充\0
            $maxKey = count($ciphertext) - 1;
            $ciphertext[$maxKey] = str_pad($ciphertext[$maxKey], 128, chr(0), STR_PAD_LEFT);
        }

        // 每128位解析
        $plaintext = '';
        foreach ($ciphertext as $c) {
            if (openssl_public_decrypt($c, $result, $public_key, $padding)) {
                $ret = $rev ? rtrim(strrev($result), "\0") : '' . $result;
            }
            
            $plaintext .= $ret;
        }

        return $plaintext;
    }
    
    /***********start  公钥加密，私钥解密 encrypt decrypt 配对，用于加解密 *****************/
    /**
     * 公钥加密
     *
     * @param string 明文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING）
     * @return string 密文
     */
    public function encryptByPublic($data, $public_key, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING) {
        $public_key = $this->setPubKey($public_key);
        $ret = false;
        if (!$this -> _checkPadding($padding, 'en'))
            $this -> _error('padding error');
        if (openssl_public_encrypt($data, $result, $public_key, $padding)) {
            $ret = $this -> _encode($result, $code);
        }
        return $ret;
    }

    /**
     * 私钥解密
     *
     * @param string 密文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
            //$padding = OPENSSL_PKCS1_PADDING;
            //$padding = OPENSSL_SSLV23_PADDING;
            //$padding = OPENSSL_PKCS1_OAEP_PADDING;
            //$padding = OPENSSL_NO_PADDING;
     * 
     * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block）
     * @return string 明文
     */
    public function decryptByPrivate($data, $private_key, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING, $rev = false) {
        $private_key = $this->setPrikey($private_key);  
        $ret = false;
        $data = $this -> _decode($data, $code);
        if (!$this -> _checkPadding($padding, 'de'))
            $this -> _error('padding error');
        if ($data !== false) {
            if (openssl_private_decrypt($data, $result, $private_key, $padding)) {
                $ret = $rev ? rtrim(strrev($result), "\0") : '' . $result;
            }
        }
        return $ret;
    }
    
    /**
     * 公钥加密 加密超长
     *
     * @param string 明文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING）
     * @return string 密文
     */
    public function encrypt128ByPublic($data, $public_key, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING) {
        $public_key = $this->setPubKey($public_key);
        if (!$this -> _checkPadding($padding, 'en')){
            $this -> _error('padding error');
        }
        //117
        $ciphertext = str_split($data, 117);
        
        if($this->pad0){
            // 最后填充\0
            $maxKey = count($ciphertext) - 1;
            $ciphertext[$maxKey] = str_pad($ciphertext[$maxKey], 117, chr(0), STR_PAD_LEFT);
        }
        
        // 每117位解析
        $plaintext = '';
        foreach ($ciphertext as $c) {
            if (openssl_public_encrypt($c, $result, $public_key, $padding)) {
                $plaintext .= $result;
            }
        }
        $ret = $this -> _encode($plaintext, $code);
        return $ret;
    }
    /**
     * 解密内容超长的字符串。一般都是双方约定的，每段长度限定在128位左右
     *
     * @param string 密文
     * @param string 密文编码（base64/hex/bin）
     * @param int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
            //$padding = OPENSSL_PKCS1_PADDING;
            //$padding = OPENSSL_SSLV23_PADDING;
            //$padding = OPENSSL_PKCS1_OAEP_PADDING;
            //$padding = OPENSSL_NO_PADDING;
     * 
     * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block）
     * @return string 明文
     */
    public function decrypt128ByPrivate($data, $private_key, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING, $rev = false) {
        $private_key = $this->setPrikey($private_key);  
        $ret = false;
        $data = $this -> _decode($data, $code);
        if (!$this -> _checkPadding($padding, 'de')){
            $this -> _error('padding error');
        }
        if ($data == false) {
            return false;
        }

        $ciphertext = str_split($data, 128);
        
        if($this->pad0){
            // 最后填充\0
            $maxKey = count($ciphertext) - 1;
            $ciphertext[$maxKey] = str_pad($ciphertext[$maxKey], 128, chr(0), STR_PAD_LEFT);
        }

        // 每128位解析
        $plaintext = '';
        foreach ($ciphertext as $c) {
            if (openssl_private_decrypt($c, $result, $private_key, $padding)) {
                $ret = $rev ? rtrim(strrev($result), "\0") : '' . $result;
            }
            
            $plaintext .= $ret;
        }

        return $plaintext;
    }
    /***********end  公钥加密，私钥解密 encrypt decrypt 配对，用于加解密 *****************/



    // 私有方法
    /**
     * 检测填充类型
     * 加密只支持PKCS1_PADDING
     * 解密支持PKCS1_PADDING和NO_PADDING
     *
     * @param int 填充模式
     * @param string 加密en/解密de
     * @return bool
     */
    private function _checkPadding($padding, $type) {
        if ($type == 'en') {
            switch ($padding) {
                case OPENSSL_PKCS1_PADDING :
                    $ret = true;
                    break;
                default :
                    $ret = false;
            }
        } else {
            switch ($padding) {
                case OPENSSL_PKCS1_PADDING :
                case OPENSSL_NO_PADDING :
                    $ret = true;
                    break;
                default :
                    $ret = false;
            }
        }
        return $ret;
    }

    private function _encode($data, $code) {
        switch (strtolower($code)) {
            case 'base64' :
                $data = base64_encode('' . $data);
                break;
            case 'hex' :
                $data = bin2hex($data);
                break;
            case 'bin' :
            default :
        }
        return $data;
    }

    private function _decode($data, $code) {
        switch (strtolower($code)) {
            case 'base64' :
                $data = base64_decode($data);
                break;
            case 'hex' :
                $data = $this -> _hex2bin($data);
                break;
            case 'bin' :
            default :
        }
        return $data;
    }


    private function _hex2bin($hex = false) {
        $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
        return $ret;
    }

    /**
     * 自定义错误处理
     */
    private function _error($msg) {
        $msg .= "\n";
        file_put_contents(__DIR__.'/RSA/rsa.log', $msg);
    }

}
/**
 * 

$rsa = new RSA(); 
$rsa -> setPubKey( file_get_contents( __DIR__  . '/RSA/public_key' ) );
$rsa -> setPrikey( file_get_contents( __DIR__  . '/RSA/private_key') );

// 签名的使用
$sign = $rsa->sign($str); 
$isOk = $rsa->verify($str, $sign); 
var_dump($sign, $isOk); 

// 加解密的使用
$crypt = $rsa->encrypt($str); 
$str = $rsa->decrypt($crypt); 
var_dump($crypt, $str);

*/