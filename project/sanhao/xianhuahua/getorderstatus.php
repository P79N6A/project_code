<?php
require_once(dirname( dirname(__FILE__) ). '/app.php');

//1.判断参数是否都存在
if( empty($array_notify['req_id']) || empty($array_notify['userno']) ) {
	$array['ret'] = 1006;
	$array['msg'] = "重要参数不能为空";
	echo json_encode($array);exit;
}

$condition = array('req_id'=>$array_notify['req_id'],'userno'=>$array_notify['userno']);
$order = DB::LimitQuery('jx_orders', array(
	'condition' => $condition,
	'one' => true,
	'select' => 'state,yeepay_id',
));	

if(!empty($order))
{
	$key = $INI['system']['key'];
	if($order['state'] == 'pay')
	{
		$array['ret'] = 100;
		$array['status'] = "pay";
		$array['yeepay_id'] = $order['yeepay_id'];
		$array['sign'] = md5($array['ret'].$array['status'].$array['yeepay_id'].$key);
		$array['msg'] = '成功';
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 100;
		$array['status'] = "unpay";
		$array['yeepay_id'] = '';
		$array['sign'] = md5($array['ret'].$array['status'].$array['yeepay_id'].$key);
		$array['msg'] = "成功";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 1007;
	$array['msg'] = '该订单不存在';
	echo json_encode($array);exit;
}