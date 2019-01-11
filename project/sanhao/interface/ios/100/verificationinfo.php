<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');
// $_POST = array('mobile'=>13411112222,'password'=>'e7fe8b88db51d86ef2f5e169144b9c1b');
$arr = getparameter($_POST, 'verificationinfo');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}
//手机号
$mobile = isset($arr['mobile']) ? addslashes(strval(trim($arr['mobile']))) : '';
//密码
$password = isset($arr['password']) ? addslashes(strval(trim($arr['password']))) : '';

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
		$login = DB::GetTableRow('jx_users', array( "mobile='".$mobile."'","password='".$password."'"));
		if(!$login)
		{
			$array['ret'] = 103;
			$array['msg'] = "手机号码或密码错误";
			echo json_encode($array);exit;
		}
		else 
		{
			if($login['status'] == 0)
			{
				$array['ret'] = 104;
				$array['msg'] = "您的账号出现异常,可能原因:禁用";
				$array['user_id'] = $login['id'];
				$array['user_mobile'] = $login['mobile'];
				$array['user_password'] = $login['password'];
				$array['user_nick'] = $login['nickname'];
				$array['user_url'] = $login['headerurl'];	
				$array['user_email'] = $login['email'];
				$array['user_desc'] = $login['description'];
			
				echo json_encode($array);exit;
			}
			else 
			{
				$binding = DB::LimitQuery('jx_bindings', array(
						'condition' => array('mobile' => $mobile),
						'one'=>true
				));
				$array['ret'] = 100;
				$array['msg'] = "登录成功";
				$array['user_id'] = $login['id'];
				$array['user_mobile'] = $login['mobile'];
				$array['user_name'] = $login['nickname'];
				$array['user_email'] = $login['email'];
				$array['user_desc'] = $login['description'];
				$array['user_url'] = !empty($login['headerurl'])?$INI['system']['imgprefix'].'/'.$login['headerurl']:$INI['system']['imgprefix'].'/'.'static/images/100.png';
				
				$array['icardpay_payno'] = isset($binding['payno'])?$binding['payno']:'';
				$snslogin = DB::LimitQuery('jx_users_sns', array(
						'condition' => array('uid' => $login['id']),
						'one'=>true
				));
				if(!empty($snslogin)){
					$array['sns_id'] = $snslogin['sns_id'];
					$array['sns_token'] = $snslogin['sns_token'];
					$array['sns_expirationtime'] = $snslogin['sns_expirationtime'];
				}
// 				var_dump('<pre>',$array);die;
				echo json_encode($array);exit;
			}
		}
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}
