<?php
require_once(dirname( dirname(__FILE__) ). '/autoload.php');

//1.判断参数是否都存在
if( empty( $array_notify['card_code'] ) ) {
	echo json_encode(array('ret'=>'1008','msg'=>'重要参数不能为空'));exit;
}

//2.根据卡磁信息进行解密，判断卡号状态、余额信息、有效期
$code = Crypt3Des::decrypt($array_notify['card_code']) ;

//3.取卡信息
$cardinfo = DB::GetTableRow('jx_cards' , array(
				'cno'=>$code,
				'status'=>3
			));
if( !$cardinfo || empty( $cardinfo ) ){
	echo json_encode(array('ret'=>'1005','msg'=>'交享卡不存在或信息错误'));exit;
}
//3.取卡交易信息
$total_origin = Table::Count('jx_orders',array('paytype'=>'jxk','relation'=>$code,"state = 'pay' or state = 'complete'"), 'origin');
$total_charge = Table::Count('jx_orders',array('paytype'=>'jxk','relation'=>$code,"state = 'pay' or state = 'complete'"), 'charge');

if( $total_origin > 0 ){
	$orders = DB::LimitQuery('jx_orders', array(
			'condition' => array('paytype'=>'jxk','relation'=>$code,"state = 'pay' or state = 'complete'"),
			'order' => 'ORDER BY createtime DESC',
			'size' => 100,
			'offset' => 0,
		));
}else {
	$orders = array() ;
}

//5.返回卡信息
if( empty( $orders ) ){
	echo json_encode(array('ret'=>'1007','msg'=>'订单数据为空或信息不正确','card_no'=>$code,'card_balance'=>$cardinfo['money'],'total_origin'=>0));exit;
}
//获取商品名称
$pids = Utility::GetColumn($orders , 'pid' ) ;
$products = Table::Fetch( 'jx_products' , $pids ) ;
//组成接口格式的数据
$data = array() ;
foreach ( $orders as $key => $val ){
	$tmp = array() ;
	$tmp['order_id'] = $val['pay_id'] ;
	$tmp['product_name'] = $products[$val['pid']]['pname'] ;
	$tmp['order_number'] = $val['quantity'] ;
	$tmp['order_origin'] = $val['origin']+$val['charge'] ;
	$tmp['order_paytime'] = date('Y-m-d H:i:s',$val['paytime']) ;
	array_push( $data , $tmp );
}
$result = array(
	'ret'=>'100',
	'msg'=>'success',
	'card_no'=>$code,
	'card_balance'=>$cardinfo['money'],
	'total_origin'=>$total_origin+$total_charge,
	'orders'=>$data
);

echo json_encode( $result ) ;exit;