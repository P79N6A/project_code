<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');
// $_POST = array('user_id'=>12,'icardpay_payno'=>15689859632);
$arr = getparameter($_POST, 'addbinding');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}
//支付通帐号
$icardpay_payno = isset($arr['icardpay_payno']) ? addslashes(strval(trim($arr['icardpay_payno']))) : '';
//用户id
$user_id = isset($arr['user_id']) ? intval(trim($arr['user_id'])) : "";
if(!empty($user_id) && !empty($icardpay_payno))
{
	$users = DB::LimitQuery('jx_users', array(
			'condition' => array('id' => $user_id),
			'one'=>true
	));
	$binding = DB::LimitQuery('jx_bindings', array(
			'condition' => array("mobile = '".$users['mobile']."'"),
			'one'=>true
	));
	if($binding){
		$array['ret'] = 104;
		$array['msg'] = "该帐号已绑定支付通帐号";
		echo json_encode($array);exit;
	}else{
		if(isset($users['mobile']) && !empty($users['mobile']))
		{
			$u['mobile'] = $users['mobile'];
			$u['payno'] = $icardpay_payno;
			$u['createtime'] = time();
			$u['id'] = DB::Insert('jx_bindings', $u);
			if($u['id'])
			{
				$array['ret'] = 100;
				$array['msg'] = "绑定是支付通帐号成功";
				echo json_encode($array);exit;
			}
			else
			{
				$array['ret'] = 102;
				$array['msg'] = "绑定是支付通帐号失败";
				echo json_encode($array);exit;
			}
		}else{
			$array['ret'] = 103;
			$array['msg'] = "个人信息不全，请先完善个人资料";
			echo json_encode($array);exit;
		}
	}
	
	
	
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}