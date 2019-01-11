<?php 
/**
 * 畅捷单笔订单快捷支付API接口
 * @author gaolian
 */
namespace app\modules\api\common\chanpay;
use app\common\Logger;

include (__DIR__ . "/api/chanpay.class.php");

class ChanpayQuick{	
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
	 * 单笔订单快捷支付API接口
	 * 
	 */
	public function quickpayment($order_id, $amount, $expired_time, $buyer_mobile, $card_type, $bank_code, $payer_name, $payer_card_no, $id_number, $phone_number, $expiry_date='', $cvv2='')
	{	 
    	$postData   =   array();
    	$postData['service']   =   'cjt_quick_payment';
    	$postData['version']   =   chanpay_version;
    	$postData['partner_id']=   chanpay_partner_id;  //合作者id  该id为测试环境id
    	$postData['_input_charset']=   chanpay_input_charset;
    	$postData['sign_type']  =   chanpay_sign_type; //签名类型
    	$postData['out_trade_no']   =   $order_id; //商户唯一订单id
    	$postData['trade_amount'] = $amount;
    	$postData['expired_time'] = $expired_time;
    	$postData['buyer_id_type'] = 'MOBILE';
    	$postData['buyer_mobile'] = $buyer_mobile;
    	$postData['card_type'] = $card_type;
    	$postData['pay_type'] = 'C';
    	$postData['bank_code'] = $bank_code;
    	$chanpay = new \Chanpay();
    	$postData['payer_name'] = $chanpay->Rsa_encrypt($payer_name);
    	$postData['payer_card_no'] = $chanpay->Rsa_encrypt($payer_card_no);
    	$postData['id_number'] = $chanpay->Rsa_encrypt($id_number);
    	$postData['phone_number'] = $chanpay->Rsa_encrypt($phone_number);
    	if($card_type == 'CC'){
    		$postData['expiry_date'] = $chanpay->Rsa_encrypt($expiry_date);
    		$postData['cvv2'] = $chanpay->Rsa_encrypt($cvv2);
    	}
    	$postData['sign']=   $chanpay->rsaSign($postData);
    	$url     = chanpay_net_url;
    	$result = $chanpay->curlGet($url, $postData);
    	Logger::dayLog('chanpay_quickpayment', $result);
    	return $result;
	}
	
	/**
	 * 快捷支付交易确认接口
	 */
	public function quickconfirm($order_id, $code){
		$postData   =   array();
		$postData['service']   =   'cjt_quick_payment_confirm';
		$postData['version']   =   chanpay_version;
		$postData['partner_id']=   chanpay_partner_id;  //合作者id  该id为测试环境id
		$postData['_input_charset']=   chanpay_input_charset;
		$postData['sign_type']  =   chanpay_sign_type; //签名类型
		$postData['out_trade_no']   =   $order_id; //商户唯一订单id
		$postData['verification_code']   =   $code;
		$chanpay = new \Chanpay();
		$postData['sign']=   $chanpay->rsaSign($postData);
		
		$url     = chanpay_net_url;
		$result = $chanpay->curlGet($url, $postData);
		Logger::dayLog('chanpay_quickconfirm', $result);
		return $result;
	}
	
	/**
	 * 交易查询网管接口
	 */
	public function tradequery($order_id, $trade_type){
		$postData   =   array();
		$postData['service']   =   'cjt_query_trade';
		$postData['version']   =   chanpay_version;
		$postData['partner_id']=   chanpay_partner_id;  //合作者id  该id为测试环境id
		$postData['_input_charset']=   chanpay_input_charset;
		$postData['sign_type']  =   chanpay_sign_type; //签名类型
		$postData['outer_trade_no']   =   $order_id; //商户唯一订单id
		$postData['trade_type']   =   $trade_type;
		$chanpay = new \Chanpay();
		$postData['sign']=   $chanpay->rsaSign($postData);
		
		$url     = chanpay_net_url;
		$result = $chanpay->curlGet($url, $postData);
		Logger::dayLog('chanpay_tradequery', $result);
		return $result;
	}
}