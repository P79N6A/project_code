<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'addorder');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//商品ID
$product_id = isset($arr['product_id']) ? intval(trim($arr['product_id'])) : "";
//购买数量
$product_quantity = isset($arr['product_quantity']) ? intval(trim($arr['product_quantity'])) : "";
//订单金额
$order_origin = isset($arr['order_origin']) ? number_format(addslashes(strval(trim($arr['order_origin']))),2) : "";
//付款手机号
$order_mobile = isset($arr['order_mobile']) ? addslashes(strval(trim($arr['order_mobile']))) : '';
//商户ID
$merchant_id = isset($arr['merchant_id']) ? addslashes(strval(trim($arr['merchant_id']))) : "";
//商户手机号
$merchant_mobile = isset($arr['merchant_mobile']) ? addslashes(strval(trim($arr['merchant_mobile']))) : '';
//下单类型
$type = isset($arr['type']) ? intval(trim($arr['type'])) : "";

if(!empty($order_origin) && !empty($merchant_id) && !empty($merchant_mobile) && !empty($type))
{
	//type为1，为商品下单，直接生成订单
	if($type == 1)
	{
		//生成一个订单
		$uarray['pay_id'] = date('YmdHis').rand(1000, 9999);
		$uarray['buyer_mobile'] = $order_mobile;
		$uarray['saler_mobile'] = $merchant_mobile;
		$uarray['pid'] = $product_id;
		$uarray['quantity'] = $product_quantity;
		$uarray['price'] = $order_origin/$product_quantity;
		$uarray['origin'] = $order_origin;
		$uarray['createtime'] = time();
		$uarray['id'] = DB::Insert('mr_orders', $uarray);
		if($uarray['id'])
		{
			//获取商家的信息
			$merchant = Table::Fetch( 'mr_merchants' , $merchant_id ) ;
			//获取商品信息
			$product = Table::Fetch( 'mr_products' , $product_id ) ;
			$array['ret'] = 100;
			$array['msg'] = "生成订单成功";
			$array['merchant_name'] = $merchant['merchant_name'];
			$array['order_pay_id'] = $uarray['pay_id'];
			$array['order_origin'] = $order_origin;
			$array['product_name'] = $product['name'];
			$array['merchant_mobile'] = $merchant_mobile;
			echo json_encode($array);exit;
		}
		else 
		{
			$array['ret'] = 102;
			$array['msg'] = "生成订单失败";
			echo json_encode($array);exit;
		}
	}
	else 
	{
		//先根据金额查询商品的ID，如果商品不存在则重新生成一个商品
		$condition = array( 'price' => $order_origin);
		$product = DB::LimitQuery('mr_products', array(
			'condition' => $condition,
			'one' => true,
		));
		//商品已经存在
		if(!empty($product))
		{
			$pid = $product['id'];
			$product_name = $product['name'];
		}
		else 
		{
			//先生成一个虚拟的商品
			$u['mid'] = $merchant_id;
			$u['name'] = "快速消费品".rand(100000,999999);
			$u['price'] = $order_origin;
			$u['createtime'] = time();
			$u['id'] = DB::Insert('mr_products', $u);
			$pid = $u['id'];
			$product_name = $u['name'];
		}
		//生成一个订单
		$uarray['pay_id'] = date('YmdHis').rand(1000, 9999);
		$uarray['buyer_mobile'] = $order_mobile;
		$uarray['saler_mobile'] = $merchant_mobile;
		$uarray['pid'] = $pid;
		$uarray['quantity'] = 1;
		$uarray['price'] = $order_origin;
		$uarray['origin'] = $order_origin;
		$uarray['createtime'] = time();
		$uarray['id'] = DB::Insert('mr_orders', $uarray);
		if($uarray['id'])
		{
			//获取商家的信息
			$merchant = Table::Fetch( 'mr_merchants' , $merchant_id ) ;
			$array['ret'] = 100;
			$array['msg'] = "生成订单成功";
			$array['merchant_name'] = $merchant['merchant_name'];
			$array['order_pay_id'] = $uarray['pay_id'];
			$array['order_origin'] = $order_origin;
			$array['product_name'] = $product_name;
			$array['merchant_mobile'] = $merchant_mobile;
			echo json_encode($array);exit;
		}
		else 
		{
			$array['ret'] = 102;
			$array['msg'] = "生成订单失败";
			echo json_encode($array);exit;
		}
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}