<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

$mobile = isset($arr['user_mobile']) ? addslashes(strval(trim($arr['user_mobile']))) : '';
$type = isset($arr['type']) ? addslashes(strval(trim($arr['type']))) : '';

if(!empty($mobile))
{
	//判断手机号码格式是否正确
	if(preg_match("/^(1(([35][0-9])|(47)|[8][01236789]))\d{8}$/",$mobile))
	{
		//判断手机号码是否注册
		$user = DB::GetTableRow('jx_users', array( "mobile='".$mobile."'"));
		if(!$user)
		{
			//判断该手机号当天获取短信验证码的次数
			$begintime = strtotime(date('Y-m-d 00:00:00'));
			$endtime = strtotime(date('Y-m-d 23:59:59'));
			$condition = array("mobile='".$mobile."' and type='$type' and addtime >= $begintime and addtime <= $endtime");
			$count = Table::Count('jx_smscodes', $condition);
			if($count >= 3)
			{
				$array['ret'] = 105;
				$array['msg'] = "该手机号今天尝试的次数过多";
				echo json_encode($array);exit;
			}
			else 
			{
				//发送短信
				$u['mobile'] = $mobile;
				$u['code'] = rand(1000, 9999);
				$u['content'] = '三好网手机注册验证码：'.$u['code'].'。请完成认证。如非本人操作，请忽略本短信。【三好网，随时随地做买卖】';
				$ret = send( $u['mobile'] , $u['content'] );
				//$ret = array();
				if(empty($ret))
				{
					$u['ret'] = 1;
					$u['mid'] = 0;
					$u['cpmid'] = 0;
				}
				else 
				{
					$u['ret'] = $ret->ret;
					$u['mid'] = $ret->mid;
					$u['cpmid'] = $ret->cpmid;
				}
				$u['type'] = $type;
				$u['addtime'] = time();
				$u['comefrom'] = 2;
				$u['id'] = DB::Insert('jx_smscodes', $u);
				if($u['id'])
				{
					$array['ret'] = 100;
					$array['msg'] = "短信验证码发送成功";
					echo json_encode($array);exit;
				}
				else 
				{
					$array['ret'] = 101;
					$array['msg'] = "短信验证码发送失败";
					echo json_encode($array);exit;
				}
			}
		}
		else 
		{
			$array['ret'] = 104;
			$array['msg'] = "该手机号已注册";
			echo json_encode($array);exit;
		}
	}
	else 
	{
		$array['ret'] = 103;
		$array['msg'] = "手机号码格式错误";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 102;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}