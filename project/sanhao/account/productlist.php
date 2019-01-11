<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
$condition = array( 'uid' => $login_user_id);
$now = time();
$nav = 'list';

$count = Table::Count('jx_products', $condition);
if($count > 0)
{
	list($pagesize, $offset, $pagestring) = pagestring($count, 10);
	$product = DB::LimitQuery('jx_products', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	foreach ($product as $key=>$value)
	{
		$cimage = array('pid'=>$value['id'],'type'=>1);
		$aField = DB::LimitQuery('jx_products_image',array(
		'condition'=>$cimage,
		'one'=>true
		));
		$product[$key]['info'] = subtostring($product[$key]['description'], 100);
		if(!empty($aField))
		{
			$product[$key]['image'] = $aField['image'];
		}
		else 
		{
			$product[$key]['image'] = '';
		}
		$corder = array("pid = '".$value['id']."' and (state = 'pay' or state = 'complete')");
		$order = DB::LimitQuery('jx_orders',array(
		'condition'=>$corder,
		));
		$totalorigin = '';
		foreach ($order as $k=>$v)
		{
			$totalorigin += $v['price']*$v['quantity'];
		}
		$product[$key]['totalorigin'] = $totalorigin;
	}
}

$pagetitle = '商品列表页';
include template('account_productlist');
