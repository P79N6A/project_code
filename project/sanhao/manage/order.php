<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(dirname(__FILE__)) . '/include/function/right.php');

need_manager();
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "list";
$menucolor = '全部订单';
if($action == 'list')
{
	//订单号
	$order_id = isset($_REQUEST['orderid']) ? $_REQUEST['orderid'] : "";
	//订单状态
	$state = isset($_REQUEST['state']) ? $_REQUEST['state'] : "";
	//总金额最小值
	$smallorigin = isset($_REQUEST['smallorigin']) ? $_REQUEST['smallorigin'] : "";
	//总金额最大值
	$bigorigin = isset($_REQUEST['bigorigin']) ? $_REQUEST['bigorigin'] : "";
	//起始时间
	$begintime = isset($_REQUEST['begintime']) ? strtotime($_REQUEST['begintime']) : "";
	$newbegintime = isset($_REQUEST['begintime']) ? $_REQUEST['begintime'] : "";
	//终止时间
	$endtime = isset($_REQUEST['endtime']) ? strtotime($_REQUEST['endtime']) : "";
	$newendtime = isset($_REQUEST['endtime']) ? $_REQUEST['endtime'] : "";
	//卖家账户
	$saleraccount = isset($_REQUEST['saleraccount']) ? $_REQUEST['saleraccount'] : "";
	//买家账户
	$buyeraccount = isset($_REQUEST['buyeraccount']) ? $_REQUEST['buyeraccount'] : "";
	if($_POST['state'] == 'all'){		
		$condition = array("state != 'del'");
	}
	$condition = array("id > 0");
	if(!empty($order_id))
	{
		$condition[] = "pay_id like '%".$order_id."%'";
	}
	if(!empty($state) && $state != 'all')
	{
		$condition[] = "state = '$state'";
	}
	if(!empty($smallorigin))
	{
		$condition[] = "origin >= $smallorigin";
	}
	if(!empty($bigorigin))
	{
		$condition[] = "origin <= $bigorigin";
	}
	if(!empty($begintime))
	{
		$condition[] = "createtime >= $begintime";
	}
	if(!empty($endtime))
	{
		$condition[] = "createtime <= $endtime";
	}
	if(!empty($saleraccount))
	{
		$usersaler = DB::LimitQuery('jx_users', array(
			'condition' => array('mobile' => $saleraccount),
			'one'=>true
		));
		$sid = $usersaler['id'];
		$condition[] = "sid = $sid";
	}
	if(!empty($buyeraccount))
	{
		$userbuyer = DB::LimitQuery('jx_users', array(
			'condition' => array('mobile' => $buyeraccount),
			'one'=>true
		));
		$uid = $userbuyer['id'];
		$condition[] = "uid = $uid";
	}
// 	var_dump('<pre>',$condition);die;
	$count = Table::Count('jx_orders', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	
	$order = DB::LimitQuery('jx_orders', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	if(!empty($order))
	{
		foreach($order as $key=>$value)
		{
			$usersaler = DB::LimitQuery('jx_users', array(
				'condition'=>array('id'=>$value['sid']),
				'one'=>true
			));
			$userbuyer = DB::LimitQuery('jx_users', array(
				'condition'=>array('id'=>$value['uid']),
				'one'=>true
			));
			$product = DB::LimitQuery('jx_products', array(
				'condition'=>array('id'=>$value['pid']),
				'one'=>true
			));
			$order[$key]['productname'] = $product['pname'];
			$order[$key]['saler'] = $usersaler['mobile'];
			$order[$key]['buyer'] = $userbuyer['mobile'];
		}
	}
// 	var_dump('<pre>',$order);die;
	if ( strval($_GET['download'])) 
	{
		$name = "order_{$state}_".date('Ymd');
		$kn = array(
			'id' => '订单编号',
			'productname' => '商品名称',
			'saler' => '卖家',
			'buyer' => '买家',
			'num' => '数量',
			'origin' => '总额',
			'state' => '订单状态',
		);
		foreach($order AS $cid => $one) {
			$one['id'] = $one['id'];
			$one['productname'] = moneyit($one['value']);
	        $cards[$cid] = $one;
		}
		down_xls($cards, $kn, $name);
	}
	$countsum = Table::Count('jx_orders', array(array("id > 0")));
	$countpaysum = Table::Count('jx_orders', array(array("state = 'pay'")));
	$countunpaysum = Table::Count('jx_orders', array(array("state = 'unpay'")));
	$countcompletesum = Table::Count('jx_orders', array(array("state = 'complete'")));
	$countdelsum = Table::Count('jx_orders', array(array("state = 'del'")));
	
	include template('manage_order_index');
}
else if($action == "detail")
{
	$id = strval($_GET['id']);
	$condition = array('id' => $id);
	//查询订单的信息
	$order = DB::LimitQuery('jx_orders', array(
			'condition' => $condition,
			'one'=>true
	));
	//查询省市区
	$conditionprovince = array( 'id' => $order['province_id']);
	$province = DB::LimitQuery('jx_areas', array(
			'condition' => $conditionprovince,
		));
	$concity = array( 'id' => $order['city_id'] );
	$city = DB::LimitQuery('jx_areas', array(
			'condition' => $concity,
	));
	$conarea = array( 'id' => $order['area_id'] );
	$area = DB::LimitQuery('jx_areas', array(
			'condition' => $conarea,
	));
	
	//查询商品的信息
	$conditionproduct = array('id'=>$order['pid']);
	$product = DB::LimitQuery('jx_products', array(
			'condition' => $conditionproduct,
			'one'=>true
	));
	$productimage = DB::LimitQuery('jx_products_image', array(
			'condition' => array('pid'=>$order['pid']),
			'one'=>true
	));
	$productproperty = DB::LimitQuery('jx_products_property', array(
			'condition' => array('pid'=>$order['pid']),
			'one'=>true
	));
	//查询付款时间
	$conditionfukuan = array(
		'pay_id' => $order['pay_id'],
		'type' => 'pay',
	);
	$orderfukuan = DB::LimitQuery('jx_financial_records', array(
			'condition' => $conditionfukuan,
			'one'=>true
	));
	//查询结算成功的时间
	$conditionjiesuan = array(
		'pay_id' => $order['pay_id'],
		'type' => 'settle',
	);
	$orderjiesuan = DB::LimitQuery('jx_financial_records', array(
			'condition' => $conditionjiesuan,
			'one'=>true
	));
	//查询买家的信息和支付通账户
	$userbuyer = DB::LimitQuery('jx_users', array(
			'condition' => array('id'=>$order['uid']),
			'one'=>true
	));
	$userbuyericardpay = DB::LimitQuery('jx_bindings', array(
			'condition' => array('mobile'=>$userbuyer['mobile']),
			'one'=>true
	));
	//查询卖家的信息
	$usersaler = DB::LimitQuery('jx_users', array(
			'condition' => array('id'=>$order['sid']),
			'one'=>true
	));
	$usersalericardpay = DB::LimitQuery('jx_bindings', array(
			'condition' => array('mobile'=>$usersaler['mobile']),
			'one'=>true
	));
	
	if(!empty($order['createtime'])){
		$order['createtime'] = date('Y-m-d H:i:s',$order['createtime']);
	}
	if(!empty($order['shiptime'])){
		$order['shiptime'] = date('Y-m-d H:i:s',$order['shiptime']);
	}
	if(!empty($orderfukuan['createtime'])){
		$orderfukuan['createtime'] = date('Y-m-d H:i:s',$orderfukuan['createtime']);
	}
	if(!empty($orderjiesuan['createtime'])){
		$orderjiesuan['createtime'] = date('Y-m-d H:i:s',$orderjiesuan['createtime']);
	}
// 	var_dump('<pre>',$orderfukuan);die;
	include template('manage_order_detail');
}
