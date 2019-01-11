<?php

namespace app\commonapi\apiInterface;

use app\commonapi\Apihttp;
use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use ReflectionClass;
use Yii;

class Sinaopenacc extends Apihttp {

	/**
    * @abstract 新浪出款接口
    * @param [aid,req_id]
    * @return [true,false]
    * */
    public function openacc( $params ){
    	$param_map = ['request_id', 'user_id', 'name', 'idcard', 'phone','cardno','card_type','bankcode', 'ip'];
    	if( !$this->validParamMap($param_map, $params) )
    		$ret = ['res_code'=>'-999','res_msg'=>'参数不匹配'];
    	$url = "sinapay/bindcard" ;
    	$openApi = new ApiClientCrypt;
    	$res = $openApi->sent($url, $params);
    	$result = $openApi->parseResponse($res);
        Logger::dayLog('sinaopenacc', 'opanacc', $result);
    	//新浪开户
    	if( $result['res_code'] === 0 ){
    		$ret = ['res_code'=>'0000','res_msg'=>$result['res_data']];
    	}else{
    		$ret = ['res_code'=>$result['res_code'],'res_msg'=>$result['res_data']];
    	}
    	return $ret;
    	
    }
	
}
