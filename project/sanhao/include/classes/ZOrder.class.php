<?php
class ZOrder {
	static public function OnlineIt($order_id, $pay_id, $money,  $service='BOCOM'){ 
		if (!$order_id || !$pay_id || $money <= 0 ) return false;
		$order = Table::Fetch('jx_orders', $order_id);
		
		//记录支付记录
		if ( $order['state'] == 'unpay' ) {

			//$table = new Table('jx_pays');
			//$table->id = $pay_id;
			//$table->money = $money;
			//$table->service = $service;
			//$table->createtime = time();
			//$ia = array('id', 'money', 'service', 'createtime');

			//if (Table::Fetch('jx_pays', $pay_id) || ! $table->insert($ia)) {
			//	return false;
			//}
			
			//修改订单状态
			$uarray = array( 'state' => 'pay','paytime'=>time());
			Table::UpdateCache('jx_orders', $order_id, $uarray);
			
			//修改商品已售数量
			$sql = "update `jx_products` set sale_number=sale_number+'".$order['quantity']."' where id=".$order['pid'];
			DB::Query($sql);
		
		}
		return true;
	}

	
}
?>
