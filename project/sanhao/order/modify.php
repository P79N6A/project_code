<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$id = abs(intval($_GET['id']));

$order = Table::Fetch('jx_orders', $id);

$condition = array( 'id' => $order['pid']);
$product = DB::LimitQuery('jx_products', array(
	'condition' => $condition,
	'one' => true,
));
$now = time();
$diff_time = $left_time = $product['end_time'] - $now;
$left_day = floor($diff_time/86400);
$left_time = $left_time % 86400;
$left_hour = floor($left_time/3600);
$left_time = $left_time % 3600;
$left_minute = floor($left_time/60);
$left_time = $left_time % 60;
//获取商品详情页显示的第一张图片
$conimage = array('pid'=>$order['pid'],'type'=>1);
$productimage = DB::LimitQuery('jx_products_image', array(
	'condition' => $conimage,
	'one' => true,
));
//获取商品的属性
$con = array('pid'=>$order['pid']);
$property = DB::LimitQuery('jx_products_property', array(
	'condition' => $con
));	
if(!empty($property))
{
	foreach ($property as $k=>$v)
	{
		$arrcontent = explode(' ',$v['content']);
		$property[$k]['size'] = $arrcontent;
	}
}

//获取用户的地址信息
$conaddress = array( 'uid' => $login_user_id );
$address = DB::LimitQuery('jx_address', array(
	'condition' => $conaddress,
	'one' => true,
));
if(!empty($address))
{
	$conprovince = array( 'pID' => 0);
	$province = DB::LimitQuery('jx_areas', array(
			'condition' => $conprovince,
		));
	//查询省份下所属的城市
	$concity = array( 'pID' => $address['province_id'] );
	$city = DB::LimitQuery('jx_areas', array(
			'condition' => $concity,
	));
	//查询城市下所属的地区
	$conarea = array( 'pID' => $address['city_id'] );
	$area = DB::LimitQuery('jx_areas', array(
			'condition' => $conarea,
	));
}
else
{
	$conprovince = array( 'pID' => 0);
	$province = DB::LimitQuery('jx_areas', array(
			'condition' => $conprovince,
		));
}

$pagetitle = '购买商品';
include template('order_modify');