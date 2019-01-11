<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/paybank.php');

need_login();
need_checksns();

$id = intval($_GET['id']);
$order = Table::Fetch('jx_orders', $id);
//商品信息
$product = Table::Fetch('jx_products', $order['pid']);


//用户的详细地址
$province = Table::Fetch('jx_areas', $order['province_id']);
$city = Table::Fetch('jx_areas', $order['city_id']);
$area = Table::Fetch('jx_areas', $order['area_id']);

$useraddress = $province['name'].$city['name'].$area['name'].$order['street'];

$pagetitle = '支付遇到问题';
include template('order_question');
