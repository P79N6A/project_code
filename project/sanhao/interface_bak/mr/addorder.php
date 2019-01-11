<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

$post = file_get_contents('php://input');
$arr = getparameter($post, 'addorder');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//商户ID
$mid = isset($arr['mid']) ? intval(trim($arr['mid'])) : "";
//商品名称
$name = isset($arr['product_name']) ? addslashes(strval(trim($arr['product_name']))) : "";
//消费金额
$origin = isset($arr['origin']) ? addslashes(strval(trim($arr['origin']))) : "";
//手机号码
$mobile = isset($arr['mobile']) ? addslashes(strval(trim($arr['mobile']))) : "";
//订单编号
$pay_id = isset($arr['pay_id']) ? addslashes(strval(trim($arr['pay_id']))) : "";
//订单生成时间
$createtime = isset($arr['createtime']) ? strtotime(trim($arr['createtime'])) : "";

if(!empty($mid) && !empty($name) && !empty($origin) && !empty($mobile) && !empty($pay_id) && !empty($createtime))
{
	//先生成一个商品信息
	$u['mid'] = $mid;
	$u['name'] = $name;
	$u['price'] = $origin;
	$u['createtime'] = time();
	$u['id'] = DB::Insert('mr_products', $u);
	if($u['id'])
	{
		//获取买家的ID
		$user = DB::GetTableRow('mr_merchants', array( "id=$mid"));
		//如果改用户存在
		if(!empty($user))
		{
			$buyer_id = $user['id'];
		}
		else 
		{
			//随机取一个用户的手机号
			
			
		}
		//生成一个订单
		$uarray['pay_id'] = $pay_id;
		$uarray['mid'] = $buyer_id;
		$uarray['sid'] = $mid;
		$uarray['pid'] = $u['id'];
		$uarray['quantity'] = 1;
		$uarray['price'] = $origin;
		$uarray['origin'] = $origin;
		$uarray['createtime'] = time();
		$uarray['id'] = DB::Insert('mr_orders', $uarray);
		if($uarray['id'])
		{
			$array['ret'] = 100;
			$array['msg'] = "生成订单成功";
			echo json_encode($array);exit;
		}
		else 
		{
			$array['ret'] = 103;
			$array['msg'] = "生成订单失败";
			echo json_encode($array);exit;
		}
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "生成虚拟商品失败";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}