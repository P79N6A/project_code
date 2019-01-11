<?php

namespace app\models\phonelab\pingan;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Curl;
use app\common\Logger;
use app\models\phonelab\PhoneInterface;

/**
 * 凭安统一对外开放接口
 */
class PingAnApi implements PhoneInterface
{
	private $config;
    private $phone_tag_api; //号码标签接口

    public function __construct() {
        // 获取配置文件
        $this->config = $this->getConfig();
        $this->phone_tag_api = 'phonetag';
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
     * 调用凭安API获取手机号标签信息
     * @param $phone
     * @return array
     */
    public function getPhoneInfo($phone)
    {
        if (empty($phone)) {
            return [];
        }
        # 获取请求参数
        $authParam = $this->getAuthParam();
        if (empty($authParam)) {
            Logger::dayLog('pingan','获取请求参数异常',$phone);
            return [];
        }
        # set Param
        $authParam['phone'] = $phone;
        # 请求凭安接口
        $phone_tag = $this->sendApi($authParam,$this->phone_tag_api);
        if (empty($phone_tag)) {
            return [];
        }
        return $phone_tag;
    }
    /**
     * 发送请求接口
     * @param $arr
     * @return mixed
     */
    public function sendApi($params,$apiurl)
    {
        if (empty($params) || !is_array($params) || empty($apiurl)) {
            return [];
        }
        $url = $this->buildUrl($apiurl);
        if (empty($url)) {
            return [];
        }
        $curl = new Curl();
        $res = $curl->post($url,$params);
        Logger::dayLog('sendApi/res',$res,$apiurl,json_encode($params));
        $array_res = json_decode($res,true);
        if (empty($array_res)) {
            return [];
        }

        if (isset($array_res['result']) && $array_res['result'] != 0) {
            Logger::dayLog('sendApi/error',$this->config['error_code'][$array_res['result']],$res,$apiurl,json_encode($params));
            return [];
        }
        $data = ArrayHelper::getValue($array_res,'data','');
        return $data;
       

    }
    
    /**
     * 发送请求接口
     * @param $arr
     * @return mixed
     */
    public function buildUrl($apiurl)
    {
        $url = ArrayHelper::getValue($this->config,'url','');
        if (empty($url)) {
            return '';
        }
        $real_url = $url.trim($apiurl,'/');
        return $real_url;
    }  

    /**
     * 凭安认证参数
     * @param $arr
     * @return mixed
     */
    private function getAuthParam(){
        $config_param = $this->config;
        $pname = ArrayHelper::getValue($config_param,'pname','');
        $pkey = ArrayHelper::getValue($config_param,'pkey','');
        $ptime = time();
        if (empty($pname) || empty($pkey) || empty($ptime)) {
            return [];
        }
        $vkey=md5($pkey."_".$ptime."_".$pkey);
        if (empty($vkey)) {
            return [];
        }
        $params = [
            'pname' => $pname,
            'pkey' => $pkey,
            'ptime' => $ptime,
            'vkey' => $vkey,
        ];
        return $params;
    }
}
