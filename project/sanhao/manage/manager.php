<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(dirname(__FILE__)) . '/include/function/right.php');

need_manager();
need_auth('super');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "list";

if($action == "list")
{
	$condition = array('id > 0');
	$count = Table::Count('jx_manages', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	
	$manager = DB::LimitQuery('jx_manages', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	//用于高亮显示
	$menucolor = '管理员列表';
// 	var_dump('<pre>',$manager);die;
	include template('manage_manager_index');
}
elseif($action == "add")
{
// 	var_dump('<pre>',$login_user);die;
	//只有超级管理员才可以添加管理员
	if($_POST){
		$u['username'] = $_POST['username'];
		$u['realname'] = $_POST['realname'];
		$u['mobile'] = $_POST['mobile'];
		$u['email'] = $_POST['email'];
		$u['password'] = ZUser::GenPassword($_POST['password']);
		//将权限序列化
		$u['power'] = serialize($_POST['power']);
		$u['createtime'] = time();
		$u['id'] = DB::Insert('jx_manages', $u);
		if($u['id'])
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'添加新管理员'.$u['username'].'成功';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/manager.php');
		}
		else 
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'添加新管理员'.$u['username'].'失败';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/manager.php?action=add');
		}
	}else{
		$menucolor = '添加管理员';
		include template('manage_manager_add');
	}
}
elseif($action == "update")
{
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
			$o['content'] = $login_user['username'].'修改管理员'.$_POST['username'].'的信息成功';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/manager.php');
		}
		else 
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'修改管理员'.$_POST['username'].'的信息失败';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/manager.php?action=update&id='.$id);
		}
		$menucolor = '管理员列表';
	}else{
		$id = $_GET['id'];
		$condition = array( 'id' => $id);
		$manage = DB::LimitQuery('jx_manages', array(
			'condition' => $condition,
		));
		$permiss = unserialize($manage[0]['power']);
		$manageleftcolor = 1;
		include template('manage_manager_update');
	}
}
elseif($action == "delmanager")
{
	$id = $_POST['newid'];
	//循环删除
	Table::Delete('jx_products', $id);
	redirect( WEB_ROOT . '/manage/manager.php');
}
