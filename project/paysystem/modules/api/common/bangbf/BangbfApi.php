<?php
namespace app\modules\api\common\bangbf;

use app\common\Curl;
use app\common\Logger;


/**
 * @desc 邦宝付接口类;
 */
class BangbfApi {
    private $config = null;
    private $bbfCrypt = null;
    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
        $this->bbfCrypt = new bbfCrypt($this->config["private_key_path"], $this->config["public_key_path"], $this->config["private_key_password"]); //实例化加密类。
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
    /**
     * @desc 直接支付接口
     * @param  obj $oPayorder
     * @return [res_code, res_data]
     */
    public function pay($oPayorder) {
        $nowDate = date('Ymd',time());
        $dataContent = [
            'version' => $this->config['version'],
            'charset' => $this->config['charset'],
            'signType' => $this->config['signType'],
            'service' => $this->config['service'],
            'merchantId' => $this->config['merchantId'],
            'offlineNotifyUrl' => $this->config['offlineNotifyUrl'],
            'mercReqNo' => $oPayorder->channel_id."_".$oPayorder->orderid,
            'mercOrdNo' => $oPayorder->channel_id."_".$oPayorder->orderid,
            'ordDate' => $nowDate,
            'cardNo' => $oPayorder->cardno,
            'idInfo' => $oPayorder->idcard,
            'idType' => $this->config['idType'],
            'cardType' => $this->config['cardType'],
            'cardName' => $oPayorder->name,
            'cardPhone' => $oPayorder->phone,
            'encryptFlag' => $this->config['encryptFlag'],
            'cardType' => $this->config['cardType'],
            //'identityId' => $oPayorder->identityid,
            'totalAmount'=> $oPayorder->amount,
            'currency'  => $this->config['currency'],
            'productName' => $oPayorder->productname,
            'isRegAgreement' => $this->config['isRegAgreement'],
        ];
        ksort($dataContent);
        $data = $this->buildRequestPara($dataContent);
       //var_dump($data);die;
        if(empty($data)) return false; 
        $returnInfo = $this->HttpClientPost($this->config['action_url'],$data);
        //var_dump($returnInfo);die;
        Logger::dayLog('bangbf', 'bangbfApi/pay',$this->config['action_url'], $data, $returnInfo);
        $result = $this->parseResult($returnInfo);
        return $result;
    }
    public function query($oPayorder){
        $dataContent = [
            'version' => $this->config['version'],
            'charset' => $this->config['charset'],
            'signType' => $this->config['signType'],
            'service' => 'OrderSearch',
            'merchantId' => $this->config['merchantId'],
            'requestId' => $oPayorder->channel_id."_".$oPayorder->orderid.rand(1000,9999),
            'orderId' => $oPayorder->channel_id."_".$oPayorder->orderid,
        ];
        ksort($dataContent);
        $data = $this->buildRequestPara($dataContent);
        //var_dump($data);die;
        if(empty($data)) return false;
        $returnInfo = $this->HttpClientPost($this->config['action_url'],$data);
        //var_dump($returnInfo);die;
        Logger::dayLog('bangbf', 'bangbfApi/query',$this->config['action_url'], $data, $returnInfo);
        $result = $this->parseResult($returnInfo);
        return $result;
    }
    /**
     * 生成要请求给连连支付的参数数组
     * @param $dataContent 请求前的参数数组
     * @return 要请求的参数数组
     */
    public function buildRequestPara($dataContent) {
        $dataArr = $this->paraFilter($dataContent);
        $encryptedString =implode("&", $dataArr);
        //生成签名结果
        $mysign = $this->bbfCrypt->Rsasign($encryptedString);
        //签名结果与签名方式加入请求提交参数组中
        $dataContent['merchantSign'] = $mysign;
        $dataContent['merchantCert'] = $this->bbfCrypt->merchantCert;
        return $dataContent;
    }
    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的签名
     */
    function paraFilter($para) {
        $dataArr = [];
        foreach ($para as $key => $value) {
            if ($value == null || $value == '') {
                unset($para[$key]);
                continue;
            }
            $dataArr[]=$key.'='.$value;
        }
        return $dataArr;
    }
    /**
     * @desc 提交数据
     * @param string $url
     * @param array $data
     * @return string
     */
    private function HttpClientPost($url,$data) {
        $timeLog = new \app\common\TimeLog();
        $postData = $this->paraFilter($data);
        $postDataString = implode("&", $postData);
        Logger::dayLog('bangbf', 'bangbfApi/HttpClientPost',$postDataString);
        $curl = new Curl();
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->setOption(CURLOPT_TIMEOUT, 30);
        $content = '';
        $content = $curl->post($url, $postDataString);
        $status = $curl->getStatus();
        $timeLog->save('bangbf', ['api', 'POST', $status, $url, $postDataString, $content]);
        if ($status != 200) {
            Logger::dayLog(
                "bangbf",
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
        if(!$res)  return ['returnCode' => "1110001", 'returnMessage' => "请求出错，请检查网络"];
        $resArr = explode('&',$res);
        $newResArr = array();
        if(!empty($resArr)){
            foreach ($resArr as $key=>$value){
                $k = strpos($value,"=");
                $newResArr[substr($value,0,$k)] = substr($value,$k+1);
            }
        }
        $serverSign = empty($newResArr['serverSign'])?'':$newResArr['serverSign'];
        unset($newResArr['serverCert']);
        unset($newResArr['serverSign']);
        ksort($newResArr);
        $newResArr = $this->paraFilter($newResArr);
        $signString = implode("&", $newResArr);

        $result = $this->bbfCrypt->verify($signString,$serverSign);
        if($result)
            return $newResArr;
        else
            return ['returnCode' => "1110002", 'returnMessage' => "验签失败"];
    }

    /**
     * @param $res
     * @return array|bool
     * 异步回调 验签
     */
    public function verifyNotify($res){
        if(!$res)  return ['returnCode' => "1110003", 'returnMessage' => "参数为空"];
        $serverSign = empty($res['serverSign'])?'':$res['serverSign'];
        unset($res['serverCert']);
        unset($res['serverSign']);
        ksort($res);
        $newResArr = $this->paraFilter($res);
        $signString = implode("&", $newResArr);

        $result = $this->bbfCrypt->verify($signString,$serverSign);
        return $result;
    }
}
