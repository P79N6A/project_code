<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if ( $_POST ) {
	$login = $_POST['islogin'];
	if($login == 'nologin')
	{
		$u['email'] = $_POST['email'];
		$u['name'] = $_POST['name'];
		$u['content'] = $_POST['content'];
		$u['createtime'] = time();
	}
	else 
	{
		//获取登录用户的个人信息
		$condition = array( 'id' => $login );
		$user = DB::LimitQuery('jx_users', array(
			'condition' => $condition,
			'one' => true,
		));
		$u['email'] = $user['email'];
		$u['mobile'] = $user['mobile'];
		$u['name'] = $user['nickname'];
		$u['content'] = $_POST['content'];
		$u['createtime'] = time();
	}
	$u['id'] = DB::Insert('jx_feedbacks', $u);
	//数据入库成功
	if($u['id'])
	{
		echo 'success';exit;
	}
	else 
	{
		echo 'fail';exit;
	}
}
