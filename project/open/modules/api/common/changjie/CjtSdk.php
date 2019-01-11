<?php

namespace app\modules\api\common\changjie;
use app\common\Logger;
	/*
	 * 畅捷代付款SDK
	 * 
	 */
class CjtSdk {

    private $xml;
    private $private_key;
    private $public_key;
    private $private_cert;
    private $url;

    function __construct($private_key_path,$public_key_path,$private_key_password,$url){

    // echo $private_key_path.'---'.$public_key_path.'---'.$private_key_password.'---'.$url;exit;

         // 初始化商户私钥
         $pkcs12 = file_get_contents($private_key_path);
         $private_arr = array();
         openssl_pkcs12_read($pkcs12, $private_arr, $private_key_password);
         $private_key = empty($private_arr['pkey'])?'':$private_arr['pkey'];
         $private_cert = empty($private_arr['cert'])?'':$private_arr['cert'];
         $public_key = file_get_contents($public_key_path);
         $this->private_key = $private_key;
         $this->private_cert = $private_cert;
         $this->public_key = $public_key;
         $this->url = $url;
         


    }
    public function createXml($params){
        $this->array2xml($params);
        $reponse = $this->sendRequest();
        return $reponse;
    }
    private function sendRequest(){
        // echo $this->xml.'<br><br><br><br>';
        $req_encrypted = $this->sign();
        $resp_encrypted = $this->httpRequest($this->url, $req_encrypted);
        if ($resp_encrypted === null) {
            return false;
        }
        $resp = $this->verify($resp_encrypted);
        if ($resp === null) {
            return false;
        }
	    return $resp;
    }

 
    function httpRequest($url, $post_data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($post_data !== null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        } else {
            curl_setopt($ch, CURLOPT_POST, 0);
        }

        // 测试环境不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        \app\common\Logger::dayLog(
                "cjt",
                "请求信息", $url, $post_data,
                "响应内容", $response
        );
        $curl_errno = (int)curl_errno($ch);
        curl_close($ch);

        if ($curl_errno !== CURLE_OK) {
            return false;
        }
         
        return $response;
    }
    /**
     * Undocumented function
     * 验签
     * @return void
     */
    private function verify($xml){
        $signature = null;
        $plain = preg_replace_callback('/\<SIGNED_MSG\>(.*)\<\/SIGNED_MSG\>/', function ($matches) use (&$signature){
            $signature = $matches[1];
            return '<SIGNED_MSG></SIGNED_MSG>';
        }, $xml);

        if ($signature === null || $plain === null || $plain === $xml) {
            return false;
        }

        // 服务器返回的是 Detached 格式，签名中不包含原文，这里把原文拼回去
        $smime = $this->wrapSMIME($plain, $signature);
        $openssl_cert = openssl_x509_read($this->public_key);

        $file_source = tempnam(sys_get_temp_dir(), 'CP');
        $file_cert = tempnam(sys_get_temp_dir(), 'CP');

        file_put_contents($file_source, $smime);
        file_put_contents($file_cert, $this->public_key);

        if (openssl_pkcs7_verify($file_source, PKCS7_BINARY | PKCS7_DETACHED | PKCS7_NOVERIFY, $file_source, array(), $file_cert) !== TRUE) // 只验签名，不验证书 
        {
            return false;
        }

        unlink($file_cert);
        unlink($file_source);

        openssl_x509_free($openssl_cert);
        
        return $plain;
    }
    /**
     * Undocumented function
     * 生成sign签名
     * @return void
     */
    public function sign(){
        $xml = $this->xml;
        if (strpos($xml, '<SIGNED_MSG></SIGNED_MSG>') === false) {
		    return false;
	    }
        // 验签的时候对方或许有可能需要拼接 S/MIME 文本，内容中的\r、\n或许会干扰，为了容错性强一点，干掉它们
	    $plain = str_replace([ "\r", "\n" ], [ '', '' ], $xml);

        $openssl_cert = openssl_x509_read($this->private_cert);
        $openssl_private_key = openssl_pkey_get_private($this->private_key);
        $file_source = tempnam(sys_get_temp_dir(), 'CP');
	    $file_signed = tempnam(sys_get_temp_dir(), 'CP');
        file_put_contents($file_source, $plain);
        // 服务器返回的是 PKCS7 Detached 格式，一致起见也发给服务器 Detached 格式
        openssl_pkcs7_sign($file_source, $file_signed,$openssl_cert, $openssl_private_key, array(), PKCS7_BINARY | PKCS7_DETACHED);
        $signed_str = file_get_contents($file_signed);
        unlink($file_signed);
	    unlink($file_source);
        openssl_x509_free($openssl_cert);
	    openssl_pkey_free($openssl_private_key);
        $signed_msg = str_replace("\n", '', explode("\n\n", $signed_str)[3]);
	    $signed = str_replace('<SIGNED_MSG></SIGNED_MSG>', '<SIGNED_MSG>' . $signed_msg . '</SIGNED_MSG>', $plain);
        // var_dump($signed);die;
        return $signed;
    }
    /**
     * 将原文和 Detached 格式的 PKCS7 签名包成一个 multipart/signed 格式的 S/MIME 文本
     *
     * @param $plain
     * @param $detached_pkcs7_signature
     * @return string
     */
    private function wrapSMIME($plain, $detached_pkcs7_signature)
    {
        $boundary = '----' . md5(microtime() . '__salt_xxx__' . mt_rand(100000000, 999999999));
        $sign = chunk_split($detached_pkcs7_signature, 64, "\r\n");
        return "MIME-Version: 1.0\r\n"
                . "Content-Type: multipart/signed; protocol=\"application/x-pkcs7-signature\"; micalg=\"sha1\";"
                    ." boundary=\"$boundary\"\r\n"
                . "\r\n"
                . "This is an S/MIME signed message\r\n"
                . "\r\n"
                . "--$boundary\r\n"
                . "$plain\r\n"
                . "--$boundary\r\n"
                . "Content-Type: application/x-pkcs7-signature; name=\"smime.p7s\"\r\n"
                . "Content-Transfer-Encoding: base64\r\n"
                . "Content-Disposition: attachment; filename=\"smime.p7s\"\r\n"
                . "\r\n"
                . "$sign\r\n"
                . "--$boundary--\r\n"
                . "\r\n";
    }
    public function array2xml($array,$encoding='utf-8') {
        $this->xml='<?xml version="1.0" encoding="'.$encoding.'"?>';
        $this->xml.=$this->_array2xml($array);
        return $this->xml;
    }
    private function _array2xml($array)
    {
        $xml='';
        foreach($array as $key=>$val){
            $key = strtoupper($key);
            if(is_numeric($key)){
                $key="item id=\"$key\"";
            }else{
                //去掉空格，只取空格之前文字为key
                list($key,)=explode(' ',$key);
            } 
            $xml.="<$key>";
            $xml.=is_array($val)?$this->_array2xml($val):$val;
            //去掉空格，只取空格之前文字为key
            list($key,)=explode(' ',$key);
            $xml.="</$key>";
        }
        return $xml;
    }
}
