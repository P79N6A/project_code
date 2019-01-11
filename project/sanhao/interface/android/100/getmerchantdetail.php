<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'getmerchantdetail');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//商户ID
$merchant_id = isset($arr['merchant_id']) ? addslashes(strval(trim($arr['merchant_id']))) : "";

if(!empty($merchant_id))
{
	$condition = array( 'id' => $merchant_id);
	$merchant = DB::LimitQuery('mr_merchants', array(
		'condition' => $condition,
		'one' => true,
	));
	
	if(!empty($merchant))
	{
		$array['ret'] = 100;
		$array['msg'] = "商户详情";
		$array['merchant_id'] = $merchant['id'];
		$array['merchant_mobile'] = $merchant['mobile'];
		$array['merchant_name'] = $merchant['merchant_name'];
		$array['merchant_identity'] = $merchant['identity'];
		$array['merchant_front_photo'] = $INI['system']['imgprefix'].'/'.$merchant['front_photo'];
		$array['merchant_back_photo'] = $INI['system']['imgprefix'].'/'.$merchant['back_photo'];
		$array['merchant_photo'] = $INI['system']['imgprefix'].'/'.$merchant['photo'];
		$array['merchant_device_number'] = $merchant['device_number'];
		$array['merchant_status'] = $merchant['status'];
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "无商户详细信息";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}