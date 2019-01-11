<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
need_checksns();
$nav = 'order';
$action = isset($_GET['action']) ? $_GET['action'] : 'sale';

if($action == 'sale')
{	
	$condition = array('sid'=>$login_user_id,"state = 'pay' or state = 'complete'");
	$count = Table::Count('jx_orders', $condition);
	if($count > 0)
	{
		list($pagesize, $offset, $pagestring) = pagestring($count, 20);
		$aorderlist = DB::LimitQuery('jx_orders', array(
				'condition' => $condition,
				'order'=>'ORDER BY id DESC',
				'size' => $pagesize,
				'offset' => $offset,
		));
		foreach ($aorderlist as $key=>$value)
		{
			$product = Table::Fetch('jx_products', $value['pid']);
			$aorderlist[$key]['productname'] = $product['pname'];
			$cimage = array('pid'=>$value['pid'],'type'=>1);
			$aField = DB::LimitQuery('jx_products_image',array(
					'condition'=>$cimage,
					'one'=>true
			));
			if(!empty($aField))
			{
				$aorderlist[$key]['image'] = $aField['image'];
			}
			else 
			{
				$aorderlist[$key]['image'] = '';
			}
			//获取购买者信息
			$user = Table::Fetch('jx_users', $value['uid']);
			if(!empty($user['nickname']))
			{
				$aorderlist[$key]['buyer'] = $user['nickname'];
			}
			else 
			{
				if($user['type'] == 1)
				{
					$aorderlist[$key]['buyer'] = $user['email'];
				}
				else 
				{
					$aorderlist[$key]['buyer'] = $user['mobile'];
				}
			}
			if($value['paytype'] == 'jxk'){
				$aorderlist[$key]['tatolmoney'] = $value['origin']+$value['charge'];
			}else{
				$aorderlist[$key]['tatolmoney'] = $value['origin'];
			}
		}
	}
	$pagetitle = '我的订单-出售';
	include template('order_index');
}
else if($action == 'buy')
{
	$condition = array('uid'=>$login_user_id,"state = 'unpay' or state = 'pay' or state = 'complete'");
	$count = Table::Count('jx_orders', $condition);
	if($count > 0)
	{
		list($pagesize, $offset, $pagestring) = pagestring($count, 20);
		$aorderlist = DB::LimitQuery('jx_orders', array(
				'condition' => $condition,
				'order'=>'ORDER BY id DESC',
				'size' => $pagesize,
				'offset' => $offset,
		));
		foreach ($aorderlist as $key=>$value)
		{
			$product = Table::Fetch('jx_products', $value['pid']);
			$aorderlist[$key]['productname'] = $product['pname'];
			$cimage = array('pid'=>$value['pid'],'type'=>1);
			$aField = DB::LimitQuery('jx_products_image',array(
					'condition'=>$cimage,
					'one'=>true
			));
			if(!empty($aField))
			{
				$aorderlist[$key]['image'] = $aField['image'];
			}
			else 
			{
				$aorderlist[$key]['image'] = '';
			}
			//获取卖家信息
			$user = Table::Fetch('jx_users', $value['sid']);
			if(!empty($user['nickname']))
			{
				$aorderlist[$key]['saler'] = $user['nickname'];
			}
			else 
			{
				if($user['type'] == 1)
				{
					$aorderlist[$key]['saler'] = $user['email'];
				}
				else 
				{
					$aorderlist[$key]['saler'] = $user['mobile'];
				}
			}
			if($value['paytype'] == 'jxk'){
				$aorderlist[$key]['tatolmoney'] = $value['origin']+$value['charge'];
			}else{
				$aorderlist[$key]['tatolmoney'] = $value['origin'];
			}
			$aorderlist[$key]['now'] = time();
			$aorderlist[$key]['diff_time'] = $aorderlist[$key]['left_time'] = ($value['createtime'] + 60*60)-$aorderlist[$key]['now'];
			$aorderlist[$key]['left_time'] = $aorderlist[$key]['left_time'] % 3600;
			$aorderlist[$key]['seconds'] = floor($aorderlist[$key]['left_time']/60);
			$aorderlist[$key]['time'] = $aorderlist[$key]['left_time'] % 60;
		}
	}
	$pagetitle = '我的订单-购买';
	include template('order_my_buy');
}
