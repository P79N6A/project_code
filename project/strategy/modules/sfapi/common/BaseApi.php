<?php
/**
 * 接口基类
 */
namespace app\modules\sfapi\common;

use app\common\Logger;
use app\common\Curl;
use app\common\ApiSign;
use yii\helpers\ArrayHelper;
use app\models\Request;
use app\models\loan\FavoriteContacts;
class BaseApi {
	private $wsdl_url;
	private $anti_key;	
	private $cloud_url;
	private $anti_url;

	function __construct()
    {
    	if (SYSTEM_PROD) {
    		$this->wsdl_url = "http://localhost:8091/ws/S1Public?wsdl";
    		$this->cloud_url = "http://100.112.35.139:8082/api/";
    		$this->anti_url = "http://100.112.35.139:8081/api/analysis";
    	} else {
    		$this->wsdl_url = "http://47.93.121.86:8092/ws/S1Public?wsdl";
    		$this->cloud_url = "http://182.92.80.211:8082/api/";
    		$this->anti_url = "http://182.92.80.211:8001/api/analysis";
    	}
    	$this->anti_key = 'spLu1bSt3jXPY8ximZUf9k7F';
    }

	/**
	 * 将xml解析成数组
	 */
	private function xml2array( $resxml ){
		libxml_disable_entity_loader(true);
		$xmlstring = simplexml_load_string($resxml, 'SimpleXMLElement', LIBXML_NOCDATA);   
		$xmlArray = json_decode(json_encode($xmlstring),true);
		Logger::dayLog('api/xmlArray',$xmlArray);
		$result = isset($xmlArray['Body']['Application']['Variables'])?$xmlArray['Body']['Application']['Variables']:[];
		if( !is_array($result) || empty($result) ){
			return null;
		}
		$categories = isset($xmlArray['Body']['Application']['Categories'])?$xmlArray['Body']['Application']['Categories'] : [];
		if (!empty($categories)) {
			foreach ($categories as $key => $value) {
				foreach ($value as $val) {
					if (isset($val['Variables'])) {
						$result[$key][$val['Variables'][key($val['Variables'])]] = key($val['Variables']);
					} else {
						$result[$key][$val[key($val)]] = key($val);
					}
				}
			}
		}
		return $result;
	}
	/**
	 * 解析响应
	 * @param $resxml 
	 * @return bool:false | []
	 */
	private function parseResponse($obj){
		if(empty($obj)) return false;
		$errorCode 		= $obj->errorCode;
		$executionTime 	= $obj->executionTime;
		$log 			= $obj->log;
		$message 		= $obj->message;
		if(isset($errorCode) && $errorCode!=0){
			return ['res_code'=>$errorCode,'res_data'=>$message];
		}
		$result = $this->xml2array($message);
		return $result;
	}
	public function sendRequest($params){
		Logger::dayLog('api/sendData',$params);
		if(empty($params)) return false;
		$xml = $this->createXml($params);
		$result = $this->sent($xml);
		if(empty($result)) return false;
		$parseresult = $this->parseResponse($result);
		Logger::dayLog('api/resArray',$parseresult);
		return $parseresult;
	}


	/**
	 * 向服务端发送xml请求
	 * @param $xml
	 */
	private function sent($xml){
		$url = $this->wsdl_url;
		libxml_disable_entity_loader(false);
		$client = new \SoapClient($url);
    	$result = $client->execute($xml);
		return $result;
	}
	/**
	 * Undocumented function
	 * 生成xml
	 * @param [type] $params
	 * 
	 * @return void
	 */
	private function createXml($params){
		$request_id = ArrayHelper::getValue($params,'request_id');
		$process_code = ArrayHelper::getValue($params,'process_code');
		$params_data = ArrayHelper::getValue($params,'params_data');
		$other_data = ArrayHelper::getValue($params,'other_data');
		
		 $string = <<<XML
<?xml version="1.0" encoding="UTF-8" ?> 
    <StrategyOneRequest>
    	<Header>
   			<InquiryCode>{$request_id}</InquiryCode>
   			<ProcessCode>{$process_code}</ProcessCode>
    	</Header>
		<Body>
			<Application>
				<Variables>
XML;
				foreach($params_data as $key=>$value){
                    $string .= "<{$key}>{$value}</{$key}>";
                }
				$string .= <<<XML
 				</Variables>
XML;
				if(!empty($other_data)){
					$string .= "<Categories>";
					foreach($other_data as $key=>$value){						
						$_keyArr = explode('_',$key);//的当有多个相同的键时，需要对数组键值进行区分key_num
						$key = $_keyArr[0];
						$string .= "<{$key}>";
						$string .= "<Variables>";
						foreach($value as $k=>$v){
							 $string .= "<{$k}>{$v}</{$k}>";
						}
						$string .= "</Variables>";
						$string .= "</{$key}>";
					}
					$string .= "</Categories>";
				}

$string .= <<<XML
    		</Application>
   		 </Body>
    </StrategyOneRequest>
XML;
Logger::dayLog('api/createXml',$params,$string);
			return $string;
	}

	//请求cloud系统
	public function queryCloud($postdata,$url)
	{
		$c_url = $this->cloud_url.$url;
        $curl = new Curl();
        $sign_data = (new ApiSign)->signData($postdata,1);
        $res_c = $curl->post($c_url,$sign_data);
        $res = json_decode($res_c,true);
        if (empty($res)) {
        	Logger::dayLog('api/queryCloud','接口错误',$res_c,$c_url,$postdata);
			return $this->returnInfo(false,'获取数据失败');
        }
        $isVerify = (new ApiSign)->verifyCloud($res['data'], $res['_sign']);
		if (!$isVerify) {
			Logger::dayLog('api/verifyCloud','验签失败',$res,$c_url,$postdata);
			return $this->returnInfo(false,'验签失败');
		}
		$data = json_decode($res['data'],true);
		if (isset($data) && $data['rsp_code'] != '0') {
			Logger::dayLog('api/rsp_code','接口异常',$data,$c_url);
			return $this->returnInfo(false, $data['rsp_msg']);
		}
		return $this->returnInfo(true, $data);
	}

	protected function returnInfo($result, $info)
    {
        $this->info = $info;
        return $result;
    }
    //请求运营商分析报告
    public function queryAnti($data)
    {
    	if (empty($data['add_url']) || !isset($data['add_url'])) {
    		Logger::dayLog('api/queryAnti',$data);
			return $this->returnInfo(false,'通讯录地址不能为空');
    	}
		$address = $this->getAddress($data);
		$operator = $this->getOperator($data);
		$sendData = [
			'request_id' => (string)(isset($data['yy_request_id']) ? $data['yy_request_id'] : ''),
			'user_id'=>(string)(isset($data['identity_id']) ? $data['identity_id'] : ''),
			'loan_id'=>(string)(isset($data['loan_id']) ? $data['loan_id'] : ''),
			'phone'=>(string)(isset($data['phone']) ? $data['phone'] : ''),
			'identity'=>(string)(isset($data['idcard']) ? $data['idcard'] : ''),
			'aid'=>(string)(isset($data['aid']) ? $data['aid'] : ''),
			'address'=>(string)$address,
			'operator'=>(string)$operator,
			'relation'=>(string)(isset($data['relation']) ? $data['relation'] : ''),
		];
		$sign = $this->getSign($sendData);
		$sendData['sign']=$sign;
		$sendData  = json_encode($sendData,JSON_UNESCAPED_UNICODE);
		$a_url = $this->anti_url;
		$res = Curl::dataPost($sendData,$a_url);
		if (isset($res['http_status']) && $res['http_status'] !== 200) {
			Logger::dayLog('api/queryAnti',$res,$a_url,$sendData);
			return $this->returnInfo(false,'请求运营商数据失败');
		}
		$res = $res['result'];
		$res = json_decode($res,true);
		if (empty($res)) {
			Logger::dayLog('api/queryAnti',$res,$a_url,$sendData);
			return $this->returnInfo(false,'获取运营商数据失败');
		}
		if (isset($res['code']) && $res['code'] != '0') {
			Logger::dayLog('api/queryAnti',$res,$a_url,$sendData);
			return $this->returnInfo(false, $res['msg']);
		}
		return $this->returnInfo(true, $res['data']);
    }

    private function getAddress($data,$type = '2')
    {
    	if (SYSTEM_PROD) {
    		//正式
    		$add_data = [
	    		'type'=>$type,
	    		'data'=>isset($data['add_url']) ? $data['add_url'] : '',
	    	];
    	} else {
    		//测试
    		$add_data = [
	    		'type'=>$type,
	    		'data'=>'http://182.92.80.211:8104/mobile/api/phone/index',
	    	];
    	}
    	$add_data = json_encode($add_data, JSON_UNESCAPED_UNICODE);
    	return $add_data;
    }

    private function getOperator($data,$type = '1')
    {
    	$ret = [
	    		'type'=>$type,
	    		'data'=>isset($data['yy_request_id']) ? $data['yy_request_id'] : 0,
    		];
    	return json_encode($ret, JSON_UNESCAPED_UNICODE);
    }

    //	数据加密
    private function getSign($data)
    {
    	$str = '';
    	ksort($data);
    	foreach ($data as $k => $v) {
    		$str .= $k.'='.$v.'&';
    	}
    	$str = rtrim($str,'&');
    	$sign = md5(substr(md5($str),0,30).$this->anti_key);
    	return $sign;
    }	
	
}