<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'getproductlist');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//商户ID
$merchant_id = isset($arr['merchant_id']) ? addslashes(strval(trim($arr['merchant_id']))) : "";
//如果不传时间则默认调用当前的时间
$now = time();
if(!empty($arr['requesttime']))
{
	$begintime = trim($arr['requesttime']);
}
else 
{
	$begintime = $now;
}
//滑动方式，如果不传值，默认为more,往下拉，刷新refresh
$type = isset($arr['type']) ? strval(trim($arr['type'])) : "more";
//商品状态
$product_status = isset($arr['product_status']) ? strval(trim($arr['product_status'])) : "";
if(!empty($merchant_id) && !empty($begintime) && !empty($type) && !empty($product_status))
{
	if($type == 'more')
	{
		$condition[] = "mid=$merchant_id AND createtime < $begintime AND type=2";
		//sale为出售中
		if($product_status == 'sale')
		{
			$condition[] = "status=1 AND ((end_time is NULL OR end_time > $now) AND (max_number is NULL OR max_number > sale_number))";
		}
		else 
		{
			$condition[] = "status=0 OR ((end_time <= $now) OR (max_number <= sale_number))";
		}
	}
	else 
	{
		$condition[] = "mid=$merchant_id AND createtime > $begintime AND type=2";
		if($product_status == 'sale')
		{
			$condition[] = "status=1 AND ((end_time is NULL OR end_time > $now) AND (max_number is NULL OR max_number > sale_number))";
		}
		else 
		{
			$condition[] = "status=0 OR ((end_time <= $now) OR (max_number <= sale_number))";
		}
	}
	$product = DB::LimitQuery('mr_products', array(
		'condition' => $condition,
		'order' => 'ORDER BY createtime DESC',
		'size' => 20,
	));
	if(!empty($product))
	{
		$alist = array();
		foreach ($product as $key=>$value)
		{
			$alist[$key]['product_url'] = $INI['system']['imgprefix'].'/'.$value['url'];
			$alist[$key]['product_id'] = $value['id'];
			$alist[$key]['product_name'] = $value['name'];
			$alist[$key]['product_price'] = $value['price'];
			$alist[$key]['product_number'] = $value['number'];
			if(!empty($value['express_price']))
			{
				$alist[$key]['product_express_price'] = $value['express_price'];
			}
			else 
			{
				$alist[$key]['product_express_price'] = '';
			}
			if(!empty($value['desc']))
			{
				$alist[$key]['product_desc'] = $value['desc'];
			}
			else 
			{
				$alist[$key]['product_desc'] = '';
			}
			if(!empty($value['end_time']))
			{
				$alist[$key]['product_end_time'] = date('Y-m-d',$value['end_time']);
			}
			else 
			{
				$alist[$key]['product_end_time'] = '';
			}
			if(!empty($value['max_number']))
			{
				$alist[$key]['product_max_number'] = $value['max_number'];
			}
			else 
			{
				$alist[$key]['product_max_number'] = '';
			}
			$alist[$key]['product_sale_number'] = $value['sale_number'];
			$alist[$key]['product_create_time'] = $value['createtime'];
		}
		$array = array(
			'ret' => 100,
			'msg' => '商品列表',
			'product_list' => $alist
		);
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "无商品数据";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}