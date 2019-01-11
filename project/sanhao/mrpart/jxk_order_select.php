<?php
require_once(dirname( dirname(__FILE__) ). '/autoload.php');

$condition = array('state'=>'unpay', 'paytype'=>'jxk') ;
//判断传递过来的参数条件
if( empty( $array_notify['mobile'] )){
	echo json_encode(array('ret'=>'1008','msg'=>'重要参数不能为空'));exit;
}else {
	$condition['zxbmobile'] = $array_notify['mobile'] ;
}
if( !isset( $array_notify['start_rows'] ) || empty( $array_notify['start_rows'] )) {
	$offset = 0;
}else {
	$offset = intval( $array_notify['start_rows'] ) ;
}
if( !isset( $array_notify['offset'] ) || empty( $array_notify['offset'] )) {
	$pagesize = 10;
}else {
	$pagesize = intval( $array_notify['offset'] ) ;
}

$orders = DB::LimitQuery('jx_orders', array(
		'condition' => $condition,
		'select'=>'pay_id,sid,pid,price,quantity,origin,createtime,charge',
		'order' => 'ORDER BY createtime DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	
if( empty( $orders ) ){
	echo json_encode(array('ret'=>'1007','msg'=>'订单数据为空或信息不正确'));exit;
}
//获取商户号
$sids = Utility::GetColumn($orders , 'sid' ) ;
$partners = Table::Fetch( 'jx_users' , $sids ) ;
//获取商品名称
$pids = Utility::GetColumn($orders , 'pid' ) ;
$products = Table::Fetch( 'jx_products' , $pids ) ;

//组成接口格式的数据
$data = array() ;
foreach ( $orders as $key => $val ){
	$tmp = array() ;
	$tmp['order_id'] = $val['pay_id'] ;
	$tmp['mobile'] = $partners[$val['sid']]['mobile'] ;
	$tmp['product_name'] = $products[$val['pid']]['pname'] ;
	$tmp['product_price'] = $val['price'] ;
	$tmp['order_number'] = $val['quantity'] ;
	$tmp['order_origin'] = $val['origin']+$val['charge'] ;
	if($val['charge'] == '0.00')
	{
		$tmp['order_charge'] = '' ;
	}
	else 
	{
		$tmp['order_charge'] = $val['charge'] ;
	}
	$tmp['order_createtime'] = date('Y-m-d H:i:s',$val['createtime']);
	array_push( $data , $tmp );
}
$result = array(
	'ret'=>'100',
	'msg'=>'success',
	'orders'=>$data
);
echo json_encode( $result ) ;exit;