<?php
namespace app\modules\api\common\rbremit;
/* *
 * Util
 * 功能：融宝代付接口请求提交类
 * 详细：构造融宝代付各接口表单HTML文本，获取远程HTTP数据
 * 版本：1.1
 * 日期：2014-04-16
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 */

class Util {

    private $rbpay_config;

    /**
     * 连连认证支付网关地址
     *
     */
    //private $llpay_gateway_new = 'https://yintong.com.cn/llpayh5/authpay.htm';

    public function __construct($rbpay_config) {
        $this->rbpay_config = $rbpay_config;
    }

    /**
     * 生成签名结果
     * @param $para_sort 已排序要签名的数组
     * return 签名结果字符串
     */
    public function buildRequestMysign($para_sort) {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = createLinkstring($para_sort);
        $mysign = "";
        //PHP5.3 版本以上 风控参数去斜杠
        $prestr = stripslashes($prestr);
        //file_put_contents("log.txt", "新的签名:" . $prestr . "\n", FILE_APPEND);
        switch (strtoupper(trim($this->rbpay_config['sign_type']))) {
        case "MD5":
            $mysign = md5Sign($prestr, $this->rbpay_config['key']);
            break;
        case "RSA":
            $mysign = RsaSign($prestr, $this->rbpay_config['RSA_PRIVATE_KEY']);
            break;
        default:
            $mysign = "";
        }
        //file_put_contents("log.txt", "签名:" . $mysign . "\n", FILE_APPEND);
        return $mysign;
    }

    public function send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id, $version) {
        //生成签名
        $sign = $this->createSign($paramArr, $apiKey);
        $paramArr['sign'] = $sign;
        $paramArr['sign_type'] = 'MD5';
        //生成AESkey
        $generateAESKey = $this->generateAESKey();
        $request = array();
        $request['merchant_id'] = $merchant_id;
        //加密key
        $request['encryptkey'] = $this->RSAEncryptkey($generateAESKey, $reapalPublicKey);
        //加密数据
        $request['data'] = $this->AESEncryptRequest($generateAESKey, $paramArr);

        $request['version'] = $version;

        // print_r($request);
        return $this->sendHttpRequest($request, $url);
    }

    //签名函数
    public function createSign($paramArr) {
        $apiKey = $this->rbpay_config['key'];
//        global $appSecret;
        //        $sign = $appSecret;
        $sign = '';
        ksort($paramArr);
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $sign .= $key . '=' . $val . '&';
            }
        }

        $sign = substr($sign, 0, (strlen($sign) - 1));
//        $sign.=$appSecret;
        $sign . $apiKey;
        $sign = md5($sign . $apiKey);
        return $sign;
    }

    /**
     * 生成要请求给连连支付的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
    public function buildRequestPara($para_temp) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = paraFilter($para_temp);
        //对待签名参数数组排序
        $para_sort = argSort($para_filter);
        //生成签名结果
        $mysign = $this->buildRequestMysign($para_sort);
        //签名结果与签名方式加入请求提交参数组中
        $para_filter['sign'] = $mysign;
        $para_filter['sign_type'] = $this->rbpay_config['sign_type'];
        return $para_filter;
    }

    public function sendHttpRequest($params, $url) {
        $opts = $this->getRequestParamString($params);
        //echo $opts;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证HOST
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-type:application/x-www-form-urlencoded;charset=UTF-8',
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts);

        /**
         * 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
         */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 运行cURL，请求网页
        $html = curl_exec($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        \app\common\Logger::dayLog('rongbao/curl', $status, $html);

        // close cURL resource, and free up system resources
        curl_close($ch);
        return $html;
    }

    /**
     * 组装报文
     *
     * @param unknown_type $params
     * @return string
     */
    public function getRequestParamString($params) {
        $params_str = '';
        foreach ($params as $key => $value) {
            $params_str .= ($key . '=' . (!isset($value) ? '' : urlencode($value)) . '&');
        }
        return substr($params_str, 0, strlen($params_str) - 1);
    }

    /**
     * 生成要请求给融宝代付的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组字符串
     */
    public function buildRequestParaToString($para_temp) {
        //生成AESkey
        $generateAESKey = $this->generateAESKey();
        $request = array();
        $request['merchant_id'] = $this->rbpay_config['merchant_id'];
        //加密key
        $request['encryptkey'] = $this->RSAEncryptkey($generateAESKey);
        //加密数据
        $request['data'] = $this->AESEncryptRequest($generateAESKey, $para_temp);
        $request['version'] = $this->rbpay_config['version'];
        return $request;
    }

    public function decryRequest($response) {
        $encryptkey = $this->RSADecryptkey($response['encryptkey'], $this->rbpay_config['privateKey']);
        $result = $this->AESDecryptResponse($encryptkey, $response['data']);
        return $result;
    }

    /**
     * 通过AES解密请求数据
     *
     * @param array $query
     * @return string
     */
    public function AESDecryptResponse($encryptKey, $data) {
        return $this->decrypt($data, $encryptKey);
    }

    public function decrypt($sStr, $sKey) {
        $decrypted = mcrypt_decrypt(
            MCRYPT_RIJNDAEL_128, $sKey, base64_decode($sStr), MCRYPT_MODE_ECB
        );

        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s - 1]);
        $decrypted = substr($decrypted, 0, -$padding);
        return $decrypted;
    }

    /**
     * 通过RSA，使用融宝公钥，加密本次请求的AESKey
     *
     * @return string
     */
    public function RSADecryptkey($encryptKey, $merchantPrivateKey) {
        $private_key = $this->getPrivateKey($merchantPrivateKey);
        $pi_key = openssl_pkey_get_private($private_key); //这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        openssl_private_decrypt(base64_decode($encryptKey), $decrypted, $pi_key); //私钥解密
        return $decrypted;
    }

    private function getPrivateKey($cert_path) {
        $pkcs12 = file_get_contents($cert_path);

        return $pkcs12;
    }

    /**
     * 通过AES加密请求数据
     *
     * @param array $query
     * @return string
     */
    public function AESEncryptRequest($encryptKey, array $query) {

        return $this->encrypt(json_encode($query), $encryptKey);
    }

    public function encrypt($input, $key) {
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $input = $this->pkcs5_pad($input, $size);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }

    public function pkcs5_pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    /**
     * 生成一个随机的字符串作为AES密钥
     *
     * @param number $length
     * @return string
     */
    public function generateAESKey($length = 16) {
        $baseString = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $AESKey = '';
        $_len = strlen($baseString);
        for ($i = 1; $i <= $length; $i++) {
            $AESKey .= $baseString[rand(0, $_len - 1)];
        }
        return $AESKey;
    }

    /**
     * 通过RSA，使用融宝公钥，加密本次请求的AESKey
     *
     * @return string
     */
    public function RSAEncryptkey($encryptKey) {
//        echo $this->rbpay_config['pubKeyUrl'];
        $public_key = $this->getPublicKey($this->rbpay_config['pubKeyUrl']);

        $pu_key = openssl_pkey_get_public($public_key); //这个函数可用来判断公钥是否是可用的

        openssl_public_encrypt($encryptKey, $encrypted, $pu_key); //公钥加密
        return base64_encode($encrypted);
    }

    public function getPublicKey($cert_path) {
        $pkcs12 = file_get_contents($cert_path);
        return $pkcs12;
    }

}
