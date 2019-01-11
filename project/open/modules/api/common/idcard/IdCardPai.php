<?php 
/**
 * 量化派身份验证
 * @author lijin
 */
namespace app\modules\api\common\idcard;
use app\common\RSA;

class IdCardPai{
	private $rsa;
	public $errinfo;// 错误结果
	private $config=[];
	
	public function __construct($env){
		$this->config = [
			'appId' => "0010",
			'appKey' => "e95bbd54",
		];
		if($env == 'prod'){
			$this->config['url'] ="http://openapi.quantgroup.cn/kauth/ZX/queryIDCard";
		}else{
			$this->config['url'] = "http://61.50.125.14:8001/ZX/queryIDCard";
		}
	}
	
	/**
	 * 根据姓名，身份证获取学籍信息
	 * @param $name 姓名
	 * @param $idcard 身份证
	 * @return []
	 */
	public function get($username, $idcard, $userId){
		//1 加密参数
		$timeunit = time() * 1000;
		$appId = $this->config['appId'];
		$appKey = $this->config['appKey'];
		$content = [
			'appId' => $appId,
			'timeunit' => $timeunit,
			'token' => md5("timeunit={$timeunit}appkey={$appKey}"),
			//'token' => md5("{$appKey}{$timeunit}"),
			'userId' => $appId. '_' . $userId,
			'idCardCode'  => $idcard,
			'idCardName' => $username,
			
		];
		
		//2 获取响应
		$resContent = $this->send($this->config['url'], $content );
		//@todo
		//$resContent = '{"result":"00","idCardName":"吴冬芳","idCardCode":"51372219890920053X","idCardPhoto":"data:image/png;base64,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","message":"一致"}';

		//3 解析响应 : 解密 验签 
		$arr =  $this->parseResult( $resContent );
		return $arr;
	}
	/**
	 * 返回错误信息
	 */
	public function returnError($result, $errinfo){
		$this->errinfo = $errinfo;
		return $result;
	}
									 
	/**
	 * 向服务端发送请求
	 */
	private function send($url, $params){
		$url = $url . '?' . http_build_query($params);
		$result = \app\common\Http::getCurl($url);
		return $result;
	}
	/**
	 * 解析响应结果
	 * @param $str
	 * @return null | []
	 */
	private function parseResult($resContent){
		//1 解密返回数据
		if(!$resContent){
			return $this->returnError(null, "响应错误"); // 这个表示超时引起的
		}

		//2 json 解析
		$data = json_decode($resContent, true);
		$result = is_array($data) && isset($data['result']) && $data['result'] == '00';
		if(!$result){
			$err = isset($data['errorcode']) ? $data['errorcode'] : '';
			$err2 = isset($data['message']) ? $data['message'] : '';
			return $this->returnError(null, $err.$err2); 
		}
		
		//3 返回结果
		return $data;
	}
}