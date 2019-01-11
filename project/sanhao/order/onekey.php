<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();

$order_id = $id = abs(intval($_GET['id']));

if(!$order_id || !($order = Table::Fetch('order', $order_id))) {
	redirect( WEB_ROOT. '/index.php');
}
if ( $order['user_id'] != $login_user['id']) {
	redirect( WEB_ROOT . "/team.php?id={$order['team_id']}");
}

$pay_callback = "contract_by_onekey";

if ( function_exists($pay_callback) ) {
	$payhtml = $pay_callback($total_money, $order);
	die(include template('chinabankpay'));
}
else {
	Session::Set('error', '无合适的支付方式或余额不足');
	redirect( WEB_ROOT. "/team.php?id={$order_id}");
}
