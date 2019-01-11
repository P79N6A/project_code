<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

//判断传递过来的参数条件
if( empty( $array_notify['product_id'] )){
	echo json_encode(array('ret'=>'1008','msg'=>'重要参数不能为空'));exit;
}

//根据商品编号判断商品的信息
$product = DB::GetTableRow('mr_products' , array(
				'number'=>$array_notify['product_id']
			));
if( empty( $product ) ){
	echo json_encode(array('ret'=>'1010','msg'=>'商品信息不存在'));exit;
}
else 
{
	$array['ret'] = 100;
	$array['msg'] = "成功";
	$array['product_id'] = $product['number'];
	$array['product_name'] = $product['name'];
	$array['product_price'] = $product['price'];
	if(!empty($product['desc']))
	{
		$array['product_desc'] = $product['desc'];
	}
	else 
	{
		$array['product_desc'] = '';
	}
	$array['product_image'] = $INI['system']['imgprefix'].'/'.$product['url'];
	$array['product_createtime'] = date('Y-m-d H:i:s', $product['createtime']);
	$key = $INI['system']['key'];
	$array['sign'] = md5($array['product_createtime'].$array['product_desc'].$array['product_id'].$array['product_image'].$array['product_name'].$array['product_price'].$array['ret'].$key);
	echo json_encode($array);exit;
}