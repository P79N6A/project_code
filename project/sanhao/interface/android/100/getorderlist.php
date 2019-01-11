<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'getorderlist');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

$now = time();
//商户手机号码
$merchant_mobile = isset($arr['merchant_mobile']) ? addslashes(strval(trim($arr['merchant_mobile']))) : '';
//商品ID
$product_id = isset($arr['product_id']) ? addslashes(intval(trim($arr['product_id']))) : '';
//开始时间
$begintime = isset($arr['begintime']) ? addslashes(strval(trim($arr['begintime']))) : '';
//结束时间
$endtime = isset($arr['endtime']) ? addslashes(strval(trim($arr['endtime']))) : '';
//如果不传时间则默认调用当前的时间
if(!empty($arr['requesttime']))
{
	$requesttime = trim($arr['requesttime']);
}
else 
{
	$requesttime = $now;
}
//滑动方式，如果不传值，默认为more,往下拉，刷新refresh
$type = isset($arr['type']) ? strval(trim($arr['type'])) : "more";
if(!empty($begintime) && !empty($endtime))
{
	if($begintime > $endtime)
	{
		$array['ret'] = 103;
		$array['msg'] = "开始时间不能大于结束时间";
		echo json_encode($array);exit;
	}
}
if(!empty($merchant_mobile) && !empty($requesttime) && !empty($type))
{
	$condition = array("saler_mobile" => $merchant_mobile);
	$conditionall = array("saler_mobile" => $merchant_mobile);
	if(!empty($begintime))
	{
		$begindate = strtotime($begintime.'00:00:00');
		$condition[] = "createtime >= $begindate";
		$conditionall[] = "createtime >= $begindate";
	}
	if(!empty($endtime))
	{
		$enddate = strtotime($endtime.'23:59:59');
		$condition[] = "createtime <= $enddate";
		$conditionall[] = "createtime <= $enddate";
	}
	if(!empty($product_id))
	{
		$condition[] = "pid=$product_id";
		$conditionall[] = "pid=$product_id";
	}
	if($type == "more")
	{
		$condition[] = "(state = 'pay' or state = 'complete') AND createtime < $requesttime";
	}
	else 
	{
		$condition[] = "(state = 'pay' or state = 'complete') AND createtime > $requesttime";	
	}
	$conditionall[] = "state = 'pay' or state = 'complete'";
	$totalorigin = Table::Count('mr_orders', $conditionall, 'origin');
	$aorderlist = DB::LimitQuery('mr_orders', array(
		'condition' => $condition,
		'order'=>'ORDER BY createtime DESC',
		'size' => 20,
	));
	if(!empty($aorderlist))
	{
		$alist = array();
		foreach ($aorderlist as $key=>$value)
		{
			$alist[$key]['order_id'] = $value['id'];
			$alist[$key]['order_pay_id'] = $value['pay_id'];
			$alist[$key]['order_create_time'] = $value['createtime'];
			$alist[$key]['order_public_time'] = date('Y-m-d H:i:s', $value['createtime']);
			$alist[$key]['order_state'] = $value['state'];
			$alist[$key]['order_origin'] = $value['origin'];
			$product = Table::Fetch('mr_products', $value['pid']);
			$alist[$key]['order_product']['product_id'] = $product['id'];
			if(!empty($product['name']))
			{
				$alist[$key]['order_product']['product_name'] = $product['name'];
			}
			else 
			{
				$alist[$key]['order_product']['product_name'] = '';
			}
			if(!empty($product['price']))
			{
				$alist[$key]['order_product']['product_price'] = $product['price'];
			}
			else 
			{
				$alist[$key]['order_product']['product_price'] = '';
			}
			if(!empty($product['url']))
			{
				$alist[$key]['order_product']['product_url'] = $product['url'];
			}
			else 
			{
				$alist[$key]['order_product']['product_url'] = '';
			}
		}
		$array = array(
			'ret' => 100,
			'msg' => '商品订单列表',
			'order_total'=>$totalorigin,
			'order_list' => $alist
		);
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "无商品订单数据";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}