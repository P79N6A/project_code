<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'modifyingpassword');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//商户ID
$merchant_id = isset($arr['merchant_id']) ? addslashes(strval(trim($arr['merchant_id']))) : "";
$oldpassword = isset($arr['old_password']) ? addslashes(strval(trim($arr['old_password']))) : "";
$newpassword = isset($arr['new_password']) ? addslashes(strval(trim($arr['new_password']))) : "";

$denewpassword = Crypt3Des::decrypt($newpassword);
if(!preg_match("/^[A-Za-z0-9_-]{6,20}$/",$denewpassword))
{
	$array['ret'] = 104;
	$array['msg'] = "密码为6-20位数字,字母,_或-";
	echo json_encode($array);exit;
}

if(!empty($merchant_id) && !empty($oldpassword) && !empty($newpassword))
{
	$user = DB::GetTableRow('mr_merchants', array( "id='".$merchant_id."'"));
	if($user['password'] == $oldpassword)
	{
		//调用修改密码的接口
		$app_key = $INI['system']['app_key'];
		$version = '1.0';
		$service_type = 'icardpay.mr.pos.user.mdy.pwd';
		$mobile = $user['mobile'];
		$pwd_type = 1;
		$old_pwd = trim($oldpassword);
		$new_pwd = trim($newpassword);
		//系统分配的密匙
		$key = $INI['system']['key'];
		
		//签名
		$sign = md5($app_key.$mobile.$new_pwd.$old_pwd.$pwd_type.$service_type.$version.$key);
		$url = $INI['system']['url'];
		$data = 'app_key='.$app_key.'&mobile='.$mobile.'&new_pwd='.$new_pwd.'&old_pwd='.$old_pwd.'&pwd_type='.$pwd_type.'&service_type='.$service_type.'&version='.$version.'&sign='.$sign;
		$ret = json_decode(interface_post($url, $data));
		define('ORDER_ROOT', str_replace('\\','/',dirname(dirname(dirname(dirname(__FILE__))))));
		$correctdate = ORDER_ROOT.'/log/icardpay/mr/'.date('Y-m-d');
		
		if(!file_exists( $correctdate ))
		{
			@mkdir($correctdate, 0777);
		}
		file_put_contents($correctdate.'/'.'updatepassword_'.$mobile.'.txt' , print_r( $ret , true ) ) ;
		
		if($ret->rsp_code == '0000')
		{
			$table = new Table('mr_merchants', $_POST);
			$table->pk_value = $merchant_id;
			$table->password = $newpassword;
			$up_array = array('password');
			$flag = $table->update( $up_array );
			if($flag)
			{
				$array['ret'] = 100;
				$array['msg'] = "密码修改成功";
				echo json_encode($array);exit;
			}
			else 
			{
				$array['ret'] = 103;
				$array['msg'] = "密码修改失败";
				echo json_encode($array);exit;
			}
		}
		else 
		{
			$array['ret'] = 103;
			$array['msg'] = "密码修改失败";
			echo json_encode($array);exit;
		}
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "旧密码错误";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}