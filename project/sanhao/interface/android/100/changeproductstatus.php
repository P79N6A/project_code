<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'changeproductstatus');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

$product_id = isset($arr['product_id']) ? intval(trim($arr['product_id'])) : "";
$merchant_id = isset($arr['merchant_id']) ? addslashes(strval(trim($arr['merchant_id']))) : "";

if(!empty($product_id) && !empty($merchant_id))
{
	$table = new Table('mr_products', $_POST);
	$table->pk_value = $product_id;
	$table->status = 0;
	$up_array = array('status');
	$flag = $table->update( $up_array );
	if($flag)
	{
		$array['ret'] = 100;
		$array['msg'] = "成功";
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "下架失败";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}