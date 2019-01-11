<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$config = require_once(DIR_LIBARAY."/icardpay/KafkaConfig.php");
$merchantKey = $config[1]['merchantKey'];
$bindappip = $config[1]['bindappip'];
$otherappip = $config[1]['otherappip'];
$loginip = getLoginIP();
$appname = $config[1]['loginapp'];
$appname2 = $config[1]['freeloginapp'];
$expiretime = 10*60;
//验签
$sign = md5($appname.$appname2.$_SESSION['mobile'].$loginip.$expiretime.$merchantKey);
//调用SSO接口，获取token值
$urlpay  = $otherappip."/sso/token/create";
$datapay = "userIp=".$loginip."&userLogin1=".$_SESSION['mobile']."&appName1=".$appname."&appName2=".$appname2."&expireSecond=".$expiretime."&sign=".$sign;
$result = json_decode(interface_post($urlpay, $datapay)); 
//if($result->RspCode == '00')
//{
$token = $result->RspValue;
//}
//else 
//{
//	//没有获取到token值，则认为支付通那边的绑定关系已删除，删除三好网这边的绑定关系，然后跳转至绑定页面
//	$binding = DB::LimitQuery('jx_bindings', array(
//		'condition' => array('mobile' => $_SESSION['mobile']),
//		'one'=>true
//	));
//	Table::Delete('jx_bindings', $binding['id']);
//	$url = "http://www.jxtuan.com/account/receivableaccount.php";
//	header("Location:$url");
//	exit;
//}
//跳转到的支付通页面
$icardpayurl = $bindappip."/user/561262.tran?SYSCOD=0001&FLAG=00&token=".$token;
header("Location:$icardpayurl");