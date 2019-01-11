<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

$now = time();
$uid = isset($arr['uid']) ? intval(trim($arr['uid'])) : "";
$begintime = isset($arr['begintime']) ? strtotime(trim($arr['begintime'])) : $now;
$type = isset($arr['type']) ? strval(trim($arr['type'])) : "more";

if(!empty($uid))
{
	if($type == "more")
	{
		$condition = array( 'uid' => $uid, "createtime <= $begintime");
	}
	else 
	{
		$condition = array( 'uid' => $uid, "createtime > $begintime");
	}
	$product = DB::LimitQuery('jx_products', array(
		'condition' => $condition,
		'order' => 'ORDER BY createtime DESC',
		'size' => 20,
	));
	if(!empty($product))
	{
		$alist = array();
		foreach ($product as $key=>$value)
		{
			$cimage = array('pid'=>$value['id'],'type'=>1);
			$aField = DB::LimitQuery('jx_products_image',array(
			'condition'=>$cimage,
			'one'=>true
			));
			if(!empty($aField))
			{
				$alist[$key]['product_purl'] = $aField['image'];
			}
			else 
			{
				$alist[$key]['product_purl'] = '';
			}
			$alist[$key]['product_pid'] = $value['id'];
			$alist[$key]['product_pname'] = $value['pname'];
			$alist[$key]['product_price'] = $value['price'];
			$alist[$key]['product_end_time'] = $value['end_time'];
			$alist[$key]['product_max_number'] = $value['max_number'];
			$alist[$key]['product_sale_number'] = $value['sale_number'];
			$alist[$key]['product_begintime'] = $value['createtime'];
			//判断商品的状态
			if($value['end_time'] != '' && $value['max_number'] != '')
			{
				//商品已下架
				if(($value['end_time'] < $now) || ($value['max_number']-$value['sale_number'] <= 0))
				{
					$alist[$key]['product_pstatus'] = "under";
				}
				else 
				{
					$alist[$key]['product_pstatus'] = "normal";
				}
			}
			else if($value['end_time'] != '' && $value['max_number'] == '')
			{
				//商品已下架
				if($value['end_time'] < $now)
				{
					$alist[$key]['product_pstatus'] = "under";
				}
				else 
				{
					$alist[$key]['product_pstatus'] = "normal";
				}
			}
			else if($value['end_time'] == '' && $value['max_number'] != '')
			{
				if($value['max_number']-$value['sale_number'] <= 0)
				{
					$alist[$key]['product_pstatus'] = "under";
				}
				else 
				{
					$alist[$key]['product_pstatus'] = "normal";
				}
			}
			else 
			{
				$alist[$key]['product_pstatus'] = "normal";
			}
			$array = array(
				'ret' => 100,
				'msg' => '商品列表',
				'list' => $alist
			);
			echo json_encode($array);exit;
		}
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "无商品数据";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}