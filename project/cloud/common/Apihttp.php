<?php

namespace app\common;

use app\common\ApiClientCrypt;
use app\common\Logger;
use ReflectionClass;
use Yii;

class Apihttp {

    
    /**
     * 接口请求方式
     * @param unknown $url
     * @param unknown $data
     * @return mixed
     */
    public function httpPost($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);

        curl_close($ch);
        return $ret;
    }

    public function httpGet($url) {//get https的内容
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //不输出内容
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    /**
     * @abstract 获取同盾数据
     * @param [name,mobile,idno,service_type,event_type,birth_year,school,edu,school_year,industry,position,company,seq_id,token_id,version]
     * @return [true,false]
     * */
    public function riskLoanValid( $params ){
    	$param_map = ['account_name',
    				   'mobile',
    			       'id_number',
    				   'seq_id',
    				   'ip_address',
    			       'type',
    				   'token_id',
    				   'ext_school',
    				   'ext_diploma',
    				   'ext_start_year',
    			       'card_number',
    			       'pay_amount',
    				   'event_occur_time',
    				   'ext_birth_year',
    				   'organization',
    				   'ext_position',
                       'xhh_apps',
                       'black_box',
    	];
		
    	$url = "fraudmetrix" ;
    	$openApi = new ApiClientCrypt;
    	$res = $openApi->sent($url, $params);
    	$result = json_decode($res);
        if (isset($result->res_code) && $result->res_code != '0000' ) {
            Logger::errorLog($params['id_number']."--".print_r($result, true), 'riskLoanValid');
        }
    	return $result;
    }

     /**
     * @abstract 获取百度金融数据
     * @return 
     * */
    public function BaiduRiskApi( $data ){
        $param_map = [
            'name',
            'idcard',
            'phone',
        ];
        if (SYSTEM_PROD) {
            $url  = 'http://open.xianhuahua.com/bdrisk';
        } else {
            $url = 'http://182.92.80.211:8091/bdrisk';
        }
        $curl = new Curl();
        $res = $curl->post($url,$data);
        $res = json_decode($res,true);
        return $res;
    }
    
}
