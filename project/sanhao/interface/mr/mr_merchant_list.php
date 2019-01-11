<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

//判断传递过来的参数条件
if( empty( $array_notify['longitude'] ) || empty( $array_notify['latitude'] ) ){
	echo json_encode(array('ret'=>'1008','msg'=>'重要参数不能为空'));exit;
}

$condition = array('type'=>2);
//查询所有的商户
$merchant = DB::LimitQuery('mr_merchants', array(
		'condition' => $condition,
		'order' => 'ORDER BY createtime DESC',
	));
if(!empty($merchant))
{
	$amerchantlist = array();
	foreach($merchant as $key=>$value)
	{
		$amerchantlist[$key]['merchant_id'] = $value['id'];
		$amerchantlist[$key]['merchant_mobile'] = $value['mobile'];
		$amerchantlist[$key]['merchant_name'] = $value['merchant_nickname'];
		$amerchantlist[$key]['merchant_desc'] = $value['merchant_desc'];
		$amerchantlist[$key]['merchant_logo'] = $INI['system']['imgprefix'].'/'.$value['merchant_logo'];
		$amerchantlist[$key]['merchant_phone'] = $value['merchant_phone'];
		$amerchantlist[$key]['merchant_address'] = $value['address'];
		$amerchantlist[$key]['merchant_longitude'] = $value['merchant_longitude'];
		$amerchantlist[$key]['merchant_latitude'] = $value['merchant_latitude'];
		$amerchantlist[$key]['currinstance'] = getdistance($array_notify['longitude'], $array_notify['latitude'], $value['merchant_longitude'], $value['merchant_latitude']);
	}
	$amerchantlist_sort = multi_array_sort($amerchantlist,'currinstance');
	$result = array(
	'ret'=>'100',
	'msg'=>'成功',
	'merchants'=>$amerchantlist_sort
	);
	echo json_encode( $result ) ;exit;
}
else 
{
	echo json_encode(array('ret'=>'1013','msg'=>'商家信息不存在'));exit;
}