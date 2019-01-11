<?php

namespace app\commonapi\apiInterface;

use app\commonapi\Apihttp;
use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use ReflectionClass;
use Yii;

class Sinaremit extends Apihttp {

    
    
	/**
    * @abstract 新浪出款接口
    * @param [aid,req_id]
    * @return [true,false]
    * */
    public function outBlance( $params ){
    	$param_map = ['req_id', 'user_id', 'cardno', 'settle_amount', 'callbackurl', 'ip'];
    	if( !$this->validParamMap($param_map, $params) )
    		$ret = ['res_code'=>'-999','res_msg'=>'参数不匹配'];
    	$url = "sinapay/remit" ;
    	$openApi = new ApiClientCrypt;
    	$res = $openApi->sent($url, $params);
    	$result = $openApi->parseResponse($res);
    	Logger::errorLog($params['req_id']."--".print_r($result, true), 'sinaremit');
    	//新浪出款，开放平台返回的结果里，[0,3,6]都表示提交
    	if( $result['res_code'] === 0 ){
    		$ret = ['res_code'=>'0000','res_msg'=>$result['res_data']];
    	}else{
    		$ret = ['res_code'=>$result['res_code'],'res_msg'=>$result['res_data']];
    	}
    	return $ret;
    	
    }
	
}
