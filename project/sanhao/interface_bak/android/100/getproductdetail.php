<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

$pid = isset($arr['pid']) ? intval(trim($arr['pid'])) : "";
$uid = isset($arr['uid']) ? intval(trim($arr['uid'])) : "";

if(!empty($pid) && !empty($uid))
{
	$condition = array( 'id' => $pid);
	$product = DB::LimitQuery('jx_products', array(
		'condition' => $condition,
		'one' => true,
	));
	if(!empty($product))
	{
		$con = array('pid'=>$pid,'type'=>1);
		//获取商品的图片
		$image = DB::LimitQuery('jx_products_image', array(
			'condition' => $con,
			'one'=>true,
		));	
		$product['purl'] = $image['image'];
		//获取卖家的信息
		$consaler = array( 'id' => $product['uid']);
		$user = DB::LimitQuery('jx_users', array(
			'condition' => $consaler,
			'one'=>true
		));	
		//获取卖家的头像
		if($user['headerurl'] != '')
		{
			$product['user_url'] = str_replace('user/old', 'user/big', $user['headerurl']);
		}
		else 
		{
			$product['user_url'] = '';
		}
		$array['ret'] = 100;
		$array['msg'] = "商品详情";
		$array['product_pid'] = $product['id'];
		$array['product_pname'] = $product['pname'];
		$array['product_price'] = $product['price'];
		$array['product_express_price'] = $product['express_price'];
		$array['product_description'] = $product['description'];
		$array['product_end_time'] = $product['end_time'];
		$array['product_max_number'] = $product['max_number'];
		$array['product_sale_number'] = $product['sale_number'];
		$array['product_purl'] = $product['purl'];
		$array['user_url'] = $product['user_url'];
		$array['user_id'] = $product['uid'];
		//判断商品的状态以及商品是否是自己的
		$now = time();
		if($product['end_time'] != '' && $product['max_number'] != '')
		{
			//商品已下架
			if(($product['end_time'] < $now) || ($product['max_number']-$product['sale_number'] <= 0))
			{
				$array['product_pstatus'] = "under";
			}
			else 
			{
				$array['product_pstatus'] = "normal";
			}
		}
		else if($product['end_time'] != '' && $product['max_number'] == '')
		{
			//商品已下架
			if($product['end_time'] < $now)
			{
				$array['product_pstatus'] = "under";
			}
			else 
			{
				$array['product_pstatus'] = "normal";
			}
		}
		else if($product['end_time'] == '' && $product['max_number'] != '')
		{
			if($product['max_number']-$product['sale_number'] <= 0)
			{
				$array['product_pstatus'] = "under";
			}
			else 
			{
				$array['product_pstatus'] = "normal";
			}
		}
		else 
		{
			$array['product_pstatus'] = "normal";
		}
		if($uid = $product['uid'])
		{
			if($array['product_pstatus'] = "normal")
			{
				$array['product_pisme'] = 'yes';
			}
			else 
			{
				$array['product_pisme'] = 'yesunder';
			}
		}
		else 
		{
			$array['product_pisme'] = 'no';
		}
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