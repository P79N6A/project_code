<?php
namespace app\modules\api\common\baidurisk;
use Yii;
use app\common\Logger;
use app\common\Curl;
class RiskApi {
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
		$configPath = __DIR__ . "/config.php";
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
	public function sendRequest($data){
		$postdata = [
			'sp_no'=>$this->config['sp_no'],
			'service_id'=>$this->config['service_id'],
			'reqid'=>'risk_'.date('YmdHis').rand(10000,99999),
			'name'=>$data['name'],
			'identity'=>$data['idcard'],
			'phone'=>$data['phone'],
			'datetime'=>$this->microtime_int(),
			'sign_type'=>$this->config['sign_type']
		];
		$postdata['sign'] = $this->getSign($postdata);
		$result = $this->postCurl($postdata);
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
	private function postCurl($postdata){
		$curl = new Curl();
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->setOption(CURLOPT_TIMEOUT, 30);
		$content = '';
		$url = $this->config['url'];
		$content = $curl->post($url, $postdata);
		//$content = json_decode($content,true);
        $status = $curl->getStatus();
        Logger::dayLog("baidurisk","请求信息", $url, $postdata,"http状态", $status,"响应内容", $content);
        return $content;
	}
}
