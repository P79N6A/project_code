<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');
$arr = getparameter($_POST, 'senddeliveryinfo');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

 $order_id = isset($arr['order_id']) ? trim($arr['order_id']) : '';
 $express_name = isset($arr['express_name']) ? trim($arr['express_name']) : '';
 $express_id = isset($arr['express_id']) ? trim($arr['express_id']) : '';
 $pay_id = isset($arr['order_pay_id']) ? trim($arr['order_pay_id']) : '';
 
 $orderlist = DB::GetTableRow('jx_orders', array( "id=".$order_id." and pay_id='".$pay_id."'"));
 if(!empty($order_id) && !empty($express_name) && !empty($express_id) && $orderlist){
 	
 	$table = new Table('jx_orders', $_POST);
 	$table->pk_value = $order_id;
 	$table->express_name = $express_name;
 	$table->express_id = $express_id;
 	$table->state = 'complete';
 	$table->shiptime = time();
 	$up_array = array('express_name', 'express_id', 'state', 'shiptime');
 	
 	$flag = $table->update( $up_array );
 	if($flag)
 	{
 		$array['ret'] = 100;
 		$array['msg'] = "快递信息添加成功";
 		echo json_encode($array);exit;
 	}
 	else
 	{
 		$array['ret'] = 102;
 		$array['msg'] = "快递信息添加失败";
 		echo json_encode($array);exit;
 	}
 }else{
 	$array['ret'] = 101;
 	$array['msg'] = "参数错误";
 	echo json_encode($array);exit;
 }