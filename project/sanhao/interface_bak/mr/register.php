<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');
$post = file_get_contents('php://input');

$arr = getparameter($_POST, 'register');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//手机号
$mobile = isset($arr['merchant_mobile']) ? addslashes(strval(trim($arr['merchant_mobile']))) : '';
//密码
$password = isset($arr['merchant_password']) ? addslashes(strval(trim($arr['merchant_password']))) : '';
//验证码
$code = isset($arr['code']) ? intval(trim($arr['code'])) : "";
//商户姓名
$name = isset($arr['merchant_name']) ? addslashes(strval(trim($arr['merchant_name']))) : "";
//身份证号
$identity = isset($arr['merchant_identity']) ? addslashes(strval(trim($arr['merchant_identity']))) : "";
//身份证正面照片
$front_photo = isset($arr['front_photo']) ? addslashes(strval(trim($arr['front_photo']))) : "";
//身份证背面照片
$back_photo = isset($arr['back_photo']) ? addslashes(strval(trim($arr['back_photo']))) : "";
//身份证与本人合照照片
$photo = isset($arr['photo']) ? addslashes(strval(trim($arr['photo']))) : "";
//设备编号
$device_number = isset($arr['device_number']) ? addslashes(strval(trim($arr['device_number']))) : "";
//开户银行
$bank = isset($arr['bank']) ? addslashes(strval(trim($arr['bank']))) : "";
//银行卡号
$card_number = isset($arr['card_number']) ? addslashes(strval(trim($arr['card_number']))) : "";
//取现密码
$card_password = isset($arr['card_password']) ? addslashes(strval(trim($arr['card_password']))) : "";

if(!empty($mobile) && !empty($password) && !empty($code) && !empty($name) && !empty($identity) && !empty($front_photo) && !empty($back_photo) && !empty($photo) && !empty($device_number) && !empty($bank) && !empty($card_number) && !empty($card_password))
{
	//判断短信验证码是否正确
	$condition = array("mobile='".$mobile."' and type='register'");
	$aField = DB::LimitQuery('jx_smscodes', array(
		'condition' => $condition,
		'one'=>true,
		'order' => 'ORDER BY id DESC',
	));
	//如果短信验证码正确，判断发送时间是否大于30分钟
	if($aField['code'] == $code)
	{
		if(time()-$aField['addtime'] <= 30*60)
		{
			//判断身份证号的格式是否正确
			if(preg_match("/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/",$identity) || preg_match("/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/",$identity))
			{
				$u['mobile'] = $mobile;
				$u['password'] = ZUser::GenPassword($password);
				$u['merchant_name'] = $name;
				$u['identity'] = $identity;
				$u['front_photo'] = $front_photo;
				$u['back_photo'] = $back_photo;
				$u['photo'] = $photo;
				$u['bank'] = $bank;
				$u['card_number'] = $card_number;
				$u['card_password'] = $card_password;
				$u['device_number'] = $device_number;
				$u['createtime'] = time();
				$u['id'] = DB::Insert('mr_merchants', $u);
				if($u['id'])
				{
					$array['ret'] = 100;
					$array['msg'] = "注册成功";
					$array['user_id'] = $u['id'];
					$array['user_mobile'] = $mobile;
					echo json_encode($array);exit;
				}
				else 
				{
					$array['ret'] = 105;
					$array['msg'] = "注册失败";
					echo json_encode($array);exit;
				}
			}
			else 
			{
				$array['ret'] = 104;
				$array['msg'] = "身份证号格式不正确";
				echo json_encode($array);exit;
			}
		}
		else 
		{
			$array['ret'] = 103;
			$array['msg'] = "短信验证码的有效期为30分钟";
			echo json_encode($array);exit;
		}
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "短信验证码错误";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}


