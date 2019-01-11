<?php
require_once(dirname( dirname(__FILE__) ). '/autoload.php');

//1.判断参数是否都存在
if( empty( $array_notify['card_code'] ) || empty( $array_notify['order_id'] ) || empty( $array_notify['mobile'] ) ){
	echo json_encode(array('ret'=>'1008','msg'=>'重要参数不能为空'));exit;
}
//2.根据卡磁信息进行解密，判断卡号状态、余额信息
$code = $array_notify['card_code'] ;
//取前16位为卡号

$extracode = Crypt3Des::decrypt( $code );
$cno = substr( $extracode , 0 , 16 ) ;
$extracode = substr( $extracode , 16 );

//先根据卡号查询卡是否存在
$cardinfo = DB::GetTableRow('jx_cards' , array(
				'cno'=>$cno,
				'status'=>3
			));
if(!empty($cardinfo))
{			
	//卡的附加码
	$cardcode = $cardinfo['code'];
	//然后将卡的附加码转换成ASCII 码
	$codecno = '';
	for($i=0; $i<strlen($cardcode);$i++)
	{
		$codecno .= ord($cardcode[$i]);
	}
	if($extracode != $codecno)
	{
		echo json_encode(array('ret'=>'1006','msg'=>'交享卡卡附加码信息不正确'));exit;
	}
}
else 
{
	echo json_encode(array('ret'=>'1005','msg'=>'交享卡数据为空或信息不正确'));exit;
}
//根据订单号获取订单信息
$order = DB::GetTableRow('jx_orders' , array('pay_id'=>$array_notify['order_id'],'zxbmobile'=>$array_notify['mobile'] )) ;
if( !$order || empty( $order ) ){
	echo json_encode(array('ret'=>'1007','msg'=>'订单信息不存在'));exit;
}
//获取商品信息
$product = DB::GetTableRow('jx_products',array('id'=>$order['pid'] ) ) ;
if( !$product || empty( $product ) ){
	echo json_encode(array('ret'=>'1010','msg'=>'商品信息不存在'));exit;
}
//判断卡余额是否足够支付
$paymoney = $order['origin'] + $order['charge'] ;
if( $cardinfo['money'] < $paymoney ){
	$result = array(
		'ret'=>'1009',
		'msg'=>'卡余额不足',
		'order_id'=>$order['pay_id'],
		'order_origin'=>$order['origin'],
		'product_name'=>$product['pname'],
		'order_number'=>$order['quantity'],
		'order_status'=>'unpay',
		'order_paytime'=>'',
		'card_no'=>$cno,
		'card_balance'=>$cardinfo['money'],
		'card_valid'=>$cardinfo['endtime'],
	);
	echo json_encode( $result ) ;exit;
}
//3.卡信息正确，进行扣款
//更新订单状态
$paytime = time() ;
$ret = Table::UpdateCache('jx_orders', $order['id'] , array('state'=>'pay','paytime'=>$paytime,'relation'=>$cno)) ;
if( $ret ){
	//扣除卡余额
	$ret2 = Table::UpdateCache('jx_cards',$cardinfo['id'],array('money'=>($cardinfo['money']-$paymoney)));
	if( $ret2 ){
		//修改商品已售数量
		$sql = "update `jx_products` set sale_number=sale_number+'".$order['quantity']."' where id=".$order['pid'];
		DB::Query($sql);
		//给卖家商户转账
		
		$config = require_once(DIR_LIBARAY."/icardpay/KafkaConfig.php");
		//转账接口
		$merchantId = $config[2]['merchantId'];
		$signType = $config[2]['signType'];
		$keyFile = $config[2]['keyFile'];
		$password = $config[2]['password'];
		$merchantKey = $config[2]['merchantKey'];
		$payNo = $config[2]['payNo'];
		//IP地址
		$appip = $config[2]['appip'];
		
		$nowtime = date('YmdHi');
		//收款人(卖家)的手机号,根据订单号获取卖家的id,然后在获取手机号
		$user = DB::LimitQuery('jx_users', array(
			'condition' => array('id' => $order['sid']),
			'one'=>true
		));
		//查询绑定关系表中对应的支付通账号
		$binding = DB::LimitQuery('jx_bindings', array(
			'condition' => array('mobile' => $user['mobile']),
			'one'=>true
		));
		$mobile = $binding['payno'];
		//订单id对应为购买卡时候的订单id
		$pay_id = $cardinfo['pid'] ;
		//转账金额
		$amount = $order['origin']*100;
		$mac = md5($merchantId.$payNo.$mobile.$pay_id.$nowtime.$merchantKey);
		//记录转账接口返回的内容
		$transferdate = DIR_ROOT.'/log/icardpay/transfer/'.date('Y-m-d');

		if(!file_exists( $transferdate ))
		{
			@mkdir($transferdate);
		}
		
		//调用支付通转账接口
		$url = $appip.'/hk-frt-sys-web/F20111.front';
		$data = "merNo=".$merchantId."&payNo=".$payNo."&userMoblieNo=".$mobile."&amount=".$amount."&prdOrdNo=".$pay_id."&TrDt=".$nowtime."&MAC=".$mac;
		file_put_contents($transferdate.'/'.$pay_id.'_'.time().'_'.$order['origin'].'.txt' , print_r( $url."?".$data , true ) ) ;
		$ret3 = json_decode(interface_post($url, $data)); 
		file_put_contents($transferdate.'/'.$pay_id.'_'.time().'.txt' , print_r( $ret3 , true ) ) ;
		//记录日志
		/***************************/
		//end
		if($ret3->RSPCD == '00000')
		{
			//记录从三好网转出的账户信息
			$u_array['pay_id'] = $pay_id;
			$u_array['amount_transferred'] = $order['origin'];
			$u_array['whereabouts'] = $mobile;
			$u_array['type'] = 'settle';
			$u_array['createtime'] = time();
			$u_array['id'] = DB::Insert('jx_transfer_records', $u_array);
		}
		else 
		{
			//转账失败，记录转账失败的信息
			$u_array['pay_id'] = $pay_id;
			$u_array['amount_transferred'] = $order['origin'];
			$u_array['whereabouts'] = $mobile;
			$u_array['type'] = 'failed';
			$u_array['createtime'] = time();
			$u_array['id'] = DB::Insert('jx_transfer_records', $u_array);
		}
	}
	$cardother = DB::GetTableRow('jx_cards',array('id'=>$cardinfo['id'] ) ) ;
	//4.返回订单支付结果和卡信息
	$result = array(
			'ret'=>'100',
			'msg'=>'success',
			'order_id'=>$order['pay_id'],
			'order_origin'=>$order['origin']+$order['charge'],
			'product_name'=>$product['pname'],
			'order_number'=>$order['quantity'],
			'order_status'=>'pay',
			'order_paytime'=>$paytime,
			'card_no'=>$cno,
			'card_balance'=>$cardother['money'],
			'card_valid'=>$cardinfo['endtime'],
		);
	echo json_encode( $result ) ;exit;
}else{
	$result = array(
		'ret'=>'1009',
		'msg'=>'卡余额不足',
		'order_id'=>$order['pay_id'],
		'order_origin'=>$order['origin']+$order['charge'],
		'product_name'=>$product['pname'],
		'order_number'=>$order['quantity'],
		'order_status'=>'unpay',
		'order_paytime'=>'',
		'card_no'=>$cno,
		'card_balance'=>$cardinfo['money'],
		'card_valid'=>$cardinfo['endtime'],
	);
	echo json_encode( $result ) ;exit;
}

