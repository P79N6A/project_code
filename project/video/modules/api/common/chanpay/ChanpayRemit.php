<?php 
/**
 * 畅捷网银支付
 * @author gaolian
 */
namespace app\modules\api\common\chanpay;
use app\common\Curl;
use app\common\Logger;
use Yii;

class ChanpayRemit{	
	private $config;
	
	public function __construct($env){
		/**
		 * 账号配置文件
		 */
		$configPath = __DIR__ . "/config/config.{$env}.php";
		if( !file_exists($configPath) ){
			throw new \Exception($configPath."配置文件不存在",6000);
		}
		$this->config = include( $configPath );
	}
	
	/**
	 * 畅捷出款接口
	 * 
	 */
	public function remit($order_id, $product_name, $amount)
	{	 
	    $postData   =   array();
	    $postData['service']   =   'cjt_create_instant_trade';
	    $postData['version']   =   chanpay_version;
	    $postData['partner_id']=   chanpay_partner_id;  //合作者id  该id为测试环境id
	    $postData['_input_charset']=   chanpay_input_charset;
	    $postData['sign_type']  =   chanpay_sign_type; //签名类型
	    $postData['return_url'] =   chanpay_return_url;  //前端回调地址
	    $postData['out_trade_no']   =   $order_id; //商户唯一订单id
	    $postData['trade_amount']   =   $amount;
	    $postData['product_name']   =   $product_name;
	    $postData['notify_url']     =   chanpay_notify_url;//通知回调地址
	    $postData['buyer_id']       =   ''; //用户id
	    $postData['buyer_id_type']  =   'MEMBER_ID';
	    $postData['pay_method']     =   '2';
	    $postData['is_anonymous']   =   'Y';
	    $postData['sign']=   $this->rsaSign($postData);
	    $query  =   http_build_query($postData);
	    $url     = chanpay_net_url.'?'.$query;  //该url为测试环境url
	   	return $url;
	}
	
	/**
	 * 签名
	 * $args 签名字符串数组
	 *
	 * return 签名结果
	 */
	function rsaSign($args) {
	    $args=array_filter($args);//过滤掉空值
	    ksort($args);
	    $query  =   '';
	    foreach($args as $k=>$v){
	        if($k=='sign_type'){
	            continue;
	        }
	        if($query){
	            $query  .=  '&'.$k.'='.$v;
	        }else{
	            $query  =  $k.'='.$v;
	        }
	    }
	    //这地方不能用 http_build_query  否则会urlencode
	    //$query=http_build_query($args);
	    $path   =   chanpay_rsa_private_key;  //私钥地址 
	    $private_key= file_get_contents($path);
	    $pkeyid = openssl_get_privatekey($private_key);
	    openssl_sign($query, $sign, $pkeyid);
	    openssl_free_key($pkeyid);
	    $sign = base64_encode($sign);
	    return $sign;
	}
	
	/**
	 * 验证签名
	 * 
	 * @param $args 需要签名的数组
	 * @param $sign 签名结果
	 * return 验证是否成功
	 */
	function rsaVerify($args, $sign) {
		$args=array_filter($args);//过滤掉空值
		ksort($args);
		$query  =   '';
		foreach($args as $k=>$v){
			if($k=='sign_type' || $k=='sign'){
				continue;
			}
			if($query){
				$query  .=  '&'.$k.'='.$v;
			}else{
				$query  =  $k.'='.$v;
			}
		}
		//这地方不能用 http_build_query  否则会urlencode
		$sign = base64_decode($sign);
		$path   =   chanpay_rsa_public_key;  //公钥地址
		$public_key= file_get_contents($path);
		$pkeyid = openssl_get_publickey($public_key);
		if ($pkeyid) {
			$verify = openssl_verify($query, $sign, $pkeyid);
			openssl_free_key($pkeyid);
		}
		if($verify == 1){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 获取数据
	 * @param array $data
	 * @param str2json
	 * @return null
	 */
	public function curlGet($url, $params = array()){
		$curl = new Curl();
		$curl -> setOption(CURLOPT_CONNECTTIMEOUT,10);
		$curl -> setOption(CURLOPT_TIMEOUT,10);
		$content = $curl -> get($url, $params);
		$status  = $curl -> getStatus();
		if( $status == 200 ){
			return $content;
		}else{
			Logger::dayLog(
			"risk",
			"请求信息",$url,$params,
			"http状态",$status,
			"响应内容",$content
			);
			return null;
		}
	
	}
	
}