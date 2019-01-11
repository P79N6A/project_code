<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'deladdress');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//商户id
$user_id = isset($arr['user_id']) ? intval(trim($arr['user_id'])) : "";
//地址编号
$address_id = isset($arr['address_id']) ? intval(trim($arr['address_id'])) : "";

if(!empty($user_id) && !empty($address_id))
{
	$condition = array( 'uid' => $user_id);
	$address = DB::LimitQuery('jx_address', array(
			'condition' => $condition,
	));
	if($address){
		Table::Delete('jx_address', $address_id);

			$array['ret'] = 100;
			$array['msg'] = "删除成功";
			echo json_encode($array);exit;
	}else{
		$array['ret'] = 102;
		$array['msg'] = "删除失败";
		echo json_encode($array);exit;
	}
	
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}