<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';

if($action == 'login')
{
	if ( $_POST ) {
		$username = strval($_POST['username']);
		$password = strval(ZUser::GenPassword($_POST['password']));
		if(empty($username) || empty($_POST['password']))
		{
			$msg = "请输入账号或密码";
		}
		else 
		{
			$user = DB::GetTableRow('jx_users', array( "mobile='".$username."'"));
			if(!$user)
			{
				$msg = "该账号尚未注册";
			}
			else 
			{
				$login = DB::GetTableRow('jx_users', array( "mobile='".$username."'",'password'=>$password));
				if(!$login)
				{
					$msg = "账号或密码错误，请重新输入";
				}
				else 
				{
					if($login['status'] == 0){
						$msg = "该用户已被禁用，请联系管理员";
					}else{
						Session::Set('user_id', $login['id']);
						Session::Set('type', $login['type']);
						if($login['type'] == 1)
						{
							Session::Set('email', $login['email']);
						}
						else 
						{
							Session::Set('mobile', $login['mobile']);
						}
						redirect(WEB_ROOT .'/account/productlist.php');
					}
				}
			}
		}
	}
}
else if($action == "sinalogin")
{
	redirect('/account/bindingsns.php');
}
else if($action == "qqlogin")
{
	redirect('/account/bindingsns.php');
}

$pagetitle = '登录';
include template('account_login');
