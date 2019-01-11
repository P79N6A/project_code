<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/paybank.php');

need_login();
need_checksns();

$id = intval($_GET['id']);
$order = Table::Fetch('jx_orders', $id);
if(empty($order))
{
	redirect( WEB_ROOT . "/");
}

$pagetitle = '订单支付中';
include template('order_orderpay');