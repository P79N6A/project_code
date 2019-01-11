<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/paybank.php');

$order_id = abs(intval($_POST['order_id']));
$order_paytype = strval($_POST['paytype']);
$bank = strval($_POST['bank']);

if(!$order_id || !($order = Table::Fetch('jx_orders', $order_id))) {
	redirect( WEB_ROOT. '/index.php');
}

$product = Table::Fetch('jx_products', $order['pid']);


if ($order_paytype) {
	$uarray = array( 'paytype' => pay_getservice($order_paytype), 'zxbmobile' => NULL );
	Table::UpdateCache('jx_orders', $order_id, $uarray);
	$order['paytype'] = pay_getservice($order_paytype);
}

//如果是交享卡支付，则直接跳转至支付中的页面
if($order['paytype'] == 'jxk')
{
	redirect(WEB_ROOT  . "/order/orderpay.php?id=".$order['id']);
}

//payed order
if ( $order['state'] == 'pay' ) {  
	if ( is_get() ) {
		die(include template('order_pay_success'));		
	} else {
		redirect(WEB_ROOT  . "/order/index.php?action=buy");
	}
}


/* generate unique pay_id */
if (!($pay_id = $order['pay_id'])) {
	$pay_id = date('YmdHis').rand(1000, 9999);
	Table::UpdateCache('jx_orders', $order['id'], array(
				'pay_id' => $pay_id,
				));
}

//判断订单是否失效，如果下单时间大于1小时，则失效，无法继续支付
//$now = time();
//if($now - $order['createtime'] > 60*60)
//{
//	redirect( WEB_ROOT . "/account/productdetail.php?id={$order['pid']}");
//}

//判断商品的库存，若已卖完，则跳转至商品详情页
if(!empty($product['max_number']))
{
	if(!empty($product['sale_number']))
	{
		if($product['sale_number'] >= $product['max_number'])
		{
			redirect( WEB_ROOT . "/account/productdetail.php?id={$order['pid']}");
		}
	}
}

/* noneed pay where goods soldout or end */
if(!empty($product['end_time']))
{
	if ($product['end_time'] <= time()) {
		redirect( WEB_ROOT . "/account/productdetail.php?id={$order['pid']}");
	}
}
/* end */
if( $order['paytype'] == 'yeepay' ){
	$pay_callback = "pay_team_xianhuahuayeepay";
}else {
	$pay_callback = "pay_team_comm";
	$order['service'] = $order['paytype'] ;
}
//$pay_callback = "pay_team_comm";
if ( function_exists($pay_callback) )
{
	$payhtml = $pay_callback($order['origin'], $order, $bank);
	die(include template('chinabankpay'));
}
else {
	redirect( WEB_ROOT . "/account/productdetail.php?id={$order['pid']}");
}
