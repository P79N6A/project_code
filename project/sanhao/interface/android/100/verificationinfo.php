<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'verificationinfo');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//手机号
$mobile = isset($arr['merchant_mobile']) ? addslashes(strval(trim($arr['merchant_mobile']))) : '';
//密码
$password = isset($arr['merchant_password']) ? addslashes(strval(trim($arr['merchant_password']))) : '';

if(!empty($mobile) && !empty($password))
{
	$user = DB::GetTableRow('mr_merchants', array( "mobile='".$mobile."'"));
	if(!$user)
	{
		$array['ret'] = 102;
		$array['msg'] = "该手机号码未注册";
		echo json_encode($array);exit;
	}
	else 
	{
		$login = DB::GetTableRow('mr_merchants', array( "mobile='".$mobile."'",'password'=>$password));
		if(!$login)
		{
			$array['ret'] = 103;
			$array['msg'] = "手机号码或密码错误";
			echo json_encode($array);exit;
		}
		else 
		{
			if($login['status'] == 'n')
			{
				$array['ret'] = 104;
				$array['msg'] = "您的账号出现异常,可能原因:".$login['msg'];
				$array['merchant_id'] = $login['id'];
				$array['merchant_mobile'] = $login['mobile'];
				$array['merchant_password'] = $login['password'];
				$array['merchant_name'] = $login['merchant_name'];
				$array['merchant_identity'] = $login['identity'];
				$array['merchant_front_photo'] = $INI['system']['imgprefix'].'/'.$login['front_photo'];
				$array['merchant_back_photo'] = $INI['system']['imgprefix'].'/'.$login['back_photo'];
				$array['merchant_photo'] = $INI['system']['imgprefix'].'/'.$login['photo'];
				$array['merchant_province_id'] = $login['province_id'];
				$array['merchant_city_id'] = $login['city_id'];
				$array['merchant_area_id'] = $login['area_id'];
				$array['merchant_address'] = $login['address'];
				$array['merchant_bank'] = $login['bank'];
				$array['merchant_card_number'] = $login['card_number'];
				$array['merchant_card_password'] = $login['card_password'];
				$array['merchant_device_number'] = $login['device_number'];
				echo json_encode($array);exit;
			}
			else 
			{
				$array['ret'] = 100;
				$array['msg'] = "登录成功";
				$array['merchant_id'] = $login['id'];
				$array['merchant_mobile'] = $login['mobile'];
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
