<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/paybank.php');

need_login();
need_checksns();

if (is_post()) 
{
	$order_id = abs(intval($_POST['order_id']));
	$order_paytype = strval($_POST['paytype']);
	$mobile = strval($_POST['jxk_pay_mobile']);
}

if(!$order_id || !($order = Table::Fetch('jx_orders', $order_id))) {
	redirect( WEB_ROOT. '/index.php');
}

if ( $order['uid'] != $login_user_id) {
	redirect( WEB_ROOT . "/account/productdetail.php?id={$order['pid']}");
}

$product = Table::Fetch('jx_products', $order['pid']);


if (is_post() && $_POST['paytype'] ) {
	if($_POST['paytype'] == 'jxk')
	{
		$uarray = array( 'paytype' => pay_getservice($_POST['paytype']), 'zxbmobile' => $mobile, 'charge' => $order['origin']*0.1 );
	}
	else 
	{
		$uarray = array( 'paytype' => pay_getservice($_POST['paytype']), 'zxbmobile' => NULL );
	}
	Table::UpdateCache('jx_orders', $order_id, $uarray);
	$order['paytype'] = pay_getservice($_POST['paytype']);
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
$now = time();
if($now - $order['createtime'] > 60*60)
{
	redirect( WEB_ROOT . "/account/productdetail.php?id={$order['pid']}");
}

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
	$pay_callback = "pay_team_yeepay";
}
else if($order['paytype'] == 'alipay'){
	$pay_callback = "pay_team_alipay";
}
else {
	$pay_callback = "pay_team_comm";
	$order['service'] = $order['paytype'] ;
}
//$pay_callback = "pay_team_comm";
if ( function_exists($pay_callback) )
{
	if ($order['price'] > 3000){
		$str = <<<str
		订单金额超限，请选择其他方式支付<br /><br />
		<button onclick="javascript:window.opener=null;window.close();">点击关闭</button>
str;
		echo $str;
		return false;
	}
	$url = "http://".$_SERVER['SERVER_NAME']. '/order/payali.php?pay_id='.$order['pay_id'] .'&price='.$order['price'];
	$str = <<<str
		<title>支付宝扫码支付</title>
		<div>
			<ul style="list-style-type: none;">
				<li style="margin-left:10px">请打开支付宝扫码支付</li>
				<li><img src='$url'></li>
			</ul>
		</div>
str;
	echo $str;
	//支付宝支付，改为扫码支付
	//$payhtml = $pay_callback($order['origin'], $order);
	//die(include template('chinabankpay'));
}
else {
	redirect( WEB_ROOT . "/account/productdetail.php?id={$order['pid']}");
}




