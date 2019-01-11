<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'changeorderstatus');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//商户手机号
$merchant_mobile = isset($arr['merchant_mobile']) ? addslashes(strval(trim($arr['merchant_mobile']))) : "";
//订单编号
$pay_id = isset($arr['order_pay_id']) ? addslashes(strval(trim($arr['order_pay_id']))) : "";

if(!empty($merchant_mobile) && !empty($pay_id))
{
	//先查询订单是否存在
	$condition = array('pay_id' => $pay_id, 'saler_mobile'=>$merchant_mobile);
	$order = DB::LimitQuery('mr_orders', array(
		'condition' => $condition,
		'one'=>true
	));
	if(!empty($order))
	{
		$order_id = $order['id'];
		$paytime = time();
		//修改订单状态
		$uarray = array( 'state' => 'pay','paytime' => $paytime);
		Table::UpdateCache('mr_orders', $order_id, $uarray);
		
		//修改商品已售数量
		$sql = "update `mr_products` set sale_number=sale_number+'".$order['quantity']."' where id=".$order['pid'];
		DB::Query($sql);

		$array['ret'] = 100;
		$array['msg'] = "成功";
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "订单不存在";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}