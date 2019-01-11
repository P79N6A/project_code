<?php

namespace app\modules\api\common\jd;
use app\common\Logger;

/**
 *京东接口类
 */
class JdApi {

    private $config;

    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
    }

    /**
     * 获取配置文件
     * @param  str $env
     * @param  str $aid
     * @return   []
     */
    private function getConfig($cfg) {
        $configPath = __DIR__ . "/config/{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }


    /**
     * 发起快捷支付方法
     * @param $data_xml交易的xml格式数据
     */
    public function trade($data_xml){
        /*$resp='resp=PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4NCjxDSElOQUJBTks+CiAgPFZFUlNJT04+MS4wLjA8L1ZFUlNJT04+CiAgPE1FUkNIQU5UPjExMDk0NjcwMTAwMzwvTUVSQ0hBTlQ+CiAgPFRFUk1JTkFMPjAwMDAwMDAxPC9URVJNSU5BTD4KICA8REFUQT4rT3QvaGd6MVE0R0VQTFhBMEJjQmZWMURuaFlLb2FYRERUS3REUlB1Nzdvc0IrUGlRQXFGVVJZbXhTMXFkbmRVMXB2eWw1UFFCTkZvOVZibjljWVJIVnNvTlpWVkhsWlhvODZRdTVZeW9xSFo5M1JWMEtmekkyK1g4VUViOXVqMTd0OUgvRzUrN2hyM2I3b3IvU29CMElha0JRUGNRTDVQZzNBREw2VnlQV3ppcDZZaytiTG8zMXFNcUd6V0hlejBwdG44ZS9IOHYwZVEwZUNmQWJwcmMzRUZlTHZCbUgydmhLMDF1b0gremIvbXNYN3lUNkFkcjJQbld4a3JDRXNmbklYMzZqYmFNSGNNVE01QkQ3ek1FcTNXUkV3eDJ3SXQ5STBSWTdsZGR0SnUwWHBnQTRTYjRKWHpkS0ZDWUZvL2VNT0t1b2hyRlFJdFNWM1c2cytBcWc9PTwvREFUQT4KICA8U0lHTj44OGFmZjE0YzVkZjY5OTI4MmY1Mzk4NThhNzk5MTNkNzwvU0lHTj4KPC9DSElOQUJBTks+';
        $this->operate($resp);die;*/
        //把data元素des加密
        $desObj =(new Des($this->config['3des_key']));
        $dataDES = $desObj->encrypt($data_xml);
        $sign = $this->myMd5($this->config['version'].$this->config['merchant'].$this->config['terminal'].$dataDES,$this->config['md5_key']);
        $xml = (new Xml())->xml_create($this->config['version'],$this->config['merchant'],$this->config['terminal'],$dataDES,$sign);
        //使用方法
        $param ='charset=UTF-8&req='.urlencode(base64_encode($xml));
        $resp = $this->post($param);

        $result = $this->operate($resp);
        return $result;
    }

    /**
     * @param $resp 网银在线返回的数据
     * 数据的解析步骤：
     * 1：截取resp=后面的xml数据
     * 2: base64解码
     * 3: 验证签名
     * 4: 解析交易数据处理自己的业务逻辑
     */
    function operate($resp){
        $config = $this->config;
        $temResp = base64_decode(substr($resp,5));
        //判断是否是xml数据
        if(!strrpos($temResp,'xml')){
            Logger::dayLog('jd/operate', '数据不能转换成xml', $temResp);
            return false;
        }
        $xml = simplexml_load_string($temResp);
        //验证签名, version.merchant.terminal.data
        $text = $xml->VERSION.$xml->MERCHANT.$xml->TERMINAL.$xml->DATA;
        //des密钥要网银在线后台设置
        $des = new Des($config['3des_key']);
        $decodedXML = $des->decrypt($xml->DATA);
        $dataXml = simplexml_load_string($decodedXML);
        if(!$this->md5_verify($text,$config['md5_key'],$xml->SIGN)){
            Logger::dayLog('jd/operate', '异常订单查询结果', $dataXml);
        }
        return $dataXml;

    }
    //发送请求
    public function post($param){
        #$url = "https://quick.chinabank.com.cn/express.htm";
        $url = $this->config['action_url'];
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
        #curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_PORT, 443);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名,0不验证
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        $file_contents = curl_exec($ch);//运行curl
        if(!empty(curl_error($ch))){
            Logger::dayLog('jd/operate', 'CURL错误', curl_error($ch));
        }
        curl_close($ch);
        return $file_contents;
    }


    /**
     * md5加密方法
     */
    function myMd5($text,$key){
        return md5($text.$key);
    }
    /**
     * 验证签名方法
     */
    function md5_verify($text,$key,$md5){
        $md5Text = $this->myMd5($text,$key);
        return $md5Text==$md5;
    }
}
