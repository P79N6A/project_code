<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');
$post = file_get_contents('php://input');
// $_POST = array('code'=>8552,'mobile'=>'15666666666');
$arr = getparameter($_POST, 'checkcode');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}
// $correctdate = DIR_ROOT.'/'.date('Y-m-d');
// if(!file_exists( $correctdate ))
// 	{
// 		@mkdir($correctdate, 0777);
// 	}
// file_put_contents(DIR_ROOT.'/'.date('Y-m-d').'/'.'1_'.time().'.txt' , print_r( $_POST , true ) ) ;
//手机号
$mobile = isset($arr['mobile']) ? addslashes(strval($arr['mobile'])) : '';

//验证码
$code = isset($arr['code']) ? addslashes(trim($arr['code'])) : '';

if(!preg_match("/^(1(([358][0-9])|(47)))\d{8}$/",$mobile))
{
	$array['ret'] = 104;
	$array['msg'] = "手机号码格式不正确";
	echo json_encode($array);exit;
}


if(!empty($mobile) && !empty($code))
{
	//判断短信验证码是否正确
	$condition = array("mobile='".$mobile."' and ret=0 and type='register' and comefrom=2");
	$aField = DB::LimitQuery('jx_smscodes', array(
		'condition' => $condition,
		'one'=>true,
		'order' => 'ORDER BY id DESC',
	));
// 	file_put_contents(DIR_ROOT.'/'.date('Y-m-d').'/'.'1_'.time().'.txt' , print_r( $condition , true ) ) ;
	//如果短信验证码正确，判断发送时间是否大于30分钟
	if($aField['code'] == $code)
	{
		if(time()-$aField['addtime'] <= 12*60*60)
		{
			$array['ret'] = 100;
			$array['msg'] = "验证码正确";
			echo json_encode($array);exit;
		}
		else 
		{
			$array['ret'] = 103;
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