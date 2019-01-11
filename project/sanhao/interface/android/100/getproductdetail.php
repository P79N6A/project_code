<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'getproductdetail');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

$product_id = isset($arr['product_id']) ? intval(trim($arr['product_id'])) : "";
$merchant_id = isset($arr['merchant_id']) ? addslashes(strval(trim($arr['merchant_id']))) : "";
if(!empty($product_id) && !empty($merchant_id))
{
	$condition = array( 'id' => $product_id);
	$product = DB::LimitQuery('mr_products', array(
		'condition' => $condition,
		'one' => true,
	));
	if(!empty($product))
	{
		$array['ret'] = 100;
		$array['msg'] = "商品详情";
		$array['product_id'] = $product['id'];
		$array['product_name'] = $product['pname'];
		$array['product_price'] = $product['price'];
		$array['product_number'] = $product['number'];
		$array['product_express_price'] = $product['express_price'];
		$array['product_desc'] = $product['desc'];
		$array['product_end_time'] = $product['end_time'];
		$array['product_max_number'] = $product['max_number'];
		$array['product_sale_number'] = $product['sale_number'];
		$array['product_url'] = $product['purl'];
		//判断商品的状态以及商品是否是自己的
		$now = time();
		if($product['end_time'] != '' && $product['max_number'] != '')
		{
			//商品已下架
			if(($product['end_time'] < $now) || ($product['max_number']-$product['sale_number'] <= 0))
			{
				$array['product_status'] = "under";
			}
			else 
			{
				$array['product_status'] = "normal";
			}
		}
		else if($product['end_time'] != '' && $product['max_number'] == '')
		{
			//商品已下架
			if($product['end_time'] < $now)
			{
				$array['product_status'] = "under";
			}
			else 
			{
				$array['product_status'] = "normal";
			}
		}
		else if($product['end_time'] == '' && $product['max_number'] != '')
		{
			if($product['max_number']-$product['sale_number'] <= 0)
			{
				$array['product_status'] = "under";
			}
			else 
			{
				$array['product_status'] = "normal";
			}
		}
		else 
		{
			$array['product_status'] = "normal";
		}
//		if($merchant_id = $product['mid'])
//		{
//			if($array['product_status'] = "normal")
//			{
//				$array['product_isme'] = 'yes';
//			}
//			else 
//			{
//				$array['product_isme'] = 'yesunder';
//			}
//		}
//		else 
//		{
//			$array['product_isme'] = 'no';
//		}
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "无商品详细信息";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}