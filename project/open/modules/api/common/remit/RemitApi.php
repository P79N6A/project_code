<?php 
/**
 * 中信出款程序
 * 项目代码和数据库均是utf8的.但中信接收的是gbk的.切记转换
 * @author lijin
 */
namespace app\modules\api\common\remit;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Http;
use app\common\Logger;

set_time_limit(0); 

class RemitApi{
	/**
	 * gbk的xml解析类
	 */
	private $oRemitXml;
	/**
	 * 中信请求的url
	 */
	private $requrl;
	
	public function __construct($env){
		/**
		 * 账号配置文件
		 */
		$env = 'prod';
		$configPath = __DIR__ . "/config/{$env}.php";
		if( !file_exists($configPath) ){
			throw new \Exception($configPath."配置文件不存在",6000);
		}
		$this->config = include( $configPath );
		$this->requrl = $this->config['requrl'];
		
		// 中信的xml解析与组装
		$this->oRemitXml = new RemitXml;
	}
	/**
	 * 是否同城 同城标志 0：同城；1：异地
	 * @param $province
	 * @return 1,0
	 */
	public function getCityFlag($province ){
		$r = $province == $this->config['account_province'];
		return $r ? 0 : 1;
	}
	/**
	 * 1出款操作
	 * @return [
	 * 		'status' => 200, // http响应状态
	 * 		'data'   => [], // 接口返回结果
	 * ]
	 */
	public function remit($postData){
		//1 检测参数 后面要详细检查 @todo
		if(!$postData){
			return null;
		}
		
		//2 组合中信的xml
		$xml = $this->remitXml($postData);
		
		//3 向中信发送请求
		//@todo
//		if(YII_ENV_DEV){
//			$xml = file_get_contents(Yii::$app->basePath . '/log/testxml/test_remit_req3.xml');
//		}

		
		//3 向中信发送请求
		$result = $this->curlPost($this->requrl, $xml);
		return $result;
	}
	/**
	 * 2组合出款xml
	 * @param $postData
	 * @return string xml 格式gbk字符串 
	 */
	public function remitXml($postData){
		// 1 组合出款数据
		$data  = [
			'action' 				=> 'DLOUTTRN',
			'userName' 			=> $this->config['userName'],//<!--登录名 char(30)-->
			'payAccountNo'  => $this->config['account_no'],//付款账号
			'preFlg' 				=> 0,    //预约支付标志 0：非预约交易；1：预约交易
			'payType' 			=> '05', //付方式 00：汇票； 01：中信内部转账；02：大额支付；03：小额支付；04：同城票交char(2) ；05：网银跨行支付
			'citicbankFlag' 	=> 1, //中信标志 0：中信；1：他行; 目前全是跨行交易
			
			'recBankNo' => $postData['recBankNo'],//收款人所属银行行号,支付方式为05(网银跨行支付)时非空
			'clientID' 		=> $postData['clientID'], //客户流水号 char(20)
			'preDate' 		=> $postData['preDate'],//延期支付日期char(8) 格式YYYYMMDD
			'preTime' 	=> $postData['preTime'],//延期支付时间char(6) 格式hhmmss
			
			'recAccountNo' 		=> $postData['recAccountNo'],//收款人账号 
			'recAccountName'	=> $postData['recAccountName'],//收款人名称
			'tranAmount' 			=> $postData['tranAmount'],//金额 decimal(15,2)
			
			'cityFlag' 					=> $postData['cityFlag'],//同城标志 0：同城；1：异地
			'abstract' 					=> isset($postData['abstract']) ? $postData['abstract'] : '',//摘要 varchar(22)
		];
		
		//3 根据模板生成xml格式
		//return $this->array2xml($data);
		$xml = $this->tpl2xml("remit", $data);
		return $xml;
	}
	/**
	 * 2获取查询接口
	 */
	public function query($clientID){
		//1 检测参数 后面要详细检查 @todo
		if(!$clientID){
			return null;
		}
		
		//2 组合中信的xml
		$xml = $this->queryXml($clientID);
		
		//3 向中信发送请求 @todo
		//@todo
//		if(YII_ENV_DEV){
//			$xml = file_get_contents(Yii::$app->basePath . '/log/testxml/test_query1.xml');
//		}
		$result = $this->curlPost($this->requrl, $xml);
		return $result;
	}
	/**
	 * 2获取查询接口
	 */
	public function queryXml($clientID){
		$data = [
			'userName' => $this->config['userName'],//登录名
			'clientID' => $clientID, //客户流水号
		];

		return $this->tpl2xml("query", $data);
	}
	
	/**
	 * 获取银行编号
	 */
	public function bankno(){
		$xml = $this->tpl2xml("bankno", []);
		$result = $this->curlPost($this->requrl, $xml);
		return $result;
	}
	/**
	 * 提交数据
	 * @param array $data
	 * @param str xml xml要求是gbk的
	 * @return null
	 */
	private function curlPost($url, $xml){
		// 构建测试桩 @todo
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		if($env == 'dev'){
			return $this->curlPostTest($xml);
		}

		// 1 计算log
		$timeLog = new \app\common\TimeLog();
		
		//2 提前请求
		$curl = new \app\common\Curl();
		$curl -> addHeader([
			'Content-Length' => strlen($xml),
			'Content-Type' => 'application/octet-stream',  	
			'Connection' => 'Keep-Alive', // 维持长连接
			'Charset' => 'GBK', 
		]);
		$curl -> setOption(CURLOPT_CONNECTTIMEOUT,120);
		$curl -> setOption(CURLOPT_TIMEOUT,120);
		$xmlRes = $curl -> post($url, $xml);
		$httpStatus = $curl -> getStatus();
		
		//3 详细纪录请求与响应的结果
		$timeLog->save('remit',[ $url, $xml, $httpStatus, $xmlRes ]);
		
		//4 返回响应结果
		$data = null;
		if( $httpStatus == 200){
			try{
				$data = $this -> xml2array($xmlRes);
				$xmlRes = iconv('gbk', 'utf-8', $xmlRes);
			}catch(\Exception $e){

			}
		}
		
		return [
			'httpStatus' => intval($httpStatus),
			'xml'    => $xmlRes,
			'data'   => $data,
		];
	}
	/**
	 * 测试桩功能
	 * @param  [type] $xml [description]
	 * @return [type]      [description]
	 */
	private function curlPostTest($xml){
		$httpStatus = 0;
		$xmlRes = null;
		try{
			if(strpos($xml,'<action>DLOUTTRN</action>')){
				// 出款接口
				$httpStatus = file_get_contents(Yii::$app -> basePath . '/log/zx/remit_http.txt');
				$xmlRes = file_get_contents(Yii::$app -> basePath . '/log/zx/remit.xml');
			}elseif(strpos($xml,'<action>DLCIDSTT</action>')){
				// 查询接口
				$httpStatus = file_get_contents(Yii::$app -> basePath . '/log/zx/query_http.txt');
				$xmlRes = file_get_contents(Yii::$app -> basePath . '/log/zx/query.xml');
			}
		}catch(\Exception $e){

		}
		
		$data = null;
		if( $httpStatus == 200){
			try{
				$data = $this -> xml2array($xmlRes);
			}catch(\Exception $e){

			}
		}

		// 返回响应结果
		return [
			'httpStatus' => intval($httpStatus),
			'xml'    => $xmlRes,
			'data'   => $data,
		];
	}
	
	//////* xml转换程序 /////
	/**
	 * 将xml解析成数组
	 */
	public function xml2array( &$resxml ){
		return $this->oRemitXml->xml2array($resxml );
	}
	/**
	 * 转换成中信的xml格式
	 */
	public function array2xml( $data ){
		return $this->oRemitXml->array2xml($data );
	}
	public function tpl2xml($tplname, $data){
		return $this->oRemitXml->tpl2xml($tplname, $data);
	}
	//////* xml转换程序 /////
	
	
	
}