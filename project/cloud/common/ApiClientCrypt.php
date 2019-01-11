<?php
namespace app\common;
use Yii;
use app\common\ApiCrypt;

define("XHH_CLIENT_APP_ID",'2810335722015');
define("XHH_CLIENT_AUTH_KEY",'24BEFILOPQRUVWXcdhntvwxy');


class ApiClientCrypt extends ApiCrypt
{
	//这两个参数由用户自行设置
	/**
	 * 客户自己的app_id;需要在先花花开发平台申请
	 */
	private $app_id = XHH_CLIENT_APP_ID;

	/**
	 * 客户自己的auth_key;需要在先花花开发平台申请
	 */
	private $auth_key = XHH_CLIENT_AUTH_KEY;// 商户自己设置的key

	//['open.xianhuahua.com'=>'127.0.0.1'] 仅支持一个
	private $hostMap = [];

	/**
	 * 先花花开发平台地址
	 */
	private $xhApiDomain='http://open.xianhuahua.com/api/';


	/**
	 * 设置app_id
	 * @param $app_id;
	 */
	public function setAppId($app_id){
		$this->app_id = $app_id;
	}
	/**
	 * 设置auth
	 * @param $app_id;
	 */
	public function setAuthKey($auth_key){
		$this->auth_key = $auth_key;
	}

	/**
	 * 绑定ip和host方便测试
	 */
	public function setHost($ip,$domain){
		$this->hostMap[$domain] = $ip;
	}

	// 向开发平台发送数据包
	public function sent($path, $data){
		// if (!SYSTEM_PROD) {
			$url  = $this->buildUrl($path);
		// } else {
			// $url = 'http://182.92.80.211:8091/api/'.$path;
		// }
		$data = $this->buildRequest($data);
		$request = ['app_id'=> $this->app_id, 'data'=>$data];
		return $this->curlByHost($url, $request, $this->hostMap);
	}
	// 组合api链接地址
	private function buildUrl($path){
		return $this->xhApiDomain . $path;
	}
	/**
	 * 建立请求
	 */
	public function buildRequest($data){
		return $this->buildData($data, $this->auth_key);
	}

	// 客户端对响应进行解析
	public function parseResponse($data){
		$return = json_decode($data,true);
		// 判断数据格式是否正确
		if(empty($return) || !is_array($return) || !isset($return['res_code'])){
			return ['res_code'=>20,'res_data'=>'无响应内容'];
		}
		if( $return['res_code'] != '0' ){
			return $return;
		}
		return $this->parseReturnData($return['res_data']);
	}
	/**
	 * 直接格式化返回结果
	 */
	public function parseReturnData($data){
        return $this->parseData($data, $this->auth_key);
    }
	/**
	 * 指定host的curl请求
	 * $hostMap = ['www.baidu.com'=>$ip]
	 */
	private function curlByHost($url, $data, $hostMap=null){
		$httpHeader = null;
		if( is_array($hostMap) && !empty($hostMap) ){
			// 获取host,ip映射关系
			list($host, $ip) = each($hostMap);

			// 将链接指定为ip地址
			$url = str_ireplace($host, $ip, $url);

			// 修改host信息
			$httpHeader = array('Host: ' . $host);
		}

		return $this->curlData($url, $data, $httpHeader);
	}
    /**
     * 接口请求方式
     * @param unknown $url
     * @param unknown $data
     * @return mixed
     */
    private function curlData($url, $data, $httpHeader=null) {
    	return \app\common\Http::interface_post($url, $data);
    }
}
