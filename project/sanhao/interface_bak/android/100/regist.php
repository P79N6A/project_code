<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

$mobile = isset($arr['user_mobile']) ? addslashes(strval(trim($arr['user_mobile']))) : '';
$password = isset($arr['user_password']) ? addslashes(strval(trim($arr['user_password']))) : '';
$code = isset($arr['code']) ? intval(trim($arr['code'])) : "";
$nick = isset($arr['user_nickname']) ? addslashes(strval(trim($arr['user_nickname']))) : '';


if(!empty($mobile) && !empty($password) && !empty($code) && !empty($nick))
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
			//判断密码格式是否正确
			if(preg_match("/^(.){6,16}$/",$password))
			{
				//密码格式正确，将数据保存到数据表中
				$u['mobile'] = $mobile;
				$u['password'] = ZUser::GenPassword($password);
				$u['nickname'] = $nick;
				$u['createtime'] = time();
				$u['type'] = 2;
				$u['comefrom'] = 2;
				$u['id'] = DB::Insert('jx_users', $u);
				if($u['id'])
				{
					$array['ret'] = 100;
					$array['msg'] = "注册成功";
					$array['user_id'] = $u['id'];
					$array['user_mobile'] = $mobile;
					$array['user_nickname'] = $nick;
					echo json_encode($array);exit;
				}
				else 
				{
					$array['ret'] = 103;
					$array['msg'] = "注册失败";
					echo json_encode($array);exit;
				}
			}
			else 
			{
				$array['ret'] = 105;
				$array['msg'] = "密码格式不正确";
				echo json_encode($array);exit;
			}
		}
		else 
		{
			$array['ret'] = 104;
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
