<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if ( $_POST ) {
	$loginsns = $_POST['loginsns'];
	$type = $_POST['type'];
	$user_sns_id = $_POST['user_sns_id'];
	//直接注册
	if($loginsns == 'weibo')
	{
		//先查询jx_users_sns中一些字段的值
		$condition = array( 'id' => $user_sns_id);
		$user_sns = DB::LimitQuery('jx_users_sns', array(
			'condition' => $condition,
			'one' => true,
		));
		//微博注册，分为三步进行，先将一些数据插入jx_users中，再修改jx_users_sns中uid的值
		$u['mobile'] = $_POST['username'];
		$u['password'] = ZUser::GenPassword($_POST['password']);
		$u['headerurl'] = $user_sns['sns_headerurl'];
		$u['nickname'] = $user_sns['sns_nickname'];
		$u['description'] = $user_sns['sns_description'];
		$u['sns'] = $loginsns;
		$u['type'] = $type;
		$u['createtime'] = time();
		$u['id'] = DB::Insert('jx_users', $u);
		if($u['id'])
		{
			//修改jx_users_sns中uid的值
			$table = new Table('jx_users_sns', $_POST);
			$table->pk_value = $user_sns_id;
			$table->uid = $u['id'];
			$up_array = array('uid');
			$flag = $table->update( $up_array );
			if($flag)
			{
				unset($_SESSION['sns_type']);
				Session::Set('user_id', $u['id']);
				Session::Set('type', $u['type']);
				Session::Set('mobile', $u['mobile']);
				echo 'success';exit;
			}
			else 
			{
				echo 'fail';exit;
			}
		}
		else 
		{
			echo 'fail';exit;
		}
	}
	else if($loginsns == 'qq')
	{
		//先查询jx_users_sns中一些字段的值
		$condition = array( 'id' => $user_sns_id);
		$user_sns = DB::LimitQuery('jx_users_sns', array(
			'condition' => $condition,
			'one' => true,
		));
		//QQ注册
		$u['mobile'] = $_POST['username'];
		$u['password'] = ZUser::GenPassword($_POST['password']);
		$u['headerurl'] = $user_sns['sns_headerurl'];
		$u['nickname'] = $user_sns['sns_nickname'];
		$u['sns'] = $loginsns;
		$u['type'] = $type;
		$u['createtime'] = time();
		$u['id'] = DB::Insert('jx_users', $u);
		if($u['id'])
		{
			//修改jx_users_sns中uid的值
			$table = new Table('jx_users_sns', $_POST);
			$table->pk_value = $user_sns_id;
			$table->uid = $u['id'];
			$up_array = array('uid');
			$flag = $table->update( $up_array );
			if($flag)
			{
				unset($_SESSION['sns_type']);
				Session::Set('user_id', $u['id']);
				Session::Set('type', $u['type']);
				Session::Set('mobile', $u['mobile']);
				echo 'success';exit;
			}
			else 
			{
				echo 'fail';exit;
			}
		}
		else 
		{
			echo 'fail';exit;
		}
	}
	else 
	{
		//将用户的注册信息传递过来，同时将账户的类型也传递过来，当账户类型type为1时，为邮箱注册，为2时，则为手机号注册，分别将不同的信息保存在不同的字段
		if($type == 1)
		{
			$u['email'] = $_POST['username'];
		}
		else 
		{
			$u['mobile'] = $_POST['username'];
		}
		$u['password'] = ZUser::GenPassword($_POST['password']);
		$u['createtime'] = time();
		$u['type'] = $type;
		$u['id'] = DB::Insert('jx_users', $u);
		//注册成功
		if($u['id'])
		{
			
			Session::Set('user_id', $u['id']);
			Session::Set('type', $u['type']);
			if($u['type'] == 1)
			{
				Session::Set('email', $u['email']);
			}
			else 
			{
				Session::Set('mobile', $u['mobile']);
			}
			echo 'success';exit;
			//redirect(get_loginpage(WEB_ROOT . '/account/signuped.php'));
		}
		else 
		{
			echo 'fail';exit;
			//redirect(get_loginpage(WEB_ROOT . '/account/signup.php'));
		}
	}
}

$pagetitle = '注册';
include template('account_signup');
