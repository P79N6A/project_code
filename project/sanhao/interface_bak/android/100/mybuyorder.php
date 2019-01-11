<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$now = time();
$uid = isset($arr['uid']) ? intval(trim($arr['uid'])) : "";
$begintime = isset($arr['begintime']) ? strtotime(trim($arr['begintime'])) : $now;
$type = isset($arr['type']) ? strval(trim($arr['type'])) : "more";

if(!empty($uid))
{
	if($type == "more")
	{
		$condition = array('uid'=>$uid,"state = 'unpay' or state = 'pay' or state = 'complete'","createtime <= $begintime");
	}
	else 
	{
		$condition = array('uid'=>$uid,"state = 'unpay' or state = 'pay' or state = 'complete'","createtime > $begintime");
	}
	$aorderlist = DB::LimitQuery('jx_orders', array(
		'condition' => $condition,
		'order'=>'ORDER BY createtime DESC',
		'size' => 20,
	));
	if(!empty($aorderlist))
	{
		$alist = array();
		foreach ($aorderlist as $key=>$value)
		{
			$alist[$key]['order_oid'] = $value['id'];
			$alist[$key]['order_pay_id'] = $value['pay_id'];
			$alist[$key]['order_begintime'] = $value['createtime'];
			$alist[$key]['order_state'] = $value['state'];
			$product = Table::Fetch('jx_products', $value['pid']);
			$alist[$key]['product_pname'] = $product['pname'];
			$alist[$key]['product_price'] = $product['price'];
			$cimage = array('pid'=>$value['pid'],'type'=>1);
			$aField = DB::LimitQuery('jx_products_image',array(
					'condition'=>$cimage,
					'one'=>true
			));
			if(!empty($aField))
			{
				$aorderlist[$key]['product_purl'] = $aField['image'];
			}
			else 
			{
				$aorderlist[$key]['product_purl'] = '';
			}
			$array = array(
				'ret' => 100,
				'msg' => '商品订单列表',
				'list' => $alist
			);
			echo json_encode($array);exit;
		}
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "无商品订单数据";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}