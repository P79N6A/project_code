<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

//先查询出所有为付款订单
$condition = array( 'state'=>'unpay' );
$orderlist = DB::LimitQuery('jx_orders', array(
	'condition' => $condition,
));
$now = time();
foreach ($orderlist as $key=>$value)
{
	//如果订单生成时间大于1小时，则把订单状态改为删除
	if($now-$value['createtime'] > 60*60)
	{
		$sql = "update `jx_orders` set state='del' where id=".$value['id'];
		DB::Query($sql);
	}
}
echo 'success';exit;
?>