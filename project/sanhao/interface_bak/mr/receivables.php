<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

//商户ID
$mid = isset($arr['mid']) ? intval(trim($arr['mid'])) : "";
//消费金额
$origin = isset($arr['origin']) ? addslashes(strval(trim($arr['origin']))) : "";
//手机号码
$mobile = isset($arr['mobile']) ? addslashes(strval(trim($arr['mobile']))) : "";

if(!empty($mid) && !empty($origin))
{
	//先生成一个虚拟的商品
	$u['mid'] = $mid;
	$u['name'] = "快速消费品".rand(100000,999999);
	$u['price'] = $origin;
	$u['createtime'] = time();
	$u['id'] = DB::Insert('mr_products', $u);
	if($u['id'])
	{
		//生成一个订单
		$uarray['pay_id'] = date('YmdHis').rand(1000, 9999);
		$uarray['mid'] = $mid;
		$uarray['sid'] = $mid;
		$uarray['pid'] = $u['id'];
		$uarray['quantity'] = 1;
		$uarray['price'] = $origin;
		$uarray['origin'] = $origin;
		$uarray['createtime'] = time();
		$uarray['id'] = DB::Insert('mr_orders', $uarray);
		if($uarray['id'])
		{
			//查询登录用户的个人信息
			$user = DB::GetTableRow('mr_merchants', array( "id=$mid"));
			//支付
			$app_key = '';
			$version = '1.0';
			$service_type = 'icardpay.mr.pos.order.create';
			$terminal_id = $user['device_number'];
			$req_id = $uarray['pay_id'];
			$ord_amt = $origin;
			$mobile = $mobile;
			$desc = '';
			$ret_url = '';
			$callback_url = '';
			$goods_name = $u['name'];
			//系统分配的密匙
			$key = "";
			//签名
			$sign = MD5($app_key.$callback_url.$desc.$goods_name.$mobile.$ord_amt.$req_id.$ret_url.$service_type.$terminal_id.$version.$key);
			$url = "http://bao.icardpay.com/mr_pos/gateway.rest";
			$data = 'app_key='.$app_key.'&callback_url='.$callback_url.'&desc='.$desc.'&goods_name='.$goods_name.'&mobile='.$mobile.'&ord_amt='.$ord_amt.'&req_id='.$req_id.'&ret_url='.$ret_url.'&service_type='.$service_type.'&terminal_id='.$terminal_id.'&version='.$version.'&sign='.$sign;
			$ret = json_decode(interface_post($url, $data)); 
			if($ret['status'] == '0000')
			{
				$array['ret'] = 100;
				$array['msg'] = "支付成功";
				echo json_encode($array);exit;
			}
			else 
			{
				$array['ret'] = 104;
				$array['msg'] = "支付失败";
				echo json_encode($array);exit;
			}
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