<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');
// $_POST = array('user_id'=>12,'icardpay_payno'=>15689859632);
$arr = getparameter($_POST, 'addbinding');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}
$mobile = isset($arr['mobile']) ? addslashes(strval(trim($arr['mobile']))) : '';
//密码
$password = isset($arr['password']) ? addslashes(strval(trim($arr['password']))) : '';
$type = isset($arr['type']) ? addslashes(strval(trim($arr['type']))) : '';
$newpassword = strval(ZUser::GenPassword($password));
//用户id
$user_id = isset($arr['user_id']) ? intval(trim($arr['user_id'])) : "";
$sns_id = isset($arr['sns_id']) ? intval(trim($arr['sns_id'])) : "";
$code = isset($arr['code']) ? intval(trim($arr['code'])) : "";
if(!empty($mobile)){
	$condition = array("mobile='".$mobile."' and ret=0 and type='nobinding' and comefrom=2");
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
			if($type == 'binding'){
				$user = DB::GetTableRow('jx_users', array( "mobile='".$mobile."'"));
				$snsinfo  = DB::GetTableRow('jx_users_sns', array( "sns_id='".$sns_id."'"));
				$table = new Table('jx_users_sns', $_POST);
				$table->pk_value = $snsinfo['id'];
				$table->uid = $user['id'];
				$up_array = array('uid');
				$flag = $table->update( $up_array );
				if($flag)
				{
					$array['ret'] = 100;
					$array['msg'] = "修改用户信息成功";
					$array['user_id'] = $user['id'];
					$array['mobile'] = $mobile;
					echo json_encode($array);exit;
				}
				else
				{
					$array['ret'] = 105;
					$array['msg'] = "修改用户信息失败";
					echo json_encode($array);exit;
				}
			}else if($type == 'add'){
				$snsinfo  = DB::GetTableRow('jx_users_sns', array( "sns_id='".$sns_id."'"));
				$u['mobile'] = $mobile;
				$u['password'] = $newpassword;
				$u['nickname'] = $snsinfo['sns_nickname'];
				$u['headerurl'] = $snsinfo['sns_headerurl'];
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
					
					$table = new Table('jx_users_sns', $_POST);
					$table->pk_value = $snsinfo['id'];
					$table->uid = $u['id'];
					$up_array = array('uid');
					$flag = $table->update( $up_array );
					if($flag)
					{
						$array['ret'] = 100;
						$array['msg'] = "修改用户信息成功";
						$array['user_id'] = $u['id'];
						$array['mobile'] = $mobile;
						echo json_encode($array);exit;
					}
					else
					{
						$array['ret'] = 106;
						$array['msg'] = "修改用户信息失败";
						echo json_encode($array);exit;
					}	
					
					$array['ret'] = 100;
					$array['msg'] = "注册成功";
					$array['user_id'] = $u['id'];
					$array['user_mobile'] = $mobile;
					$array['icardpay_payno'] = '';
					echo json_encode($array);exit;
				}
				else
				{
					$array['ret'] = 103;
					$array['msg'] = "注册失败";
					echo json_encode($array);exit;
				}
			}
		}
		else
		{
			$array['ret'] = 104;
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