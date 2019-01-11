<?php 
/**
 * 学籍接口程序
 * @author lijin
 */
namespace app\modules\api\common\eduroll;
use app\common\RSA;
use app\modules\api\common\yeepay\QuickYeepay;

use app\common\Xmlparse;

class EduRollCrypt{
	private $rsa;
	public $privateKey;
	private $error;// 错误结果
	
	public function __construct($env){
		/**
		 * 账号配置文件
		 */
		$configPath = __DIR__ . "/config.{$env}.php";
		if( !file_exists($configPath) ){
			throw new \Exception($configPath."配置文件不存在",6000);
		}
		$this->config = include( $configPath );

		$this->rsa = new RSA();

		$this->publicKey  = $this->config['publicKey'];
		$this->privateKey = $this->config['privateKey'];
	}
	// 公钥加解密的不使用
	// 私钥加密，公钥解密
	public function encryptByPrivate( $str ){
	    return $this->rsa->encryptByPrivate($str, $this->privateKey);
	}
	public function decryptByPublic($str){
		return $this->rsa->decryptByPublic($str,$this->publicKey);
	}
	
	// 公钥加密，私钥解密
	public function encryptByPublic($str){
	    return $this->rsa->encryptByPublic($str,$this->publicKey);
	}
	public function decryptByPrivate($str){
		return $this->rsa->decrypt128ByPrivate($str,$this->privateKey);
	}
	
	/**
	 * 根据姓名，身份证获取学籍信息
	 * @param $name 姓名
	 * @param $idcode 身份证
	 * @return []
	 */
	public function get($data){
		// 加密参数
		$enparams = $this -> getEncryptParams($data);
		if( empty($enparams) ){
			$this->setError(6100, "提交参数为空");
			return null;
		}
		
		// 创建xml
		$xml = $this->createXML($enparams);
		// 发送请求
		$resxml =  $this->sent($xml);
		//echo $resxml;
		
		// @todo
		/*
		$resxml = '<?xml version="1.0" encoding="UTF-8"?><DATA><MESSAGE><STATUS>0</STATUS><VALUE>处理成功</VALUE></MESSAGE><RESULTS>ccS7dXx+b429aEfDr4WzovRKFS6urz2TdE/GGbrJtBateyCBkAT/fKdUJfLAIMbEk3aMLttyuukSGQS4iBnVpdS6VhmSDYsvijjTDZln3gXaYg6+BzyFIZzdfjFDvwtKzS0UOLDhtB1T6KFZrTyEcR38rwlVnf50fCKH5YbKwf+2/a8B1NChD2HdtJRQsujWIREkMpyoYoIPhhoYgubo45IVbWXl9YSmfyZIdSXFRzHE2Nj2ehcIlwAOs8TYm/CVzj9/fzQs1zHhat/ReNy+M/jaZ3SS5NrJOozu7RrucK7w8ZXiSnrHEAHD+SupZErsUPAsmRIwhnBU2EV8lMxWP6xUq8Tp3PJegCNitqhD6nrV7g93hGrP7oEslM/kXOShIXzahPaCl32H5dabjkNOhJG43KQYbnkm2YAjCnMqWCEtrYf4NTRugnExroBFMla9iSz9qlVmMFQzARxwFA45SXAje4FljlkGaGUB/ugn8tSf64pSWowzbfjPwVGkJgjatCo/ToNlF7OT6nHXESE4tihkRkA2cCYnB/fuaJD0dWnXt9bl0HhbOjv34uKgTVhKG4lu1/sHtV8QaccV7xo6EiOqQ8tDgTBHLq24dQd+5THbuS6ZEP8siIvMNZWoBT4Enr7EBpsVyRRRZIEryZFKFiRtCVxy2xJt7JNQM107ZF4=</RESULTS></DATA>';
		*/
		// 解析响应
		$arr =  $this->parseResponse( $resxml );
		return $arr;
	}
	/**
	 * 将xml解析成数组
	 */
	private function xml2array( &$resxml ){
		$xmlParse = new Xmlparse(true);
		$arr = $xmlParse -> parse($resxml);
		if( !is_array($arr) || empty($arr) ){
			return null;
		}
		return $arr;
	}
	/**
	 * 解析响应
	 * @param $resxml 
	 * @return bool:false | []
	 */
	private function parseResponse(&$resxml){
		$arr = $this->xml2array($resxml);
		if( !is_array($arr) || empty($arr) ){
			$this->setError(6101, "xml解析出错:".$resxml);
			return false;
		}
		
		// 判断是否空数据,一般不会出现这个
		if( !is_array($arr['MESSAGE']) ){
			$this->setError(6102, "MESSAGE字段不存在,数据获取为空");
			return false;
		}
		
		if( $arr['MESSAGE']['STATUS'] != '0' ){
			$this->setError($arr['MESSAGE']['STATUS'], $arr['MESSAGE']['VALUE']);
			return false;
		}
		
		// 返回结果
		try{
			$resultXML = $this->decryptByPrivate($arr['RESULTS']);
		}catch(\Exception $e){
			$this->setError(6105,"RESULTS解密失败,原文：".$arr['RESULTS']);
			return false;
		}
		
		$result = $this->xml2array($resultXML);
		if( empty($result) ){
			$this->setError(6106,"解析结果出错".$resultXML);
			return false;
		}
		return $result;
	}
	/**
	 * 错误信息处理
	 */
	public function setError($res_code, $res_data){
		$this->error = ['res_code'=>$res_code,'res_data'=>$res_data];
	}
	/**
	 * 返回错误
	 */
	public function getError(){
		return $this->error;
	}

	/**
	 * 加密各参数
	 * @param $name 姓名
	 * @param $idcode 身份证
	 */
	private function getEncryptParams($data){
		if( !is_array($data) || empty($data) ){
			return null;
		}
		
		// 基本参数设置参数设置
		$batch = ['servicecode' => $this->encryptByPrivate($this->config['servicecode'])];
		if( isset($data['name']) ){
			$batch['name'] =  $this->encryptByPrivate($data['name']);
		}
		if( isset($data['idcode']) ){
			$batch['idcode'] =  $this->encryptByPrivate($data['idcode']);
		}
		if( isset($data['educationdegree']) ){
			$batch['educationdegree'] =  $this->encryptByPrivate($data['educationdegree']);
		}
		if( isset($data['graduate']) ){
			$batch['graduate'] =  $this->encryptByPrivate($data['graduate']);
		}
		if( isset($data['studystyle']) ){
			$batch['studystyle'] =  $this->encryptByPrivate($data['studystyle']);
		}
		if( isset($data['enroldate']) ){
			$batch['enroldate'] =  $this->encryptByPrivate($data['enroldate']);
		}

		$params =  [
			'userid'      => $this->config['userid'],
			'password'    => $this->encryptByPrivate($this->config['password']),
			'batch'		  => $batch,
		];
		return $params;
	}
	/**
	 * 向服务端发送xml请求
	 * @param $xml
	 */
	private function sent($xml){
		// 创建对象
		$o = new \stdClass();
		$o -> requestParameter = $xml;
		
		// 请求数据
		$client = new \SoapClient($this->config['url']);
		$st = $client -> queryServiceItemByCode($o);
		if( empty($st) || empty($st->return) ){
			return null;
		}
		return $st->return;
	}
	/**
	 * 创建xml文件
	 */
	private function createXML($enparams){
		$userid   = $enparams['userid'];
		$password = $enparams['password'];
		$batch    = $enparams['batch'];
		
		$string = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<ROOT>
	<METADATA>
		<USERID>{$userid}</USERID>
		<PASSWORD>{$password}</PASSWORD>
	</METADATA>
	<BATCHES>
		<BATCH>
XML;
		foreach($batch as $key=>$value){
			$key = strtoupper($key);
			$string .= "<{$key}>{$value}</{$key}>";
		}
		$string .= <<<XML
</BATCH>
	</BATCHES>
</ROOT>
XML;
		return $string;
	}
}