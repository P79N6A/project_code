<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

//如果不传时间则默认调用当前的时间
$now = time();
$begintime = isset($arr['begintime']) ? strtotime(trim($arr['begintime'])) : $now;
//经度
$longitude = isset($arr['longitude']) ? intval(trim($arr['longitude'])) : "";
//纬度
$latitude = isset($arr['latitude']) ? intval(trim($arr['latitude'])) : "";
//查找范围,如果不传值，默认为查找所有商品
$scope = isset($arr['scope']) ? strval(trim($arr['scope'])) : "all";
//滑动方式，如果不传值，默认为more
$type = isset($arr['type']) ? strval(trim($arr['type'])) : "more";

if(!empty($begintime) && !empty($scope) && !empty($type))
{
	//设置查询范围
	if($scope == 'all')
	{
		if($type == 'more')
		{
			$condition = array('status'=>1, "end_time is NULL OR end_time > ".$begintime, "max_number is NULL or max_number > sale_number", "createtime <= $begintime");
		}
		else 
		{
			$condition = array('status'=>1, "end_time is NULL OR end_time > ".$begintime, "max_number is NULL or max_number > sale_number", "createtime > $begintime");
		}
	}
	else if($scope == 'near')
	{
		//先查询出所有含有经度和纬度的商品
		if($type == 'more')
		{
			$condition = array('status'=>1, "longitude is NULL and latitude is NULL", "end_time is NULL OR end_time > ".$begintime, "max_number is NULL or max_number > sale_number", "createtime <= $begintime");
		}
		else 
		{
			$condition = array('status'=>1, "longitude is NULL and latitude is NULL", "end_time is NULL OR end_time > ".$begintime, "max_number is NULL or max_number > sale_number", "createtime > $begintime");
		}
	}
	$aproductlist = DB::LimitQuery('jx_products', array(
			'condition' => $condition,
			'order'=>'ORDER BY createtime DESC',
			'size' => 20,
	));
	if(!empty($aproductlist))
	{
		$alist = array();
		foreach ($aproductlist as $key=>$value)
		{
			//如果查找附近，则判断附近商品的距离
			if($scope == 'near')
			{
				$alist[$key]['currinstance'] = getdistance($longitude, $latitude, $value['longitude'], $value['latitude']);
				if($alist[$key]['currinstance'] <= 5)
				{
					//获取商品的图片
					$cimage = array('pid'=>$value['id'],'type'=>1);
					$aField = DB::LimitQuery('jx_products_image',array(
							'condition'=>$cimage,
							'one'=>true
					));
					$alist[$key]['product_url'] = str_replace('product/big', 'product/small', $aField['image']);
					$alist[$key]['product_pid'] = $value['id'];
					$alist[$key]['product_price'] = $value['price'];
					$alist[$key]['product_begintime'] = $value['createtime'];
				}
				else 
				{
					unset($aproductlist[$key]);
				}
			}
			else 
			{
				//获取商品的图片
				$cimage = array('pid'=>$value['id'],'type'=>1);
				$aField = DB::LimitQuery('jx_products_image',array(
						'condition'=>$cimage,
						'one'=>true
				));
				$alist[$key]['product_url'] = str_replace('product/big', 'product/small', $aField['image']);
				$alist[$key]['product_pid'] = $value['id'];
				$alist[$key]['product_price'] = $value['price'];
				$alist[$key]['product_begintime'] = $value['createtime'];
			}
		}
		$array = array(
			'ret' => 100,
			'msg' => '商品列表',
			'list' => $alist
		);
		echo json_encode($array);exit;
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