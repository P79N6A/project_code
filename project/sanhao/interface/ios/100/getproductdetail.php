<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');
// $_POST = array('product_id'=>80);
$arr = getparameter($_POST, 'getproductdetail');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

$product_id = isset($arr['product_id']) ? intval(trim($arr['product_id'])) : "";
if(!empty($product_id))
{
	$condition = array( 'id' => $product_id);
	$product = DB::LimitQuery('jx_products', array(
		'condition' => $condition,
		'one' => true,
	));
	if(!empty($product))
	{
		$con = array('id'=>$product['uid']);
		$uinfo = DB::LimitQuery('jx_users',array(
				'condition'=>$con,
				'one'=>true
		));
		$array['ret'] = 100;
		$array['msg'] = "商品详情";
		$array['product_id'] = $product['id'];
		$array['product_user_id'] = $product['uid'];
		$array['product_user_mobile'] = $uinfo['mobile'];	
		$array['product_user_url'] = empty($uinfo['headerurl'])?$INI['system']['imgprefix'].'/static/images/50.png':$INI['system']['imgprefix'].'/'.$uinfo['headerurl'];	
		$array['product_name'] = $product['pname'];
		$array['product_price'] = $product['price'];
		$array['product_express_price'] = $product['express_price'];
		$array['product_desc'] = $product['description'];
		$array['product_end_time'] = $product['end_time'];
		$array['product_max_number'] = $product['max_number'];
		$array['product_sale_number'] = $product['sale_number'];
		$cimage = array('pid'=>$product['id'],'type'=>1);
		$aField = DB::LimitQuery('jx_products_image',array(
				'condition'=>$cimage,
				'one'=>true
		));
		$array['product_url'] = $INI['system']['imgprefix'].'/'.str_replace('product/big', 'product/small', $aField['image']);
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
				if($product['status'] == 1){
					$array['product_status'] = "normal";
				}else if($product['status'] == 3){
					$array['product_status'] = "under";
				}else if($product['status'] == 4){
					$array['product_status'] = "complete";
				}else{
					$array['product_status'] = "del";
				}
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
				if($product['status'] == 1){
					$array['product_status'] = "normal";
				}else if($product['status'] == 3){
					$array['product_status'] = "under";
				}else if($product['status'] == 4){
					$array['product_status'] = "complete";
				}else{
					$array['product_status'] = "del";
				}
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
				if($product['status'] == 1){
					$array['product_status'] = "normal";
				}else if($product['status'] == 3){
					$array['product_status'] = "under";
				}else if($product['status'] == 4){
					$array['product_status'] = "complete";
				}else{
					$array['product_status'] = "del";
				}
			}
		}
		else 
		{
			if($product['status'] == 1){
				$array['product_status'] = "normal";
			}else if($product['status'] == 3){
				$array['product_status'] = "under";
			}else if($product['status'] == 4){
				$array['product_status'] = "complete";
			}else{
				$array['product_status'] = "del";
			}
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
// var_dump('<pre>',$array);die;
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