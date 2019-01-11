<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "list";
if($action == 'list'){
	$captcha = strval($_POST['vcaptcha']);
	if ( $_POST && Utility::CaptchaCheck($captcha)) {		
		$login_admin = ZUser::GetManageLogin($_POST['username'], $_POST['password']);
		if ( !$login_admin ) {
			Session::Set('error', '用户名密码不匹配！');
			$msg = '用户名密码不匹配！';
			include template('manage_login');
		} else {
			Session::Set('admin_id', $login_admin['id']);
			Session::Set('user_id', $login_admin['id']);
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 1;
			$o['content'] = $login_user['username'].'登录成功';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/order.php');
		}
	}else if($_POST){
		$msg = '验证码输入有误';
		include template('manage_login');
	}
	include template('manage_login');
}else if($action == 'checkusername'){
	$ainfo = DB::GetTableRow('jx_manages', array( "username='".$_POST['username']."'"));
	if($ainfo){
		echo 'exist';
	}else{
		echo 'noexist';
	}
	exit;
}
