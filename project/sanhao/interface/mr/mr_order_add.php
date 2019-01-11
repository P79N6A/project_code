<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

//判断传递过来的参数条件
if( empty( $array_notify['merchant_id'] ) || empty( $array_notify['product_id'] ) || empty( $array_notify['order_number'])){
	echo json_encode(array('ret'=>'1008','msg'=>'重要参数不能为空'));exit;
}

//先根据传递过来的手机号查询商户的信息
$merchant = DB::GetTableRow('mr_merchants' , array(
				'id'=>$array_notify['merchant_id']
			));
if( empty( $merchant ) ){
	echo json_encode(array('ret'=>'1011','msg'=>'登录商户信息不存在'));exit;
}

//根据商品编号判断商品的信息
$product = DB::GetTableRow('mr_products' , array(
				'number'=>$array_notify['product_id']
			));
if( empty( $product ) ){
	echo json_encode(array('ret'=>'1010','msg'=>'商品信息不存在'));exit;
}

$partners = DB::GetTableRow('mr_merchants' , array(
				'id'=>$product['mid']
			));

//下单
$uarray['pay_id'] = date('YmdHis').rand(1000, 9999);
$uarray['buyer_mobile'] = $merchant['mobile'];
$uarray['saler_mobile'] = $partners['mobile'];
$uarray['pid'] = $product['id'];
$uarray['quantity'] = $array_notify['order_number'];
$uarray['price'] = $product['price'];
$uarray['origin'] = $product['price']*$array_notify['order_number'];
$uarray['createtime'] = time();
$uarray['id'] = DB::Insert('mr_orders', $uarray);
if($uarray['id'])
{
	$array['ret'] = 100;
	$array['msg'] = "下单成功";
	$array['merchant_id'] = $partners['id'];
	$array['product_id'] = $product['number'];
	$array['order_id'] = $uarray['pay_id'];
	$array['product_name'] = $product['name'];
	$array['product_price'] = $product['price'];
	$array['order_number'] = $array_notify['order_number'];
	$array['order_origin'] = $uarray['origin'];
	$array['mobile'] = $merchant['mobile'];
	$array['order_createtime'] = date('Y-m-d H:i:s', $uarray['createtime']);
	$key = $INI['system']['key'];
	$array['sign'] = md5($array['merchant_id'].$array['mobile'].$array['order_createtime'].$array['order_id'].$array['order_number'].$array['order_origin'].$array['product_id'].$array['product_name'].$array['product_price'].$array['ret'].$key);
	echo json_encode($array);exit;
}
else 
{
	echo json_encode(array('ret'=>'1012','msg'=>'下单失败'));exit;
}