<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(dirname(__FILE__)) . '/include/function/right.php');

if($_POST){
	//只有超级管理员才可以修改管理员
	$id = strval($_POST['id']);
	$table = new Table('jx_manages', $_POST);
	$table->pk_value = $id;
	$table->username = $_POST['username'];
	$table->realname = $_POST['realname'];
	$table->mobile = $_POST['mobile'];
	$table->email = $_POST['email'];
	if(!empty($_POST['password']))
	{
		$table->password = ZUser::GenPassword($_POST['password']);
	}
	$table->power = serialize($_POST['power']);
	if(!empty($_POST['password']))
	{
		$up_array = array('username', 'realname', 'mobile', 'email', 'password', 'power');
	}
	else 
	{
		$up_array = array('username', 'realname', 'mobile', 'email', 'power');
	}
	$flag = $table->update( $up_array );
	if($flag)
	{
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 3;
		$o['content'] = $login_user['username'].'修改了个人信息成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		
		redirect( WEB_ROOT . '/manage/admininfo.php');
	}
	else 
	{
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 3;
		$o['content'] = $login_user['username'].'修改了个人信息失败';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		redirect( WEB_ROOT . '/manage/admininfo.php');
	}
}
$id = $_SESSION['admin_id'];
$ainfo = DB::GetTableRow('jx_manages', array( "id=".$id));
$permiss = unserialize($ainfo['power']);
// 	var_dump('<pre>',$ainfo);die;
$menucolor = '修改信息';
include template('manage_admininfo');
