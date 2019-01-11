<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

file_put_contents( '3.txt' , 'wolaile' ) ;
require_once(DIR_LIBARAY."/bocom/java/Java.inc"); 

java_set_library_path("/usr/comm/lib"); //设置java开发包路径
java_set_file_encoding("GBK");      //设置java编码

//获得java对象
$client=new java("com.bocom.netpay.b2cAPI.BOCOMB2CClient");
$ret=$client->initialize(DIR_LIBARAY."/bocom/ini/B2CMerchant.xml");
$ret = java_values($ret);

file_put_contents( '4.txt' , $ret.":".$_REQUEST["notifyMsg"] ) ;
exit;

if ($ret == "0")
{
	$notifyMsg=$_REQUEST["notifyMsg"];
	
    $lastIndex=strripos($notifyMsg, "|");
    $signMsg=substr($notifyMsg, $lastIndex + 1);   //签名信息
    $srcMsg=substr($notifyMsg, 0, $lastIndex + 1); //原文

    $signMsg=new java("java.lang.String", $signMsg);
    $srcMsg=new java("java.lang.String", $srcMsg);
    $veriyCode=-1;
    
    $nss=new java("com.bocom.netpay.b2cAPI.NetSignServer");
    $nss->NSDetachedVerify($signMsg->getBytes("GBK"), $srcMsg->getBytes("GBK")); //验签

    $veriyCode=java_values($nss->getLastErrnum());
	//判断支付结果是否可信
    if ($veriyCode >= 0) 
    { 
    	$arr=preg_split("/\|{1,}/", $srcMsg);
    	//订单编号pay_id
    	$payId = $arr[1
    	]; 
    	//交易金额
    	$money = $arr[2];
    	//支付时间
    	$pay_time = $arr[6].$arr[7];
    	//支付结果
    	$pay_ret = $arr[9];
    	/* 判断返回信息，如果支付成功，1表示支付成功 */
		if ( $pay_ret == '1' ) {
			//充值时id为：c-1-time()
			if( strpos( $payId , "-" ) !== false ){
				@list($_, $user_id, $create_time) = explode('-', $payId, 3);
				ZFlow::CreateFromCharge($money, $user_id, $create_time, 'comm');
				die('ok');
			}
			//订单付款是为订单编号：time()+订单ID+购买数量
			$order = Table::Fetch('order' , $payId , 'pay_id' ) ;
			$order_id = $order['id'] ;
			
			$currency = 'CNY';
			$service = $order['service'];
			global $option_service;
			$bank = $option_service[$service];
			ZOrder::OnlineIt($order_id, $payId, $money, $currency, $service, $bank);
			die('ok'); 
		}	
    }
}
die('error');
?>