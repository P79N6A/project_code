<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
need_login();
need_checksns();

$id = intval($_GET['id']);
$order = Table::Fetch('jx_orders', $id);
$order['createdate'] = date('Y-m-d H:i:s',$order['createtime']);

//剩余时间
$now = time();
$diff_time = $left_time = ($order['createtime']+60*60) - $now;
$left_time = $left_time % 3600;
$left_minute = floor($left_time/60);
$left_time = $left_time % 60;

//用户的详细地址
$province = Table::Fetch('jx_areas', $order['province_id']);
$city = Table::Fetch('jx_areas', $order['city_id']);
$area = Table::Fetch('jx_areas', $order['area_id']);

$useraddress = $province['name'].$city['name'].$area['name'].$order['street'];

//商品信息
$product = Table::Fetch('jx_products', $order['pid']);

//卖家信息
$user = Table::Fetch('jx_users', $order['sid']);
if(!empty($user['nickname']))
{
	$user['saler'] = $user['nickname'];
}
else 
{
	if($user['type'] == 1)
	{
		$user['saler'] = $user['email'];
	}
	else 
	{
		$user['saler'] = $user['mobile'];
	}
}

$pagetitle = '我的订单-订单详情页';
include template('order_buyersdetail');
?>