<?php

namespace app\models\xs;

use app\common\Common;
use app\common\Apihttp;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Curl;
use app\models\rc\RcBaidulbs;

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
        if ($apiType == 'LBS') {
            $url = $this->config['lbs_url'];
        } else {
            $url = $this->config['url'];
        }
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
     * 获取百度金融LBS地址验证信息
     */
    public function getBaiduLbsInfo($data)
    {
        $bd_data = [];
        $oBd = (new XsBaidulbs) -> getResult($data['mobile'],$data['identity']);
        // 拼接百度lbs信息
        if($oBd){
            $bd_data = (new YArray) -> getByKeys($oBd,[
                'retCode',
                'retMsg',
            ],0);
            $bd_data['result'] = json_decode($oBd['result_info'],true);
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
    //百度LBS地址校验接口
    public function queryBaiduLbs($data)
    {      
        //标准化LBS查询参数
        $params = $this->getLbsParams($data);
        $apiType = 'LBS';
        //请求前入库
        $baidu = new XsBaidulbs();
        $save_lbs = $baidu->saveData($data,$params['reqid']);
        if (!$save_lbs) {
            Logger::dayLog('BaiduLbs/save_lbs', '百度请求保存失败', $params,$baidu->errors);
        }
        //请求接口
        $baidu_lbs = $this->sendRequest($params,$apiType);
        //LBS结果更新
        $updata_lbs = $baidu->updateLbsInfo($baidu_lbs);
        if (!$updata_lbs) {
            Logger::dayLog('BaiduLbs/updata_lbs', '百度请求更新失败', $params,$baidu->errors);
        }
        return $baidu_lbs;
    }

    /** 
     * 根据起点坐标和终点坐标测距离 
     * @param  [array]   $from  [起点坐标(经纬度),例如:array(118.012951,36.810024)] 
     * @param  [array]   $to    [终点坐标(经纬度)] 
     * @param  [bool]    $km        是否以公里为单位 false:米 true:公里(千米) 
     * @param  [int]     $decimal   精度 保留小数位数 
     * @return [string]  距离数值 
     */  
    public function getDistance($from,$to,$km=true,$decimal=2){  
        sort($from);  
        sort($to);  
        $EARTH_RADIUS = 6370.996; // 地球半径系数  
          
        $distance = $EARTH_RADIUS*2*asin(sqrt(pow(sin( ($from[0]*pi()/180-$to[0]*pi()/180)/2),2)+cos($from[0]*pi()/180)*cos($to[0]*pi()/180)* pow(sin( ($from[1]*pi()/180-$to[1]*pi()/180)/2),2)))*1000;  
          
        if($km){  
            $distance = $distance / 1000;  
        }  
      
        return round($distance, $decimal);  
    }

    //百度LBS地址校验参数
    private function getLbsParams($data)
    {
        $params = [];
        if (!is_array($data) || empty($data)) {
            return $params;
        }    
        $params = [
                'sp_no' => $this->config['sp_no'],
                'name' => isset($data['realname']) ? $data['realname'] : '',
                'id_type' => '1',
                'id_no' => isset($data['identity']) ? $data['identity'] : '',
                'phone' => isset($data['mobile']) ? $data['mobile'] : '',
                'agencyCode' => '',
                'datetime'=>$this->microtime_int(),
                'sign_type'=>$this->config['sign_type'],
                'reqid'=>'LBS_'.date('YmdHis').rand(10000,99999),
            ];
            //家庭住址
            if (isset($data['home_address']) && !empty($data['home_address']) && isset($data['home_city']) && !empty($data['home_city'])) {
                $params['home_address'] = $data['home_address'];
                $params['home_city'] = $data['home_city'];
            }
            //公司住址
            if (isset($data['company_address']) && !empty($data['company_address']) && isset($data['company_city']) && !empty($data['company_city'])) {
                $params['company_city'] = $data['company_city'];
                $params['company_address'] = $data['company_address'];
            }
            //门店地址
            if (isset($data['bus_shop_address']) && !empty($data['bus_shop_address']) && isset($data['bus_shop_city']) && !empty($data['bus_shop_city'])) {
                $params['bus_shop_city'] = $data['bus_shop_city'];
                $params['bus_shop_address'] = $data['bus_shop_address'];
            }
            //未知固定地址
            if (isset($data['fix_address']) && !empty($data['fix_address'])) {
                $params['fix_address'] = $data['fix_address'];
                $params['fix_address_lng'] = $data['longitude'];
                $params['fix_address_lat'] = $data['latitude'];
                $params['fix_addr_coorType'] = 5;
            }
        return $params;
    }
}
