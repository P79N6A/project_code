<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
need_checksns();
//判断QQ是否绑定
$cqq = array( 'uid' => $login_user_id, 'sns_type' => 'qq' );
$userqq = DB::LimitQuery('jx_users_sns' , array(
		'condition' => $cqq,
		'one'=>true
	));
	
//判断新浪微博是否绑定
$cweibo = array( 'uid' => $login_user_id, 'sns_type' => 'weibo' );
$userweibo = DB::LimitQuery('jx_users_sns' , array(
		'condition' => $cweibo,
		'one'=>true
	));

$pagetitle = '绑定SNS账号';
include template('account_bindingsns');