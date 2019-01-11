<?php
namespace app\modules\api\common\baofoo;

use app\modules\api\common\baofoo\functions\BaofooSdk;
use app\common\Curl;
use app\common\Logger;


/**
 * @desc 宝付代扣API;
 * @author lubaba
 */
class BaofooApi {
    private $config = null;
    private $baofooSdk = null;
    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
    }
    
    /**
     * @desc 获取配置文件
     * @param  str $cfg 
     * @return  []
     */
    private function getConfig($cfg) {
        $configPath = __DIR__ . "/config/{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }

    public function getConf() {
        return $this->config;
    }

    /**
     * @desc 请求宝付公共请求参数
     * @return  []
     */
    private function getCommomParam($sign){
        if(!$this->config) return [];
        return [
            'version' => $this->config['version'],
            'member_id' => $this->config['member_id'],
            'terminal_id' => $this->config['terminal_id'],
            'data_type' =>$this->config['data_type'],
            'data_content'=>$sign
        ];
    }
    /**
     * @desc 代付交易接口
     * @param  [] $data
     * @return [res_code, res_data]
     */
    public function bfTrade($data) {
        //组装数据
        if(empty($data)) return [];
        $dataContent = [];
        $dataContent['trans_content']['trans_reqDatas']['trans_reqData'] = $data;
        //生成签名
        $sign = $this->createSign($dataContent);
        $curlData = $this->getCommomParam($sign);
        if(empty($curlData)) return false; 
        $returnInfo = $this->httpClientPost($this->config['trade_url'],$curlData);
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('baofoo', 'baofooApi/trade',$this->config['trade_url'], $sign, $response);
        return $response;
    }


    /**
     * @desc 代扣查询
     * @param array $queryData
     * @return  array
     */
    public function bfQuery($queryData){
        //组装数据
        if(empty($queryData)) return [];
        $dataContent = [];
        $dataContent['trans_content']['trans_reqDatas']['trans_reqData'] = $queryData;
        //生成签名
        $sign = $this->createSign($dataContent);
        $data = $this->getCommomParam($sign);
        if(empty($data)) return false; 
        $returnInfo = $this->httpClientPost($this->config['query_url'],$data);
         // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('baofoo', 'baofooApi/bfQuery',$this->config['query_url'], $sign, $response);
        return $response;
    }
    /**
     * Undocumented function
     * 异步回调数据处理
     * @param [type] $params
     * @return void
     */
    public function bfNotify($params){
        if(empty($params)) return false;
        $this->baofooSdk = new BaofooSdk($this->config["pfxfilename"], $this->config["cerfilename"], $this->config["private_key_password"]); //实例化加密类。
        $result = $this->baofooSdk->decryptByPublicKey($params);
        $xml = simplexml_load_string($result);
        $result = json_decode(json_encode($xml),TRUE);
        Logger::dayLog('baofoo', 'baofooApi/bfNotify',$result);
        return $result;
    }

    /**
     * @desc 生成签名
     * @param array $data_content_parms 签名参数
     * @return string
     */
    private function createSign($dataContentParms){
        $encryptedString = str_replace("\\/", "/",json_encode($dataContentParms));//转JSON
        $this->baofooSdk = new BaofooSdk($this->config["pfxfilename"], $this->config["cerfilename"], $this->config["private_key_password"]); //实例化加密类。
        $Encrypted = $this->baofooSdk->encryptedByPrivateKey($encryptedString);	//先BASE64进行编码再RSA加密
        return $Encrypted;
    }

    /**
     * @desc 提交数据
     * @param string $url
     * @param array $data
     * @return string
     */
    private function httpClientPost($url,$data) {
        $timeLog = new \app\common\TimeLog();
        //$jsonString = json_encode($data);
        $postDataString = http_build_query($data);
        $curl = new Curl();
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->setOption(CURLOPT_TIMEOUT, 30);
        $content = '';
        $content = $curl->post($url, $postDataString);
        $status = $curl->getStatus();
        $timeLog->save('baofoo', ['api', 'POST', $status, $url, $postDataString, $content]);
        if ($status != 200) {
            Logger::dayLog(
                "baofoo",
                "请求信息", $url, $data,
                "http状态", $status,
                "响应内容", $content
            );
        }
        return $content;
    }

    /**
     * @desc 非对称解密数据并标准化返回
     * @param string $res
     * @return array
     */
    private function parseResult($res) {
        if(!$res)  return ['resp_code' => "", 'resp_msg' => "请求出错，请检查网络"];      
        $returnDecode = $this->baofooSdk->decryptByPublicKey($res); 
        $endataContent = [];
        $endataContent = json_decode($returnDecode,TRUE);
       return $endataContent;
    }
}
