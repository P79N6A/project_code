<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

//判断传递过来的参数条件
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

$sql = "select a.id as merchant_id,b.number as product_id,b.name as product_name,b.price as product_price,b.url as product_image,b.createtime as product_createtime from mr_merchants as a, mr_products as b where a.id=b.mid and a.type=2 and b.type=2 order by b.createtime desc limit $offset,$pagesize";
$product = DB::GetQueryResult($sql, false, 0);
if(!empty($product))
{
	foreach ($product as $key=>$value)
	{
		$product[$key]['product_image'] = $INI['system']['imgprefix'].'/'.$value['product_image'];
		$product[$key]['product_createtime'] = date('Y-m-d H:i:s', $value['product_createtime']);
	}
	$result = array(
	'ret'=>'100',
	'msg'=>'成功',
	'products'=>$product
	);
	echo json_encode( $result ) ;exit;
}
else 
{
	echo json_encode(array('ret'=>'1010','msg'=>'商品信息不存在'));exit;
}
