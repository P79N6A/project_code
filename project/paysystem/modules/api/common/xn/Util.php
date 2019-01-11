<?php

namespace app\modules\api\common\xn;
use app\common\Logger;
use app\common\Xmlparse;
class Util {
    private $xml;
    private $config;
    private $private_key;
    private $private_cert;
    private $public_key;
    function __construct(){
        

    }
    
    public function clientPost($url,$data){
        return $this->HttpClientPost($url,$data);
    }
   
     /**
     * @desc 提交数据
     * @param string $url
     * @param string $data
     * @return string
     */
    private function HttpClientPost($url,$data) {
        $curl = new \app\common\Curl();
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->setOption(CURLOPT_TIMEOUT, 30);
        $curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        // $curl->setOption(CURLOPT_SSLKEY, $this->config['private_key']);
        // $curl->setOption(CURLOPT_SSLCERT, $this->config['public_cert']);
        // $curl->setOption(CURLOPT_SSLCERTPASSWD, $this->config['private_key_password']);
       /* $curl->setOption(CURLOPT_HTTPHEADER, array(
				'Content-Type: application/xml')
                );*/
        $content = '';
        $content = $curl->post($url, $data);
        $status = $curl->getStatus();
        return $content;
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

        if (openssl_pkcs7_verify($file_source, PKCS7_BINARY | PKCS7_DETACHED | PKCS7_NOVERIFY,	// 只眼签名，不验证书
                $file_source, array(), $file_cert) !== TRUE) 
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
    private function sign(){
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
        //var_dump($signed);die;
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
    private function array2xml($array,$encoding='utf-8') {
        $this->xml='<?xml version="1.0" encoding="'.$encoding.'"?>';
        $this->xml.=$this->_array2xml($array);
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
    /**
	 * 将xml解析成数组
	 */
	private function xml2array( &$resxml ){
		$xmlParse = new Xmlparse(true);
		$arr = $xmlParse -> parse($resxml);
		if( !is_array($arr) || empty($arr) ){
			return null;
		}
		return $arr;
	}
}
