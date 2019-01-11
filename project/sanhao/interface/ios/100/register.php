<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

// $_POST = array('mobile'=>'15210968777','name'=>'coco','code'=>'6619','password'=>'123456');
$arr = getparameter($_POST, 'register');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}
// var_dump('<pre>',$arr);die;
//手机号
$mobile = isset($arr['mobile']) ? addslashes(strval(trim($arr['mobile']))) : '';
//密码
$password = isset($arr['password']) ? addslashes(strval(trim($arr['password']))) : '';
//商户姓名
$name = isset($arr['name']) ? addslashes(strval(trim($arr['name']))) : "";
$code = isset($arr['code']) ? addslashes(trim($arr['code'])) : '';

if(!preg_match("/^(1(([358][0-9])|(47)))\d{8}$/",$mobile))
{
	$array['ret'] = 103;
	$array['msg'] = "手机号码格式不正确";
	echo json_encode($array);exit;
}
$jx_mobile = DB::GetTableRow('jx_users', array( "mobile='".$mobile."'"));
if($jx_mobile)
{
	$array['ret'] = 104;
	$array['msg'] = "该手机号已注册";
	echo json_encode($array);exit;
}
$newpassword = strval(ZUser::GenPassword($password));
if(!empty($mobile) && !empty($password) && !empty($name)  && !empty($code))
{
	$condition = array("mobile='".$mobile."' and ret=0 and type='register' and comefrom=2");
	$aField = DB::LimitQuery('jx_smscodes', array(
			'condition' => $condition,
			'one'=>true,
			'order' => 'ORDER BY id DESC',
	));
	//如果短信验证码正确，判断发送时间是否大于30分钟
	if($aField['code'] == $code)
	{
		if(time()-$aField['addtime'] <= 12*60*60)
		{
			$u['mobile'] = $mobile;
			$u['password'] = $newpassword;
			$u['nickname'] = $name;
			$u['createtime'] = time();
			$u['type'] = 2;
			$u['id'] = DB::Insert('jx_users', $u);
			if($u['id'])
			{
				$m['uid'] = $u['id'];
				$m['type'] = 1;
				$m['is_read'] = 0;
				$m['content'] = "Hi，恭喜你成功开通交享团帐号，在这里你可以随心随意卖东西，还可以淘到超划算的东东哦！一切都可以在交享团搞定，还在等什么，走起~~~";
				$m['created'] = time();
				$m['id'] = DB::Insert('jx_messages', $m);
				
				$array['ret'] = 100;
				$array['msg'] = "注册成功";
				$array['user_id'] = $u['id'];
				$array['user_mobile'] = $mobile;
				$array['user_name'] = $name;
				$array['icardpay_payno'] = '';
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
			$array['ret'] = 106;
			$array['msg'] = "短信验证码的有效期为12小时";
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