<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
need_login();
need_checksns();
$id = intval($_GET['id']);
$order = Table::Fetch('jx_orders', $id);
$order['createdate'] = date('Y-m-d H:i',$order['createtime']);

//用户的详细地址
$province = Table::Fetch('jx_areas', $order['province_id']);
$city = Table::Fetch('jx_areas', $order['city_id']);
$area = Table::Fetch('jx_areas', $order['area_id']);

$useraddress = $province['name'].$city['name'].$area['name'].$order['street'];

//商品信息
$product = Table::Fetch('jx_products', $order['pid']);

//卖家信息
$user = Table::Fetch('jx_users', $order['uid']);
if(!empty($user['nickname']))
{
	$user['buyer'] = $user['nickname'];
}
else 
{
	if($user['type'] == 1)
	{
		$user['buyer'] = $user['email'];
	}
	else 
	{
		$user['buyer'] = $user['mobile'];
	}
}

$pagetitle = '我的订单-订单详情页';
include template('order_sellerdetail');
?>