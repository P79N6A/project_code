<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/16
 * Time: 18:15
 */

namespace app\modules\api\common\wsm;

class WSMApi
{
    private $config_info;
    public function __construct()
    {
        $config_info = new WSMConfig();//配置系统环境
        if (SYSTEM_PROD){
            $this->config_info = $config_info->initConfig('prod');
        }else {
            $this->config_info = $config_info->initConfig('dev');
        }
    }

    /**
     * 微神马加密函数
     * @param $data
     * @param $privateKey
     * @param $iv
     * @return mixed
     */
    public function wsm_encrypt($data, $privateKey, $iv) {
        $encrypted = mcrypt_encrypt ( MCRYPT_RIJNDAEL_128, $privateKey, $data, MCRYPT_MODE_CBC, $iv );
        $res = base64_encode ( $encrypted );
        return $res;
    }

    /**
     * 微神马解密函数
     * @param $data
     * @param $privateKey
     * @param $iv
     * @return mixed
     */
    public function wsm_decrypt($data, $privateKey, $iv) {
        $encryptedData = base64_decode ( trim ( $data ) );
        $decrypted = mcrypt_decrypt ( MCRYPT_RIJNDAEL_128, $privateKey, $encryptedData, MCRYPT_MODE_CBC, $iv );
        // $decrypted = iconv("gb2312","utf-8",$decrypted);
        return trim ( $decrypted );
    }

    /**
     * 商户号 由微神马提供
     * @return string
     */
    public function getMid()
    {
        //68
        return $this->config_info['mid'];
    }

    /**
     * 产品名 由微神马提供
     * @return string
     */
    public function getCpm()
    {
        return $this->config_info['cpm'];
    }

    /**
     * 加密解密串，由微神马提供
     * @return string
     */
    public function getEnkeys()
    {
        return $this->config_info['enkeys'];
    }

    /**
     * 商户密钥串，由微神马提供
     * @return string
     */
    public function getShmyc()
    {
        return $this->config_info['shmyc'];
    }

    /**
     * 异步通知回调url 微神马回调地址
     * @return string
     */
    public function getCallbackurl()
    {
        return $this->config_info['callbackurl'];
        /*
        if (SYSTEM_PROD){
            return 'http://pay.xianhuahua.com/wsm/wsmback/notify';
        }
        return 'http://paytest.xianhuahua.com/wsm/wsmback/notify';
        */
    }

    /**
     * 请求微神马http
     * @param $url
     * @param array $data
     * @param int $timeout
     * @param bool $CA
     * @return array
     */
    public  function curlPost($url, $data = array(), $timeout = 30, $CA = true){
        $dirname = dirname(__DIR__).DIRECTORY_SEPARATOR.'wsm'.DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR;
        $cacert = $dirname. 'client.pem'; //CA根证书
        $client_cert = $dirname. 'xhhz.crt';//指定客户端证书
        $client_key = $dirname.'client.pem';//指定客户端私钥

        $SSL = substr($url, 0, 8) == "https://" ? true : false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout-2);

        //	curl_setopt($ch, CURL_SSLVERSION_DEFAULT,1);
        //	CURL_SSLVERSION_TLSv1 (1) CURL_SSLVERSION_TLSv1_0 (4), CURL_SSLVERSION_TLSv1_1 (5) ， CURL_SSLVERSION_TLSv1_2 (6) 中的其中一个。

        curl_setopt($ch, CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, $client_cert); //证书 如果服务开启了 SSLVerifyClient require 不使用证书error会报 error:14094410:SSL routines:ssl3_read_bytes:sslv3 alert handshake failure

        curl_setopt($ch, CURLOPT_SSLKEYTYPE,'PEM');//私钥文件格式 不指定格式 error会报error:14094418:SSL routines:ssl3_read_bytes:tlsv1 alert unknown ca
        curl_setopt($ch, CURLOPT_SSLKEY,$client_key);//私钥
// 	curl_setopt($ch, CURLOPT_SSLKEYPASSWD,'ORTxW7S6EgTv1FLl');//运营私钥的密码 如果密码错误unable to set private key file: 'D:\phpStudy\WWW\test/client.key' type PEM
        //curl_setopt($ch, CURLOPT_SSLKEYPASSWD,'zYhAuo5y5ZynBQXm');//联调测试私钥的密码 如果密码错误unable to set private key file: 'D:\phpStudy\WWW\test/client.key' type PEM
        curl_setopt($ch, CURLOPT_SSLKEYPASSWD,'1234567890');
// 	curl_setopt($ch, CURLOPT_SSLKEYPASSWD,'a7nsbi4DoDARvzGb');//联调测试私钥的密码 如果密码错误unable to set private key file: 'D:\phpStudy\WWW\test/client.key' type PEM
// 		curl_setopt($ch, CURLOPT_SSLKEYPASSWD,'K68moP51KYbUxDwK');//4pay私钥的密码 如果密码错误unable to set private key file: 'D:\phpStudy\WWW\test/client.key' type PEM



        if ($SSL && $CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   // 只信任根证书颁布的证书
            curl_setopt($ch, CURLOPT_CAINFO, $cacert); // CA根证书（用来验证的服务器证书是否是根证书颁布的）
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 检查证书中是否设置域名，并且是否与提供的主机名匹配 2检查 0不检查
        } else if ($SSL && !$CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 检查证书中是否设置域名 2检查 0不检查
        }
        curl_setopt($ch, CURLOPT_HEADER, true);//如果获得响应头信息
        // 	curl_setopt($ch, CURLOPT_NOBODY, true);//是否不需要响应的正文
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); //避免data数据过长问题
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // 	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //data with URLEncode

        $ret = curl_exec($ch);
        //var_dump(curl_error($ch));  //查看报错信息
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);// 获得响应结果里的：头大小
        $header = substr($ret, 0, $headerSize);// 根据头大小去获取头信息内容
        $body = substr( $ret ,$headerSize-1 );
        $error = curl_error($ch);//显示详细错误信息
        curl_close($ch);
        return array($header,$body,$error);
    }

    public static function encode($input, $key = 'A91DBAB64154FE7A') {
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

    private static function pkcs5_pad($text, $blocksize) {

        $pad = $blocksize - (strlen($text) % $blocksize);

        return $text . str_repeat(chr($pad), $pad);

    }

    public static function decode($sStr, $sKey = 'A91DBAB64154FE7A') {

        $decrypted = mcrypt_decrypt(

            MCRYPT_RIJNDAEL_128,

            $sKey,

            base64_decode($sStr),

            MCRYPT_MODE_ECB

        );

        $dec_s = strlen($decrypted);

        $padding = ord($decrypted[$dec_s - 1]);

        $decrypted = substr($decrypted, 0, -$padding);

        return $decrypted;

    }

    /**
     * 微神马---查询地址
     * @return string
     */
    public function getQueryUrl()
    {
        return $this->config_info['queryUrl'];
        /*
        if (SYSTEM_PROD){
            return 'https://lt-orderquery.wsmtec.com/index.php';
        }
        return 'https://lt-orderquery.wsmtec.com/index.php';
        */
    }
    
    /**
     * 微神马---发送数据地址
     * @return string
     */
    public function getSendUrl()
    {
        return $this->config_info['sendUrl'];
        /*
        if (SYSTEM_PROD){
            return 'https://lt-aquarius.wsmtec.com/OLM/index.php';
        }
        return 'https://lt-aquarius.wsmtec.com/OLM/index.php';
        */

    }

    /**
     * 贷后通知地址
     * @return string
     */
    public function getAfterTheLoan()
    {
        return $this->config_info['afterTheLoan'];
        /*
        if (SYSTEM_PROD){
            return 'http://weixin.xianhuahua.com/new/notifyfund';
        }
        return 'http://yyytest.xianhuahua.com/new/notifyfund';
        */
    }

    /**
     * 袋后加密串
     * @return string
     */
    public function getYiyiyuanEncode()
    {
        return $this->config_info['yiyiyuanEncode'];
        /*
        if (SYSTEM_PROD){
            return '24BEFILOPQRUVWXcdhntvwxy';
        }
        return '24BEFILOPQRUVWXcdhntvwxy';
        */
    }

    /**
     * 设置空值
     * @param $data_set
     * @param $string
     * @return string
     */
    public function emptyArrDefaultNull($data_set, $string)
    {
        if (empty($data_set[$string])){
            return '';
        }
        return $data_set[$string];
    }

    /**
     * 协议地址
     * @return string
     */
    public function getAgreement()
    {
        return $this->config_info['agreement'];
        /*
        if (SYSTEM_PROD){
            return "http://weixin.xianhuahua.com/new/agreeloan";
        }
        return "http://weixin.xianhuahua.com/new/agreeloan";
        */

    }

    public function verifySign($result)
    {
        //sign = md5(sha1($state+$errorcode+$shddh)+$shmyc)
        //验签
        $sign = md5(sha1($result['state'].$result['errorcode'].$result['shddh']).$this->getShmyc());
        return $sign == $result['sign'];
    }

    /**
     * 确认出了1023，1028，其他都是失败
     * @param $errorcode
     * @return bool
     */
    public function isFailCode($errorcode)
    {
        $fail_code = [
            '1000' => '1000_商户密钥串校验未通过',
            '1001' => '1001_数字签名校验未通过',
            '1002' => '1002_绑定校验未通过',
            '1003' => '1003_支付明细校验未通过',
            '1004' => '1004_绑定范围校验未通过',
            '1005' => '1005_对公开户行相关信息非空校验未通过',
            '1006' => '1006_解密校验未通过',
            '1007' => '1007_必填校验未通过',
            '1008' => '1008_字段规则校验未通过',
            '1009' => '1009_日期校验未通过',
            '1010' => '1010_类型校验未通过',
            '1011' => '1011_依赖必填校验未通过',
            '1012' => '1012_字段长度校验未通过',
            '1013' => '1013_营业时间校验未通过',
            '1014' => '1014_A类黑名单校验未通过',
            //'1015' => '1015_(暂时停用)银行卡支持校验未通过(暂时停用)',
            '1016' => '1016_商户订单号重复校验未通过',
            '1017' => '1017_单量校验未通过',
            '1018' => '1018_资金方匹配失败',
            '1019' => '1019_还款计划生成失败',
            '1020' => '1020_签约失败',
            '1021' => '1021_支付失败',
            '1022' => '1022_商户订单号不存在',
            //'1023' => '1023_处理中',
            '1024' => '1024_借贷人年龄校验未通过',
            '1025' => '1025_资产ip地址不符合设置的ip地址',
            '1026' => '1026_开户行编号不在系统可用银行列表中',
            '1027' => '1027_第三方支付成功后银行支付处理失败',
            //'1028' => '1028_订单查询过频',
            '1029' => '1029_保证金金额校验未通过',
            '1030' => '1030_当日金额校验未通过',
        ];
        return !empty($fail_code[$errorcode]);
    }

    private function errorCodeMsg($code)
    {
        $return = [
            '1000' => '1000_商户密钥串校验未通过',
            '1001' => '1001_数字签名校验未通过',
            '1002' => '1002_绑定校验未通过',
            '1003' => '1003_支付明细校验未通过',
            '1004' => '1004_绑定范围校验未通过',
            '1005' => '1005_对公开户行相关信息非空校验未通过',
            '1006' => '1006_解密校验未通过',
            '1007' => '1007_必填校验未通过',
            '1008' => '1008_字段规则校验未通过',
            '1009' => '1009_日期校验未通过',
            '1010' => '1010_类型校验未通过',
            '1011' => '1011_依赖必填校验未通过',
            '1012' => '1012_字段长度校验未通过',
            '1013' => '1013_营业时间校验未通过',
            '1014' => '1014_A类黑名单校验未通过',
            '1015' => '1015_(暂时停用)银行卡支持校验未通过(暂时停用)',
            '1016' => '1016_商户订单号重复校验未通过',
            '1017' => '1017_单量校验未通过',
            '1018' => '1018_资金方匹配失败',
            '1019' => '1019_还款计划生成失败',
            '1020' => '1020_签约失败',
            '1021' => '1021_支付失败',
            '1022' => '1022_商户订单号不存在',
            '1023' => '1023_处理中',
            '1024' => '1024_借贷人年龄校验未通过',
            '1025' => '1025_资产ip地址不符合设置的ip地址',
            '1026' => '1026_开户行编号不在系统可用银行列表中',
            '1027' => '1027_第三方支付成功后银行支付处理失败',
            '1028' => '1028_订单查询过频',
            '1029' => '1029_保证金金额校验未通过',
            '1030' => '1030_当日金额校验未通过',
        ];
        if (!empty($error_msg[$code])){
            return $error_msg[$code];
        }
        return "4000_未知错误";

    }
}