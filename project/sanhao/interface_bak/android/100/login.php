<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

$mobile = isset($arr['user_mobile']) ? addslashes(strval(trim($arr['user_mobile']))) : '';
$password = isset($arr['user_password']) ? addslashes(trim(ZUser::GenPassword(trim($arr['user_password'])))) : "";

if(!empty($mobile) && !empty($password))
{
	$user = DB::GetTableRow('jx_users', array( "mobile='".$mobile."'"));
	if(!$user)
	{
		$array['ret'] = 102;
		$array['msg'] = "该手机号码未注册";
		echo json_encode($array);exit;
	}
	else 
	{
		$login = DB::GetTableRow('jx_users', array( "mobile='".$mobile."'",'password'=>$password));
		if(!$login)
		{
			$array['ret'] = 103;
			$array['msg'] = "手机号码或密码错误";
			echo json_encode($array);exit;
		}
		else 
		{
			$array['ret'] = 100;
			$array['msg'] = "登录成功";
			$array['user_id'] = $login['id'];
			$array['user_mobile'] = $login['mobile'];
			$array['user_nickname'] = $login['nickname'];
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