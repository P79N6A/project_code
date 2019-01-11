<?php

namespace app\modules\api\common\cjt;
use app\common\Logger;
use app\common\Xmlparse;
class Util {
    private $xml;
    private $config;
    private $private_key;
    private $private_cert;
    private $public_key;
    function __construct($config){
        $this->config = $config;
         // 初始化商户私钥
         $pkcs12 = file_get_contents($config['private_key_path']);
         $private_arr = array();
         openssl_pkcs12_read($pkcs12, $private_arr, $config['private_key_password']);
         $private_key = empty($private_arr['pkey'])?'':$private_arr['pkey'];  
         //var_dump($private_arr);     
         $private_cert = empty($private_arr['cert'])?'':$private_arr['cert'];       
         $public_key = file_get_contents($config['public_key_path']);
         $this->private_key = $private_key;
         $this->private_cert = $private_cert;
         $this->public_key = $public_key;

    }
    public function createXml($params){
        $this->array2xml($params);
        $reponse = $this->sendRequest();
        var_dump($reponse);
        return $reponse;
    }
    private function sendRequest(){
        $req_encrypted = $this->sign();
        $resp_encrypted = $this->HttpClientPost($this->config['action_url'], $req_encrypted);
        if ($resp_encrypted === null) {
            return false;
        }

        $resp = $this->verify($resp_encrypted);
        if ($resp === null) {
            Logger::dayLog("cjt","验签失败", $url, $req_encrypted);
            return false;
        }
        $resarr = $this->xml2array($resp);
	    return $resarr;
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
        curl_setopt($ch,CURLOPT_HTTPHEADER, array(
				'Content-Type: application/xml')
                );
        // // 测试环境不验证证书
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);

        //curl_setopt($ch, CURLOPT_VERBOSE, 1);
        //curl_setopt($ch, CURLOPT_CAINFO,$this->config['cacert']); //设置为证书的路径
        // curl_setopt($ch, CURLOPT_SSLKEY,$this->config['private_key']); //私钥存放路径
        // curl_setopt($ch, CURLOPT_SSLCERT,$this->config['public_cert']);//证书存放路径
        // curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->config['private_key_password']);//证书密码

        $response = curl_exec($ch);
        $response = '<?xml version="1.0" encoding="UTF-8"?><MESSAGE><INFO><TRX_CODE>G10015</TRX_CODE><VERSION>01</VERSION><MERCHANT_ID>cp2016051890757</MERCHANT_ID><REQ_SN>20170717173628cjt596c851c6ee48</REQ_SN><RET_CODE>2000</RET_CODE><ERR_MSG>同步代收业务超时，请在运营平台查看结果</ERR_MSG><TIMESTAMP>20170717174334</TIMESTAMP><SIGNED_MSG>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</SIGNED_MSG></INFO><BODY><RET_CODE>0001</RET_CODE><RET_MSG>同步代收业务超时，请在运营平台查看结果</RET_MSG><ERR_CODE></ERR_CODE><ERR_MSG></ERR_MSG><CHARGE></CHARGE><CORP_ACCT_NO>cp2016051890757</CORP_ACCT_NO><CORP_ACCT_NAME>刘鑫</CORP_ACCT_NAME><ACCOUNT_NO>6225880176551936</ACCOUNT_NO><ACCOUNT_NAME>刘鑫</ACCOUNT_NAME><AMOUNT>101400</AMOUNT><CORP_FLOW_NO></CORP_FLOW_NO><SUMMARY>无</SUMMARY><POSTSCRIPT></POSTSCRIPT></BODY></MESSAGE>';
        Logger::dayLog("cjt","请求信息", $url, $post_data, "响应内容", $response);
        curl_close($ch);   
        return $response;
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
        $curl->setOption(CURLOPT_HTTPHEADER, array(
				'Content-Type: application/xml')
                );
        $content = '';
        $content = $curl->post($url, $data);
        $status = $curl->getStatus();
        Logger::dayLog("cjt","请求信息", $url, $data,"http状态", $status,"响应内容", $content);
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
