<?php

namespace app\commonapi\apiInterface;

use app\commonapi\Apihttp;
use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use ReflectionClass;
use Yii;

class Jiufuremit extends Apihttp {

    
    
	/**
    * @abstract 玖富出款接口
    * @param [aid,req_id]
    * @return [true,false]
    * */
    public function outBlance( $params ){
    	$param_map = [
    			'req_id',
    			'name', 
    			'idcard', 
    			'phone', 
    			'email', 
    			'cardno',
    			'guest_account_bank',
    			'guest_account_bank_branch', 
    			'img_url',
    			'guest_account_province', 
    			'guest_account_city',
    			'province_id',
    			'city_id',
    			'county_id',
    			'settle_amount',
    			'customer_sex',
    			'time_limit',
    			'loan_purpose',
    			'callbackurl',
    			'liveaddressProvince',
    			'liveaddressCity',
    			'liveaddressDistinct',
    			'liveaddressRoad',
    			'contactName',
    			'contactPhone',
    			'contractCode',
    			'phonePassword',
    			'company',
    			'companyPhone',
    			'companyAdressprovince',
    			'companyAdressCity',
    			'companyAdressDist',
    			'companyAdressRoad',
    			'companyType',
    			'beginCompanyDate',
    			'product_id'
    	];
    	if( !$this->validParamMap($param_map, $params) )
    		$ret = ['res_code'=>'-999','res_msg'=>'参数不匹配'];
    	$url = "jiufu/remit" ;
    	$openApi = new ApiClientCrypt;
    	$res = $openApi->sent($url, $params);
    	$result = $openApi->parseResponse($res);
    	Logger::errorLog($params['req_id']."--".print_r($result, true), 'jiufuremit');
        
    	if( $result['res_code'] === 0 ){
    		$ret = ['res_code'=>'0000','res_msg'=>$result['res_data']];
    	}else{
    		$ret = ['res_code'=>$result['res_code'],'res_msg'=>$result['res_data']];
    	}
    	return $ret;
    }
	
}
