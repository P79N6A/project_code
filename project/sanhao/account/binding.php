<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if ( $_POST ) {
	$username = strval($_POST['mobile']);
	$password = strval(ZUser::GenPassword($_POST['password']));
	$loginsns = strval($_POST['loginsns']);
	$user_sns_id = strval($_POST['user_sns_id']);
	$user = DB::GetTableRow('jx_users', array( "mobile='".$username."'"));
	if(!$user)
	{
		//该手机号尚未注册
		echo 'noexist';
		exit;
	}
	else 
	{
		$login = DB::GetTableRow('jx_users', array( "mobile='".$username."'",'password'=>$password));
		if(!$login)
		{
			//账号或密码错误，请重新输入
			echo 'fail';
			exit;
		}
		else 
		{
			if($loginsns == 'qq')
			{
				//先查询jx_users_sns表中的数据
				$conditionsns = array( 'id' => $user_sns_id);
				$user_sns = DB::LimitQuery('jx_users_sns', array(
					'condition' => $conditionsns,
					'one' => true,
				));
				//绑定QQ，先修改jx_users表中的数据
				$table = new Table('jx_users', $_POST);
				$table->pk_value = $login['id'];
				if(empty($login['headerurl']))
				{
					$table->headerurl = $user_sns['sns_headerurl'];
				}
				else 
				{
					$table->headerurl = $login['headerurl'];
				}
				if(empty($login['nickname']))
				{
					$table->nickname = $user_sns['sns_nickname'];
				}
				else 
				{
					$table->nickname = $login['nickname'];
				}

				$up_array = array('headerurl', 'nickname');
				$flag = $table->update( $up_array );
				if($flag)
				{
					//修改jx_user_sns表中uid的值
					$table = new Table('jx_users_sns', $_POST);
					$table->pk_value = $user_sns_id;
					$table->uid = $login['id'];
					$up_array = array('uid');
					$flag = $table->update( $up_array );
					if($flag)
					{
						unset($_SESSION['sns_type']);
						Session::Set('user_id', $login['id']);
						Session::Set('type', $login['type']);
						Session::Set('mobile', $login['mobile']);
						echo 'success';
						exit;
					}
					else 
					{
						echo 'bdfail';
						exit;
					}
					
				}
				else 
				{
					echo 'bdfail';
					exit;
				}
			}
			else 
			{
				//先查询jx_users_sns表中的数据
				$conditionsns = array( 'id' => $user_sns_id);
				$user_sns = DB::LimitQuery('jx_users_sns', array(
					'condition' => $conditionsns,
					'one' => true,
				));
				//绑定微博
				$table = new Table('jx_users', $_POST);
				$table->pk_value = $login['id'];
				if(empty($login['headerurl']))
				{
					$table->headerurl = $user_sns['sns_headerurl'];
				}
				else 
				{
					$table->headerurl = $login['headerurl'];
				}
				if(empty($login['nickname']))
				{
					$table->nickname = $user_sns['sns_nickname'];
				}
				else 
				{
					$table->nickname = $login['nickname'];
				}
				if(empty($login['description']))
				{
					$table->description = $user_sns['sns_description'];
				}
				else 
				{
					$table->description = $login['description'];
				}
				$up_array = array('headerurl', 'nickname', 'description');
				$flag = $table->update( $up_array );
				if($flag)
				{
					//修改jx_user_sns表中uid的值
					$table = new Table('jx_users_sns', $_POST);
					$table->pk_value = $user_sns_id;
					$table->uid = $login['id'];
					$up_array = array('uid');
					$flag = $table->update( $up_array );
					if($flag)
					{
						unset($_SESSION['sns_type']);
						Session::Set('user_id', $login['id']);
						Session::Set('type', $login['type']);
						Session::Set('mobile', $login['mobile']);
						echo 'success';
						exit;
					}
					else 
					{
						echo 'bdfail';
						exit;
					}	
				}
				else 
				{
					echo 'bdfail';
					exit;
				}
			}
		}
	}
}