<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

// $_POST = array('user_id'=>11,'requesttime'=>'1384599527','type'=>'more');
$arr = getparameter($_POST, 'getproductlist');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//商户ID
$user_id = isset($arr['user_id']) ? addslashes(strval(trim($arr['user_id']))) : "";
//如果不传时间则默认调用当前的时间
$now = time();
if(!empty($arr['requesttime']))
{
	$begintime = trim($arr['requesttime']);
}
else 
{
	$begintime = $now;
}
//滑动方式，如果不传值，默认为more,往下拉，刷新refresh
$type = isset($arr['type']) ? strval(trim($arr['type'])) : "more";

if(!empty($begintime) && !empty($type) )
{
	if($type == 'more')
	{
		$condition[] = "createtime < $begintime";
		$condition[] = "( status=1 OR status=3 ) AND uid=$user_id";
	}
	else 
	{
		$condition[] = "createtime > $begintime";
		$condition[] = "( status=1 OR status=3 ) AND  uid=$user_id";
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
			//获取商品的图片
			$cimage = array('pid'=>$value['id'],'type'=>1);
			$aField = DB::LimitQuery('jx_products_image',array(
					'condition'=>$cimage,
					'one'=>true
			));
			$alist[$key]['product_url'] = $INI['system']['imgprefix'].'/'.str_replace('product/big', 'product/small', $aField['image']);
			$alist[$key]['product_id'] = $value['id'];
			$alist[$key]['product_name'] = $value['pname'];
			$alist[$key]['product_price'] = $value['price'];
			if(!empty($value['description']))
			{
				$alist[$key]['product_desc'] = $value['description'];
			}
			else 
			{
				$alist[$key]['product_desc'] = '';
			}
			
			$alist[$key]['product_sale_number'] = $value['sale_number'];
			$alist[$key]['product_create_time'] = $value['createtime'];
			$alist[$key]['product_end_time'] = $value['end_time'];
			if($value['status'] == 1){
				$alist[$key]['product_status'] = "normal";
			}else if($value['status'] == 3){
				$alist[$key]['product_status'] = "under";
			}else if($value['status'] == 4){
				$alist[$key]['product_status'] = "complete";
			}else{
				$alist[$key]['product_status'] = "del";
			}
		}
		$array = array(
			'ret' => 100,
			'msg' => '商品列表',
			'product_list' => $alist
		);
// 		var_dump('<pre>',$alist);die;
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