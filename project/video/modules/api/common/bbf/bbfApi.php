<?php
namespace app\modules\api\common\bbf;

use app\modules\api\common\bbf\bbfCrypt;
use app\common\Curl;
use app\common\Logger;


/**
 * @desc 邦宝付代扣API;
 * @author lubaba
 */
class BbfApi {
    private $config = null;
    private $bbfCrypt = null;
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
     * @desc 代付交易接口
     * @param  [] $data
     * @return [res_code, res_data]
     */
    public function bbfTrade($data) {
        //组装数据
        if(empty($data)) return [];
        $dataContent = [
            'version' => $this->config['version'],
            'transCode'=>$this->config['payTransCode'],
            'signType'=>$this->config['signType'],
            'merchantId'=>$this->config['merchantId'],
            'mcSequenceNo'=>$data['mcSequenceNo'],
            'mcTransDateTime'=>$data['mcTransDateTime'],
            'orderNo'=>$data['orderNo'],
            'amount'=>$data['amount'],
            'cardNo'=>$data['cardNo'],
            'accName'=>$data['accName'],
            'idInfo'=>$data['idInfo'],
            'accType'=>$this->config['accType'],
            'lBnkNo'=>$data['lBnkNo'],
            'lBnkNam'=>$data['lBnkNam']
        ];
        ksort($dataContent);
        //生成签名
        $sign = $this->createSign($dataContent);
        $dataContent['signature'] = $sign;
        $returnInfo = $this->httpClientPost($this->config['url'],$dataContent);
        // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('bbf', 'bbfApi/trade',$this->config['url'], $sign, $response);
        return $response;
    }

    /**
     * @desc 代扣查询
     * @param array $queryData
     * @return  array
     */
    public function bbfQuery($queryData){
        //组装数据
        if(empty($queryData)) return [];
        $dataContent = [
            'version' => $this->config['version'],
            'transCode'=>$this->config['queryTransCode'],
            'signType'=>$this->config['signType'],
            'merchantId'=>$this->config['merchantId'],
            'mcSequenceNo'=>$data['mcSequenceNo'],
            'mcTransDateTime'=>$data['mcTransDateTime'],
            'orderNo'=>$data['orderNo'],
            'amount'=>$data['amount']
        ];
        ksort($dataContent);
        //生成签名
        $sign = $this->createSign($dataContent);
        $dataContent['signature'] = $sign;
        $returnInfo = $this->httpClientPost($this->config['url'],$dataContent);
        
         // 返回结果
        $response = $this->parseResult($returnInfo);
        Logger::dayLog('bbf', 'bbfApi/bbfQuery',$this->config['url'], $sign, $response);
        return $response;
    }


    /**
     * @desc 生成签名
     * @param array $data_content_parms 签名参数
     * @return string
     */
    private function createSign($dataContentParms){
        
        //$encryptedString = http_build_query($dataContentParms);//转k=v
        $encryptedArr = [];
        foreach ($dataContentParms as $key => $value) {
           if ($value == null || $value == '') {
               unset($dataContentParms[$key]);
               continue;
           }
           $encryptedArr[]=$key.'='.iconv("UTF-8", "GB2312//IGNORE", trim($value));
        }
        $encryptedString = implode("&", $encryptedArr);
        $this->bbfCrypt = new bbfCrypt($this->config["pfxfilename"], $this->config["cerfilename"], $this->config["private_key_password"]); //实例化加密类。
        $Encrypted = $this->bbfCrypt->encryptedByPrivateKey($encryptedString);	//先sha1再RSA加密
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
        $postDataArr = [];
        foreach ($data as $key => $value) {
            if ($value == null || $value == '') {
               unset($data[$key]);
               continue;
            }
            if($key == 'signature'){
                $postDataArr[]=$key.'='.urlencode($value);
            }else{
                $postDataArr[]=$key.'='.iconv("UTF-8", "GB2312//IGNORE", trim($value));
            }
        }
        $postDataString =implode("&", $postDataArr);
        $curl = new Curl();
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->setOption(CURLOPT_TIMEOUT, 30);
        $content = '';
        $content = $curl->post($url, $postDataString);
        $status = $curl->getStatus();
        $timeLog->save('bbf', ['api', 'POST', $status, $url, $postDataString, $content]);
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
     * @desc 
     * @param string $res
     * @return array ['respCode' => "", 'respMsg' => ""];
     */
    private function parseResult($res) {
        if(!$res)  return ['respCode' => "110000", 'respMsg' => "请求出错，请检查网络"];
        $returnArr = explode('&',$res);
        $parseArr = [];
        foreach ($returnArr as $key => $value) {
            $k = strpos($value,"=");
            $parseArr[substr($value,0,$k)] = substr($value,$k+1);
        }
        if(empty($parseArr)){
            return ['respCode' => "110001", 'respMsg' => "参数解析错误"];
        }
        $sign = base64_decode($parseArr['signature']);
        unset($parseArr['signature']);
        ksort($parseArr);
        $encryptedArr = [];
        foreach ($parseArr as $k => $val) {
           if ($val == null || $val == '') {
               unset($parseArr[$key]);
               continue;
           }
           $encryptedArr[]=$k.'='.trim($val);
        }
        $encryptedString = openssl_digest(implode("&", $encryptedArr),"sha1");
        $returnRes = $this->bbfCrypt->decryptByPublicKey($sign,$encryptedString);
        if($returnRes)
            return $parseArr;
        else
            return ['respCode' => "110002", 'respMsg' => "验签失败"];
    }
}
