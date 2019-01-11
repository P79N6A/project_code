<?php
/**
 * java决策基类
 */
namespace app\modules\sfapi\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;

class JavaCrif
{
	const PRO_CODE_REG = 'xhh_reg';//注册决策
	const PRO_CODE_LOAN = 'xhh_loan_1';//借款申请决策
    const PRO_CODE_FRAUD = 'xhh_loan_2';//反欺诈决策
    const PRO_CODE_SCORE = 'xhh_reloan';//评分卡决策
    const PRO_CODE_TIANQI = 'anti_tianqi';//天启决策
    const PRO_CODE_PERIODS = 'anti_fq_decision';//分期决策
    const PRO_CODE_TXSKEDU = 'anti_chis_decision';//天行学信决策
    const PRO_CODE_OPERATOR = 'OPERATOR';//运营商报告决策
	private $wsdl_url;

	function __construct()
    {
    	if (SYSTEM_PROD) {
    		$this->wsdl_url = "http://localhost:8091/ws/S1Public?wsdl";
    	} else {
    		$this->wsdl_url = "http://47.93.121.86:8092/ws/S1Public?wsdl";
    	}
    }

	//请求决策系统(对外)
    public function queryCrif($request_id,$data,$process_code)
    {
        $loan_data = [
            'request_id' => $request_id,
            'process_code' => $process_code,
            'params_data' => $data,
        ];
        $result = $this->sendRequest($loan_data);
        if (empty($result)) {
            Logger::dayLog('queryCrif', '决策结果为空', $result, $data);
            return [];
        }
        if (isset($result['res_code']) && $result['res_code'] != 0) {
            Logger::dayLog('queryCrif', '决策异常', $result, $data);
            return [];
        }
        return $result;
    }
    //请求决策系统(对内)
   	private function sendRequest($params){
		Logger::dayLog('api/sendData',$params);
		if(empty($params)) return false;
		$xml = $this->createXml($params);
		$result = $this->sent($xml);
		if(empty($result)) return false;
		$parseresult = $this->parseResponse($result);
		// Logger::dayLog('api/resArray',$parseresult);
		return $parseresult;
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
// Logger::dayLog('api/createXml',$params,$string);
			return $string;
	}

	/**
	 * 向服务端发送xml请求
	 * @param $xml
	 */
	private function sent($xml){
		$url = $this->wsdl_url;
		libxml_disable_entity_loader(false);
		// ini_set('soap.wsdl_cache_enabled', "0");
		$client = new \SoapClient($url);
    	$result = $client->execute($xml);
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

	/**
	 * 将xml解析成数组
	 */
	private function xml2array( $resxml ){
		libxml_disable_entity_loader(true);
		$xmlstring = simplexml_load_string($resxml, 'SimpleXMLElement', LIBXML_NOCDATA);   
		$xmlArray = json_decode(json_encode($xmlstring),true);
		// Logger::dayLog('api/xmlArray',$xmlArray);
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

	private function returnInfo($result, $info)
    {
        $this->info = $info;
        return $result;
    }
}