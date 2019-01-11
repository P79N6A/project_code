<?php
namespace app\modules\api\common\rongbao;

use app\common\Curl;
use app\common\Logger;


/**
 * @desc 融宝API;
 * @author lubaba
 */
class RbWithholdApi {

    private $config = null;

    public $errinfo; 

    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
    }
    

    private function getConfig($cfg) {
        $configPath = __DIR__ . "/config/{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }
    /**
     * 生成一个随机的字符串作为AES密钥
     *
     * @param number $length
     * @return string
     */
    private function generateAESKey($length=16){
		$baseString = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$AESKey = '';
		$_len = strlen($baseString);
		for($i=1;$i<=$length;$i++){
			$AESKey .= $baseString[rand(0, $_len-1)];
		}
		return $AESKey;
	}
    /**
     * 创建签名
     *
     * @param array $paramArr
     * @return string 
     */
    public function createSign($paramArr) {
        $sign ='';
        ksort($paramArr);
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $sign .= $key.'='.$val.'&';
            }
        }
        $sign = substr ( $sign,0,(strlen ( $sign )-1));
        $sign = md5($sign.$this->config['apiKey']);
        return $sign;
    }

    /**
     * RSA加密AESkey
     *
     * @param  $encryptKey
     * @return string
     */
    private function RSAEncryptkey($encryptKey){

        $public_key= file_get_contents($this->config['reapalPublicKey']); 

        $pu_key = openssl_pkey_get_public($public_key);//这个函数可用来判断公钥是否是可用的 

        openssl_public_encrypt($encryptKey,$encrypted,$pu_key);//公钥加密  

        return base64_encode($encrypted);
    }

    /**
     * RSA解密AESkey
     *
     * @param  $encryptKey
     * @return string
     */
    public function RSADecryptkey($encryptKey){

        $private_key= file_get_contents($this->config['merchantPrivateKey']); 

        $pi_key =  openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id

        openssl_private_decrypt(base64_decode($encryptKey),$decrypted,$pi_key);//私钥解密
        return $decrypted;
	}

    /**
     * AES加密数据
     *
     * @param  $encryptKey AESKey
     * @param  $paramArr  待加密数据
     * @return string
     */
    private function AESEncryptRequest($encryptKey,$paramArr){
        $paramStr = json_encode($paramArr);
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $paramStr = $this->pkcs5_pad($paramStr, $size);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $encryptKey, $iv);
        $data = mcrypt_generic($td, $paramStr);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }

    private function pkcs5_pad ($text, $blocksize) {
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}
    /**
     * AES解密数据
     *
     * @param  $encryptKey AESKey
     * @param  $paramArr  待解密数据
     * @return string
     */
    public function AESDecryptResponse($encryptKey,$data){
		$decrypted= mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$encryptKey,base64_decode($data),MCRYPT_MODE_ECB);
		$dec_s = strlen($decrypted);
		$padding = ord($decrypted[$dec_s-1]);
		$decrypted = substr($decrypted, 0, -$padding);
		return $decrypted;
		
	}

    /**
     * 格式化请求数据
     *
     * @param $params
     * @return string
     */
    private function getRequestParamString($params) {
        $params_str = '';
        foreach ( $params as $key => $value ) {
            $params_str .= ($key . '=' . (!isset ( $value ) ? '' : urlencode( $value )) . '&');
        }
        return substr ($params_str, 0, strlen ( $params_str ) - 1 );
    }
    /**
     * http 请求
     *
     * @param $params
     * @return string
     */
    private function sendHttpRequest($params,$url){
        $opts = $this->getRequestParamString($params);
        //echo $opts;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//不验证HOST
        //curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array (
                'Content-type:application/x-www-form-urlencoded;charset=UTF-8' 
        ) );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        $html = curl_exec ($ch);
        curl_close ($ch);
        return $html;
    }

    /**
     * 对外提供接口
     *
     * @param  $paramArr
     * @param  $flag
     * @return void
     */
    public function send($paramArr,$flag){
        //创建签名
        $sign = $this->createSign($paramArr);
        $paramArr['sign_type'] = $this->config['sign_type'];
	    $paramArr['sign'] = $sign;
        //生成AESkey
        $generateAESKey = $this->generateAESKey();
        //RSA加密key
        $encryptkey = $this->RSAEncryptkey($generateAESKey);
        //AES加密数据
        $data = $this ->AESEncryptRequest($generateAESKey,$paramArr);
        //请求参数
        $requestData = [
            'merchant_id' => $this->config['merchant_id'],
            'encryptkey' => $encryptkey,
            'data' => $data,
        ];
        $url = $this->config['rongpay_api']."/".$flag;
        Logger::dayLog('RbWithhold','RbWithhold/send', '请求数据',$paramArr,$generateAESKey, $url, $requestData);
        $resp = $this->sendHttpRequest($requestData,$url);
        $resData = null;
        if($resp){
            //解密数据
            $response = json_decode($resp,true);
            //解密得到AESKey
            $encryptkey = $this->RSADecryptkey($response['encryptkey']);
            //解密得到数据
            $resData = $this->AESDecryptResponse($encryptkey,$response['data']);
            $resData = json_decode($resData,true);
            Logger::dayLog('RbWithhold','RbWithhold/send', '返回数据',$paramArr, $url, $resData);
        }
        return $resData;
    }
    /**
     * 对外提供接口
     *
     * @param  $paramArr
     * @param  $url
     * @return void
     */
    public function sendquery($paramArr,$url){
        //创建签名
        $sign = $this->createSign($paramArr);
        $paramArr['sign_type'] = $this->config['sign_type'];
	    $paramArr['sign'] = $sign;
        //生成AESkey
        $generateAESKey = $this->generateAESKey();
        //RSA加密key
        $encryptkey = $this->RSAEncryptkey($generateAESKey);
        //AES加密数据
        $data = $this ->AESEncryptRequest($generateAESKey,$paramArr);
        //请求参数
        $requestData = [
            'merchant_id' => $this->config['merchant_id'],
            'encryptkey' => $encryptkey,
            'data' => $data,
            'version'=>'1.0'
        ];
        Logger::dayLog('RbWithhold','RbWithhold/sendquery', '请求数据',$paramArr,$generateAESKey, $url, $requestData);
        $resp = $this->sendHttpRequest($requestData,$url);
        $resData = null;
        if($resp){
            //解密数据
            $response = json_decode($resp,true);
            //解密得到AESKey
            $encryptkey = $this->RSADecryptkey($response['encryptkey']);
            //解密得到数据
            $resData = $this->AESDecryptResponse($encryptkey,$response['data']);
            $resData = json_decode($resData,true);
            Logger::dayLog('RbWithhold','RbWithhold/sendquery', '返回数据',$paramArr, $url, $resData);
        }
        return $resData;
    }

}
