<?php

namespace app\models\xs;

use app\common\Common;
use app\common\Apihttp;
use yii\helpers\ArrayHelper;
use app\commonapi\Logger;
use app\common\Curl;

/**
 * 统一对外开放接口
 */
class XsBaiduApi {
    private $config;

    public function __construct() {
        // 获取配置文件
        $this->config = $this->getConfig();
    }
        
    /**
     * @desc 获取配置文件
     * @param  str $cfg 
     * @return  []
     */
    private function getConfig() {
        $configPath = __DIR__ . "/common/config.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }

    /**
     * Undocumented function
     * 请求接口
     * @param [type] $name
     * @param [type] $identity
     * @param [type] $phone
     * @return void
     */
    public function getParams($data,$apiType){
        $postdata = [
            'sp_no'=>$this->config['sp_no'],
            'service_id'=>$this->config['service_id'][$apiType],
            'reqid'=>$apiType.'_'.date('YmdHis').rand(10000,99999),
            'name'=>$data['name'],
            'identity'=>$data['idcard'],
            'phone'=>$data['phone'],
            'datetime'=>$this->microtime_int(),
            'sign_type'=>$this->config['sign_type']
        ];
        return $postdata;
    }

    /**
     * Undocumented function
     * 请求接口
     * @param [type] $name
     * @param [type] $identity
     * @param [type] $phone
     * @return void
     */
    public function sendRequest($postdata,$apiType){
        $postdata['sign'] = $this->getSign($postdata);
        $result = $this->postCurl($postdata,$apiType);
        $result = json_decode($result,true);
        return $result;
    }
    /**
     * Undocumented function
     * 获得毫秒13为int
     * @return void
     */
    function microtime_int(){
        list($usec, $sec) = explode(" ", microtime());
        return (int)sprintf('%.0f',(floatval($usec)+floatval($sec))*1000);
    
     }
    /**
     * Undocumented function
     * 获得签名
     * @param [type] $postdata
     * @return void
     */
    private function getSign($postdata){
        ksort($postdata);
        $str = "";
        foreach($postdata as $key=>$val){
            $str.="&".$key.'='.$val;
        }
        $str = substr($str,1);
        $str .='&key='.$this->config['key'];
        $sign =  md5($str);
        return $sign;
    }
    private function postCurl($postdata,$apiType){
        $curl = new Curl();
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->setOption(CURLOPT_TIMEOUT, 30);
        $content = '';
        $url = $this->config['url'];
        $content = $curl->post($url, $postdata);
        //$content = json_decode($content,true);
        $status = $curl->getStatus();
        Logger::dayLog("baidu".$apiType,"请求信息", $url, $postdata,"http状态", $status,"响应内容", $content);
        return $content;
    }

    /**
     * 百度接口对外
     */
    public function runBaidu($oBasic,$apiType) {
        $baidu_info = [];
        //1. 获取用户本地百度数据
        switch ($apiType) {
            case 'prea':
                $baidu_info = $this->getBaiduPreaInfo($oBasic);
                break;
            case 'multi':
                $baidu_info = $this->getBaiduMultiInfo($oBasic);
                break;
            default:
                $baidu_info = [];
                break;
        }
        if ($baidu_info) {
            return $baidu_info;
        }
        //2. 本地无数据则请求百度金融接口
        $baidu_info = $this->getBaiduApi($oBasic,$apiType);
        return $baidu_info;
    }

    /**
     * 获取百度金融Prea信息
     */
    private function getBaiduPreaInfo($oBasic)
    {
        $bd_data = [];
        $oBd = (new XsBaiduprea) -> getResult($oBasic['phone'],$oBasic['idcard']);
        //拼接百度prea信息
        if($oBd){
            $bd_data = (new YArray) -> getByKeys($oBd,[
                'retCode',
                'retMsg',
                'models',
                'score',
            ],0);
        }
        return $bd_data;
    }

    /**
     * 获取百度金融多投信息
     */
    private function getBaiduMultiInfo($oBasic)
    {
        $bd_data = [];
        $oBd = (new XsBaidumulti) -> getResult($oBasic['phone'],$oBasic['idcard']);
        //拼接百度prea信息
        if($oBd){
            $bd_data = (new YArray) -> getByKeys($oBd,[
                'name_score',
                'name_details',
                'id_score',
                'id_details',
                'ph_score',
                'ph_details',
            ],0);
        }
        return $bd_data;
    }

    /**
     * 请求百度金融接口
     * @param  obj $aid         
     * @return []
     */
    private function getBaiduApi($oBasic,$apiType)
    {
        //1, 获取请求配置参数
        $params = $this->getParams($oBasic,$apiType);

        //2, 请求前本地记录 
        switch ($apiType) {
            case 'prea':
                $baidu = new XsBaiduprea();
                $oBd = $baidu->saveData($oBasic,$params['reqid']);
                break;
            case 'multi':
                $baidu = new XsBaidumulti();
                $oBd = $baidu->saveData($oBasic,$params['reqid']);
                break;
            default:
                $baidu_info = [];
                break;
        }
        //3, 请求百度接口
        $baidu_result = $this->sendRequest($params,$apiType);
        //更新数据
        $res = $baidu->updateBdInfo($baidu_result);
        if (!$res) {
            Logger::dayLog('result/getBaiduApi', '百度请求更新失败', $params,$baidu->errors);
        }
        return $baidu_result;
    }

    public function object2array($object) 
    {   
        if (is_object($object)) {    
            foreach ($object as $key => $value) {       
                $array[$key] = $value;     
            }   
        } else {     
            $array = $object;   
        }   return $array; 
    }
}
