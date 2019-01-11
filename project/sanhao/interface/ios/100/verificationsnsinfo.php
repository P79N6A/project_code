<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');
$arr = getparameter($_POST, 'verificationsnsinfo');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}
//手机号
$sns_id = isset($arr['sns_id']) ? addslashes(intval(trim($arr['sns_id']))) : '';
$sns_headerurl = isset($arr['sns_headerurl']) ? addslashes(strval(trim($arr['sns_headerurl']))) : '';
$sns_nickname = isset($arr['sns_nickname']) ? addslashes(strval(trim($arr['sns_nickname']))) : '';
$sns_url = isset($arr['sns_url']) ? addslashes(strval(trim($arr['sns_url']))) : '';
$sns_description = isset($arr['sns_description']) ? addslashes(strval(trim($arr['sns_description']))) : '';
$sns_token = isset($arr['sns_token']) ? addslashes(strval(trim($arr['sns_token']))) : '';
$sns_expirationtime = isset($arr['sns_expirationtime']) ? addslashes(strval(trim($arr['sns_expirationtime']))) : '';
$sns_type = isset($arr['sns_type']) ? addslashes(strval(trim($arr['sns_type']))) : '';

if(!empty($sns_id))
{
	$condition = array( 'sns_id' => $sns_id, 'sns_type' => 'weibo');
	$aField = DB::LimitQuery('jx_users_sns', array(
			'condition' => $condition,
			'one' => true,
	));
	//判断jx_users_sns表中第三方用户是否存在，如果存在，则直接登录，不在向表中插入新数据
	if(!empty($aField['sns_id']))
	{
		//判断jx_users表中是否存在对应的用户
		if($aField['uid'] != '-1')
		{
			$c = array('id'=>$aField['uid']);
			$user = DB::LimitQuery('jx_users', array(
					'condition' => $c,
					'one' => true,
			));
			if(!empty($user))
			{
				$array['ret'] = 100;
				$array['msg'] = "微博登录成功，获取用户信息成功";
				$array['user_id'] = $user['id'];
				$array['user_mobile'] = $user['mobile'];
				$array['user_name'] = $user['nickname'];
				$array['user_email'] = $user['email'];
				$array['user_desc'] = $user['description'];
				$array['user_url'] = !empty($user['headerurl'])?$INI['system']['imgprefix'].'/'.$user['headerurl']:$INI['system']['imgprefix'].'/'.'static/images/100.png';
				if(!empty($user['mobile'])){
					$binding = DB::LimitQuery('jx_bindings', array(
							'condition' => array('mobile' => $user['mobile']),
							'one'=>true
					));
					$array['icardpay_payno'] = isset($binding['payno'])?$binding['payno']:'';
				}else{
					$array['icardpay_payno'] = '';
				}
				$array['sns_id'] = $aField['sns_id'];
				$array['sns_token'] = $aField['sns_token'];
				$array['sns_expirationtime'] = $aField['sns_expirationtime'];
				echo json_encode($array);exit;
			}
			else
			{
				$array['ret'] = 105;
				$array['msg'] = "微博登录成功，获取用户信息失败";
// 				var_dump('<pre>',$array);die;
				echo json_encode($array);exit;
			}
		}
		else
		{
			$array['ret'] = 106;
			$array['msg'] = "微博登录成功，当前未绑定用户";
			echo json_encode($array);exit;
		}
	}
	else
	{
		//将第三方网站用户的信息保存到jx_users_sns表中
		$s['uid'] = '-1';
		$s['sns_type'] = $sns_type;
		$s['sns_id'] = $sns_id;
		$s['sns_nickname'] = $sns_nickname;
		$s['sns_url'] = $sns_url;
		$s['sns_token'] = $sns_token;
		$s['sns_headerurl'] = $sns_headerurl;
		$s['sns_description'] = $sns_description;
		$s['sns_expirationtime'] = $sns_expirationtime;
		$s['createtime'] = time();
		$s['id'] = DB::Insert('jx_users_sns', $s);
		if(!empty($s['id']))
		{
			$array['ret'] = 100;
			$array['msg'] = "微博注册成功,用户信息更新成功";
			$array['sid'] = $s['id'];
			echo json_encode($array);exit;
		}
		else
		{
			$array['ret'] = 103;
			$array['msg'] = "微博注册失败";
			echo json_encode($array);exit;
		}
	}
}else{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}


