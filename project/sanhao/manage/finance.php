<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(dirname(__FILE__)) . '/include/function/right.php');

need_manager();
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "list";
$menucolor = '三好网财务记录';
if($action == "list")
{
	//订单号
	$order_id = isset($_REQUEST['pay_id']) ? $_REQUEST['pay_id'] : "";
	//交易类型
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
	//金额最小值
	$smallamount = isset($_REQUEST['smallamount']) ? $_REQUEST['smallamount'] : "";
	//金额最大值
	$bigamount = isset($_REQUEST['bigamount']) ? $_REQUEST['bigamount'] : "";
	//来源
	$source = isset($_REQUEST['source']) ? $_REQUEST['source'] : "";
	//去向
	$whereabouts = isset($_REQUEST['whereabouts']) ? $_REQUEST['whereabouts'] : "";
	//起始时间
	$begintime = isset($_REQUEST['begintime']) ? $_REQUEST['begintime'] : "";
	$newbegintime = isset($_REQUEST['begintime']) ? $_REQUEST['begintime'] : "";
	//终止时间
	$endtime = isset($_REQUEST['endtime']) ? strtotime($_REQUEST['endtime']) : "";
	$newendtime = isset($_REQUEST['endtime']) ? $_REQUEST['endtime'] : "";
	
	$condition = array('id > 0');
	
	if(!empty($order_id))
	{
		$condition[] = "pay_id like '%".$order_id."%'";
	}
	if(!empty($type) && $type != 'all')
	{
		$condition[] = "type = '$type'";
	}
	if(!empty($smallamount))
	{
		$condition[] = "transfer_amount >= $smallamount or amount_transferred >= $smallamount";
	}
	if(!empty($bigamount))
	{
		$condition[] = "transfer_amount <= $bigamount or amount_transferred <= $bigamount";
	}
	if(!empty($source))
	{
		$condition[] = "source like '%".$source."%'";
	}
	if(!empty($whereabouts))
	{
		$condition[] = "whereabouts like '%".$whereabouts."%'";
	}
	if(!empty($begintime))
	{
		$condition[] = "createtime >= $begintime";
	}
	if(!empty($endtime))
	{
		$condition[] = "createtime <= $endtime";
	}
	$count = Table::Count('jx_financial_records', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	
	$financelist = DB::LimitQuery('jx_financial_records', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	foreach($financelist as $fkey => $fval){
		if(!empty($fval['createtime'])){
			$financelist[$fkey]['createtime'] = date('Y-m-d H:i:s',$fval['createtime']);
		}
	}
	//查询订单总数
	$sqltotalorder = "select distinct(pay_id) as count from `jx_financial_records` where id>0";
	$row = DB::GetQueryResult($sqltotalorder, true);
	$countTotalOrder = intval($row['count']);
	//查询已结算订单数
	$conditionyijiesuan = array('type'=>'settle');
	$countorderyijiesuan = Table::Count('jx_financial_records', $conditionyijiesuan);
	//统计转入余额数
	$conditionzhuanru = array('type'=>'pay');
	$countzhuanru = Table::Count('jx_financial_records', $conditionzhuanru, 'transfer_amount');
	//统计转出金额数
	$countzhuanchu = Table::Count('jx_financial_records', $conditionyijiesuan, 'amount_transferred');
	
	if ( strval($_GET['download'])) 
	{
		$name = "order_{$state}_".date('Ymd');
		$kn = array(
			'pay_id' => '订单编号',
			'createtime' => '交易时间',
			'transfer_amount' => '转入金额',
			'amount_transferred' => '转出金额',
			'source' => '来源',
			'whereabouts' => '去向',
		);
		foreach($financelist AS $cid => $one) {
			$one['id'] = $one['id'];
			$one['productname'] = moneyit($one['value']);
	        $cards[$cid] = $one;
		}
		down_xls($cards, $kn, $name);
	}
	//统计
	$countpaysum = Table::Count('jx_financial_records', array(array("type = 'pay'")),'transfer_amount');
	$countsettlesum = Table::Count('jx_financial_records', array(array("type = 'settle'")),'amount_transferred');
	$financeleftcolor = 1;
	include template('manage_finance_index');
}
