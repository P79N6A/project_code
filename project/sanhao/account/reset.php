<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
need_checksns();

//测试转账接口
//$config = require_once(DIR_LIBARAY."/icardpay/KafkaConfig.php");
//$merchantId = $config[1]['merchantId'];
//$merchantKey = $config[1]['merchantKey'];
//$payNo = '0000000001541501';
//$nowtime = date('YmdHi');
//$pay_id = '201307041652335553';
//收款人(卖家)的手机号,根据订单号获取卖家的id,然后在获取手机号
//$order = DB::LimitQuery('jx_orders', array(
//	'condition' => array('pay_id' => $pay_id),
//	'one'=>true
//));
//$user = DB::LimitQuery('jx_users', array(
//	'condition' => array('id' => $order['sid']),
//	'one'=>true
//));
//$mobile = $user['mobile'];
//转账金额
//$amount = 1*100;
//$mac = md5($merchantId.$payNo.$mobile.$pay_id.$nowtime.$merchantKey);
//
//调用SSO登录接口，获取token值
//
//调用支付通转账接口
//$url = 'http://192.168.0.127/hk-frt-sys-web/F20111.front';
//$data = "merNo=".$merchantId."&payNo=".$payNo."&userMoblieNo=".$mobile."&amount=".$amount."&prdOrdNo=".$pay_id."&TrDt=".$nowtime."&MAC=".$mac;
//$ret = json_decode(interface_post($url, $data)); 

if (is_post()) {
	if ($_POST['password'] == $_POST['password2']) {
		ZUser::Modify($user['id'], array(
			'password' => $_POST['password'],
			'recode' => '',
		));
		redirect( WEB_ROOT . '/account/reset.php?code=ok');
	}
	Session::Set('error', '两次输入的密码不匹配，请重新设置');
}

$pagetitle = '更改密码';
include template('account_reset');
