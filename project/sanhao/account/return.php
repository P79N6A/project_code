<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

//绑定支付通后的回调地址
$config = require_once(DIR_LIBARAY."/icardpay/KafkaConfig.php");
$notify = json_decode($_POST['notify']);

//获取返回的手机号和支付通账号
$mobile = json_decode($notify->RspValue)->UserLogin1;
$payno = json_decode($notify->RspValue)->UserLogin2;

//获取绑定关系表
$binding = DB::LimitQuery('jx_bindings', array(
	'condition' => array('mobile' => $mobile),
	'one'=>true
));
if(empty($binding))
{
	$u['mobile'] = $mobile;
	$u['payno'] = $payno;
	$u['createtime'] = time();
	$u['id'] = DB::Insert('jx_bindings', $u);
	if(!empty($u['id']))
	{
		//绑定成功，跳转至绑定成功页面
		echo 'success';exit;
	}
	else 
	{
		//绑定失败，跳转至绑定失败页面
		echo 'error';exit;
	}
}