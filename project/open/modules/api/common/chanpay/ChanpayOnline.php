<?php 
/**
 * 畅捷网银支付
 * @author gaolian
 */
namespace app\modules\api\common\chanpay;
use app\common\Logger;
use Yii;

include (__DIR__ . "/api/chanpay.class.php");

class ChanpayOnline{	
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
	 * 畅捷网银接口调用
	 * 
	 */
	public function online($order_id, $product_name, $amount)
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
	    $chanpay = new \Chanpay();
	    $postData['sign']=   $chanpay->rsaSign($postData);
	    $query  =   http_build_query($postData);
	    $url     = chanpay_net_url.'?'.$query;  //该url为测试环境url
	    Logger::dayLog('chanpay_online', $url);
	   	return $url;
	}
	
	/**
	 * 验证签名
	 */
	public function checksign($args, $sign)
	{
		$chanpay = new \Chanpay();
		$result = $chanpay->rsaVerify($args, $sign);
		return $result;
	}
	
	/**
	 * 查询银行列表接口
	 */
	public function getpaychannel(){
		$postData   =   array();
		$postData['service']   =   'cjt_get_paychannel';
		$postData['version']   =   chanpay_version;
		$postData['partner_id']=   chanpay_partner_id;  //合作者id  该id为测试环境id
		$postData['_input_charset']=   chanpay_input_charset;
		$postData['sign_type']  =   chanpay_sign_type; //签名类型
		$postData['product_code']   =   20201; //商户唯一订单id
		$chanpay = new \Chanpay();
		$postData['sign']=   $chanpay->rsaSign($postData);
	
		$url     = chanpay_net_url;
		$result = $chanpay->curlGet($url, $postData);
		Logger::dayLog('chanpay_getpaychannel', $result);
		return $result;
	}
}