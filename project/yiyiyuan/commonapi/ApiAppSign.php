<?php
namespace app\commonapi;
/**
 * aes简单的签名认证
 */
class ApiAppSign {
    private $key = "spLu1bSt3jXPY8ximZUf9k7F";
    
    private $platform_host = "http://10.253.101.53:8081/api/applabel";  //线上
    private $platform_host_test = "http://182.92.80.211:8888/api/applabel";

    public function __construct(){
        $is_prod = SYSTEM_ENV == 'prod' ? true : false;
        $this->xgboost_url = $is_prod ? $this->platform_host : $this->platform_host_test;
    }
    /**
     * 加入签名
     * @param [] $data
     */
    public  function signData($data) {
        $sign = $this->getSign($data);
        $data['sign'] =  $sign;
        return $data;
    }
    public function postForm($postData){
        $postUrl = $this->xgboost_url;
        $curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $postUrl);
	curl_setopt($curl, CURLOPT_USERAGENT,'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15');
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_HEADER, FALSE);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	    'Content-Type: application/json',
	    'Content-Length: ' . strlen($postData))
	);
	$result = curl_exec($curl); 
	curl_close($curl);
        return $result;
    }
    
     /**
     * [setXgboostSign 数据加密]
     */
    private function getSign($data)
    {
        $str = '';
        ksort($data);
        foreach ($data as $k => $v) {
            $str .= $k.'='.$v.'&';
        }
        $str = rtrim($str,'&');
        $sign = md5(substr(md5($str),0,30).$this->key);
        return $sign;
    }




}
