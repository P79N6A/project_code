<?php
require_once(dirname( dirname(__FILE__) ). '/app.php');

//1.判断参数是否都存在
if( empty( $array_notify['amt']) || empty($array_notify['req_id']) || empty($array_notify['userno']) || empty($array_notify['callback']) ) {
	$array['ret'] = 1006;
	$array['msg'] = "重要参数不能为空";
	echo json_encode($array);exit;
}

//根据还款金额生成一个商品
$u['uid'] = 0;
$u['pname'] = '先花花还款';
$u['description'] = '先花花还款';
$u['price'] = $array_notify['amt'];
$u['type'] = 2;
$u['status'] = 4;
$u['createtime'] = time();
$u['id'] = DB::Insert('jx_products', $u);
if($u['id'])
{
	//生成订单
	$u_array['pay_id'] = date('YmdHis').rand(1000, 9999);
	$u_array['uid'] = 0;
	$u_array['sid'] = 0;
	$u_array['pid'] = $u['id'];
	$u_array['quantity'] = 1;
	$u_array['price'] = $u['price'];
	$u_array['origin'] = $u['price'];
	$u_array['express'] = 'n';
	$u_array['realname'] = '先花花';
	$u_array['province_id'] = 1;
	$u_array['city_id'] = 1;
	$u_array['area_id'] = 1;
	$u_array['street'] = '北京';
	$u_array['postcode'] = '100000';
	$u_array['createtime'] = time();
	$u_array['amt'] = $u['price'];
	$u_array['req_id'] = $array_notify['req_id'];
	$u_array['userno'] = $array_notify['userno'];
	$u_array['callback'] = urldecode($array_notify['callback']);
	$u_array['id'] = DB::Insert('jx_orders', $u_array);
	if($u_array['id'])
	{
		redirect( WEB_ROOT. '/order/checkyeepay.php?order_id='.$u_array['id']);
	}
	else 
	{
		$array['ret'] = 1005;
		$array['msg'] = "系统错误";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 1005;
	$array['msg'] = "系统错误";
	echo json_encode($array);exit;
}