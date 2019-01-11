<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

	$url = "http://www.xianhuahua.com/Bill/RepayCallback.do";
	$data = "amt=4052.00&req_id=REPAY00116179T&ret=success&type=2&userno=10010&alipay_id=2015060500001000640052325309&sign=d05d03e475c545bba39f99f1f4dbed1d";

	$result = interface_post($url, $data);	
	print_r($result);exit;

$orderid = 43195;
$order = Table::Fetch('jx_orders', $orderid);

$key = '9964DYByKL967c3308imytCB';
$ret = 'success';
$type = 2;
$trade_no = $order['yeepay_id'];
$sign = md5($trade_no.$order['amt'].$order['req_id'].$ret.$type.$order['userno'].$key);
$url = $order['callback'];
$data = "amt=".$order['amt']."&req_id=".$order['req_id']."&ret=".$ret."&type=".$type."&userno=".$order['userno']."&alipay_id=".$trade_no."&sign=".$sign;
echo $url."?".$data;exit;