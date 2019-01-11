<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');
$arr = getparameter($_POST, 'bindingweibo');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}
$correctdate = DIR_ROOT.'/'.date('Y-m-d');
if(!file_exists( $correctdate ))
	{
		@mkdir($correctdate, 0777);
	}
file_put_contents(DIR_ROOT.'/'.date('Y-m-d').'/'.'1_'.time().'.txt' , print_r( $_POST , true ) ) ;
$sns_id = isset($arr['sns_id']) ? addslashes(intval(trim($arr['sns_id']))) : '';
$sns_headerurl = isset($arr['sns_headerurl']) ? addslashes(strval(trim($arr['sns_headerurl']))) : '';
$sns_nickname = isset($arr['sns_nickname']) ? addslashes(strval(trim($arr['sns_nickname']))) : '';
$sns_url = isset($arr['sns_url']) ? addslashes(strval(trim($arr['sns_url']))) : '';
$sns_description = isset($arr['sns_description']) ? addslashes(strval(trim($arr['sns_description']))) : '';
$sns_token = isset($arr['sns_token']) ? addslashes(strval(trim($arr['sns_token']))) : '';
$sns_expirationtime = isset($arr['sns_expirationtime']) ? addslashes(strval(trim($arr['sns_expirationtime']))) : '';
$sns_type = isset($arr['sns_type']) ? addslashes(strval(trim($arr['sns_type']))) : '';
//用户id
$user_id = isset($arr['user_id']) ? intval(trim($arr['user_id'])) : "";
if(!empty($user_id) && !empty($sns_id) && !empty($sns_token))
{
	$users = DB::LimitQuery('jx_users', array(
			'condition' => array('id' => $user_id),
			'one'=>true
	));
	$binding = DB::GetTableRow('jx_users_sns', array( "sns_id='".$sns_id."'"));
	if($binding){
		if($binding['uid'] == '-1'){
			
			$table = new Table('jx_users_sns', $_POST);
			$table->pk_value = $binding['id'];
			$table->uid = $user_id;
			$up_array = array('uid');
			$flag = $table->update( $up_array );
			if($flag)
			{
				$array['ret'] = 100;
				$array['msg'] = "绑定微博用户成功";
				echo json_encode($array);exit;
			}
			else
			{
				$array['ret'] = 105;
				$array['msg'] = "绑定微博用户失败";
				echo json_encode($array);exit;
			}
		}else{
			$array['ret'] = 104;
			$array['msg'] = "该帐号已绑定交享团帐号";
			echo json_encode($array);exit;
		}
		
	}else{
		if(isset($users['mobile']) && !empty($users['mobile']))
		{
			$s['uid'] = $user_id;
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
				$array['msg'] = "微博绑定成功";
				$array['sid'] = $s['id'];
				echo json_encode($array);exit;
			}
			else
			{
				$array['ret'] = 102;
				$array['msg'] = "微博绑定失败";
				echo json_encode($array);exit;
			}
		}else{
			$array['ret'] = 103;
			$array['msg'] = "该用户手机号码有误";
			echo json_encode($array);exit;
		}
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}