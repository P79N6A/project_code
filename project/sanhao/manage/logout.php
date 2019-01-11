<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if (isset($_SESSION['admin_id'])) {
	$o['adminid'] = $_SESSION['admin_id'];
	$o['module'] = 1;
	$o['content'] = $login_user['username'].'退出登录成功';
	$o['ip'] = Utility::GetRemoteIp();
	$o['created'] = time();
	$o['id'] = DB::Insert('jx_oplogs', $o);
	unset($_SESSION['admin_id']);
}
session_destroy() ;
redirect( WEB_ROOT . '/manage/login.php');
