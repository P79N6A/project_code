<?php

namespace app\models\org;

use Yii;
use app\common\Common;
use app\common\Apihttp;
use app\common\Curl;
use app\common\Logger;

/**
 * 统一对外开放接口
 */
class OrgApi 
{
	private $org_url;
	private $appid; 
	private $secret; 
	private $service;
	public function __construct()
	{	
		$config = $this->getConfig();
		$this->org_url = $config['org_url'];
		$this->appid = $config['appid']; //接口账号
		$this->secret = $config['secret']; //秘钥
		$this->service = $config['service']; //服务号
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
	 * [runOrg 天启数据对外入口]
	 * @return [type] [description]
	 */
	public function runOrg($data)
	{
		#1, 获取天启数据
		$org_data = $this->getOrigin($data);
		if (!empty($org_data)) {
			return $org_data;
		}
		#2, 没有则请求接口获取数据
		$org_data = $this->setOrigin($data);
		return $org_data;
 	}
 	/**
 	 * [getOrigin 获取天启数据]
 	 * @param  [type] $data [description]
 	 * @return [type]       [description]
 	 */
 	private function getOrigin($data)
 	{
 		$origin = new DcOrigin();
        $org_data = $origin->getResult($data);
        if (empty($org_data)) {
        	return [];
        }
        $allData = [
			'credit_score' => (int)$org_data['credit_score'],
			'model_score_v2' => (int)$org_data['model_score_v2'],
			'tianqi_score_v2' => (int)$org_data['tianqi_score_v2'],
			'is_black' => (int)$org_data['is_black'],
        ];
        return $allData;
 	}
	/**
	 * [setDetail 请求天启接口入库]
	 * @param  [type] $json_url [description]
	 * @return [array]           [description]
	 */
	private function setOrigin($org_data)
	{

		//请求前本地记录 
        $origin = new DcOrigin();
        $org = $origin->saveData($org_data);
        if (!$org) {
            Logger::dayLog('origin/getOrigin', '百度请求记录失败', $org_data,$origin->errors);
        }
        //标准化参数
        $params = $this->setParams($org_data);
        if (empty($params)) {
        	Logger::dayLog('origin/getOrigin', '标准化参数异常', $org_data);
        	return [];
        }
        $url = $this->org_url;
        //生成参数串
        $dataStr= $this->getDataStr($params);
        if (empty($dataStr)) {
        	Logger::dayLog('origin/getOrigin', '生成参数串异常', $params);
        	return [];
        }
        //请求接口
        $origin_result = $this->sendOrigin($url,$dataStr);
        if (empty($origin_result)) {
        	return [];
        }
        //更新数据
        $res = $origin->updateOrgInfo($origin_result);
        if (!$res) {
            Logger::dayLog('origin/getOrigin', '请求更新失败', $org_data,$origin->errors);
        }
        // if (isset($origin_result['code']) && $origin_result['code'] != 'R0000') {
        // 	Logger::dayLog('origin/getOrigin', '接口异常', $origin_result);
        // 	return [];
        // }
        $allData = [
			'credit_score' => $origin_result['credit_score'],
			'model_score_v2' => $origin_result['model_score_v2'],
			'tianqi_score_v2' => $origin_result['tianqi_score_v2'],
			'is_black' => $origin_result['is_black'],
        ];
        return $allData;
	}

	/**
	 * [getSign 生成sign]
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	private function getDataStr($params)
	{
		$sign_str = '';
		if (!is_array($params) || empty($params)) {
			return '';
		}
		foreach ($params as $k => $val) {
			$sign_str .= $k.'='.$val.'&';
		}
		//数据串
		$sign = $this->getSign($sign_str);

		$sign_str .= 'sign='.$sign;
		return $sign_str;
	}

	private function getSign($sign_str)
	{
		//生成sign
		$sign_str .= $this->secret;
		$sign = md5($sign_str);
		return $sign;
	}
	/**
	 * [setParams 标准化参数]
	 * @param [type] $org_data [description]
	 */
	private function setParams($org_data)
	{
		if (!is_array($org_data) || empty($org_data)) {
			return [];
		}
		$params = [
			'appid' => (int)$this->appid,
			'idcard' => (string)$org_data['idcard'],
            'name' => (string)$org_data['name'],
            'phone' => (string)$org_data['phone'],
            'service' => (string)$this->service,
		];
		return $params;
	}
	/**
	 * [sendOrigin 请求天启接口整合数据]
	 * @param  [type] $url    [description]
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	private function sendOrigin($url,$params)
	{
		if (empty($url) || empty($params)) {
			return [];
		}
		$curl = new Curl();
        $res = $curl->dataPost($params,$url);
        if (isset($res['http_status']) && $res['http_status'] != 200) {
        	Logger::dayLog('origin/sendOrigin', '请求异常', $params,$res);
        	return [];
        }
        $result = $res['result'];
        $org_result = json_decode($result);
        $data = $org_result->data;
        $allRes = [
        	'message' => isset($org_result->message) ? $org_result->message : '',
        	'code' => isset($org_result->code) ? $org_result->code : '',
        	'credit_score' => isset($data->credit_score) ? $data->credit_score : 0,
        	'model_score_v2' => isset($data->model_score_v2) ? $data->model_score_v2 : 0,
        	'tianqi_score_v2' => isset($data->tianqi_score_v2) ? $data->tianqi_score_v2 : 0,
        	'is_black' => isset($data->is_black) ? $data->is_black : 0,
        ];
        return $allRes;
	}
}
