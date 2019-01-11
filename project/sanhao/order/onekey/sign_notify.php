<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

require_once(DIR_LIBARAY."/bocom/java/Java.inc"); 

java_set_library_path("/usr/comm/lib"); //设置java开发包路径
java_set_file_encoding("GBK");      //设置java编码

//获得java对象
$client=new java("com.bocom.netpay.b2cAPI.BOCOMB2CClient");
$ret=$client->initialize(DIR_LIBARAY."/bocom/ini/B2CMerchant.xml");
$ret = java_values($ret);
file_put_contents( './2.txt' , $ret.":".$_REQUEST["notifyMsg"] ) ;

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
    	$data = array() ;
    	$arr=preg_split("/\|{1,}/", $srcMsg);
    	//商户客户号
    	$data['mer_id'] = $arr[0]; 
    	//协议检索号
    	$data['meragreeno'] = $arr[1];
    	//协议号
    	$data['ptcid'] = $arr[2];
    	//签约时间
    	$data['orderdate'] = $arr[3];
    	//卡类型
    	$data['cardtype'] = $arr[4] ;
    	//卡号码
    	$data['cardnomask'] = $arr[5] ;
    	//手机号
    	$data['mobilenomask'] = $arr[6] ;
    	//商户备注
    	$data['mercomment'] = $arr[7] ;
    	//银行备注
    	$data['bankcomment'] = $arr[8] ;
    	
		//ZOrder::CreateFormSign( $data );
		die('ok'); 
    }
}
die('error');
?>