<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'modifyingpassword');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//商户ID
$merchant_id = isset($arr['user_id']) ? addslashes(strval(trim($arr['user_id']))) : "";
$oldpassword = isset($arr['old_password']) ? addslashes(strval(trim($arr['old_password']))) : "";
$newpassword = isset($arr['new_password']) ? addslashes(strval(trim($arr['new_password']))) : "";

if(!empty($merchant_id) && !empty($oldpassword) && !empty($newpassword))
{
	$user = DB::GetTableRow('jx_users', array( "id='".$merchant_id."'"));
	if($user['password'] == $oldpassword)
	{
		$table = new Table('jx_users', $_POST);
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