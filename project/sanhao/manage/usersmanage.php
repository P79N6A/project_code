<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(dirname(__FILE__)) . '/include/function/right.php');

need_manager();
need_auth('super');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "list";

if($action == "list")
{
	$mobile = isset($_REQUEST['mobile']) ? $_REQUEST['mobile'] : "";
	$nickname = isset($_REQUEST['nickname']) ? $_REQUEST['nickname'] : "";
	$condition = array('status = 1');
	
	if(!empty($mobile))
	{
		$condition[] = "mobile = '".$mobile."'";
	}
	if(!empty($nickname))
	{
		$condition[] = "nickname like '%".$nickname."%'";
	}
	$count = Table::Count('jx_users', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	
	$users = DB::LimitQuery('jx_users', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	foreach($users as $ukey => $uval){
		$ubuding = DB::LimitQuery('jx_bindings', array(
				'condition'=>array('mobile'=>$uval['mobile']),
				'one'=>true
		));
		$users[$ukey]['payno'] = $ubuding['payno'];
	}
// 	var_dump('<pre>',$users);die;
	
	$ufunc = "list";
	$menucolor = '用户列表';
	include template('manage_users_index');
}
else if($action == "recycle")
{
	$mobile = isset($_REQUEST['mobile']) ? $_REQUEST['mobile'] : "";
	$nickname = isset($_REQUEST['nickname']) ? $_REQUEST['nickname'] : "";
	$condition = array('status = 0');
	
	if(!empty($mobile))
	{
		$condition[] = "mobile = '".$mobile."'";
	}
	if(!empty($nickname))
	{
		$condition[] = "nickname like '%".$nickname."%'";
	}
	$count = Table::Count('jx_users', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	
	$users = DB::LimitQuery('jx_users', array(
			'condition' => $condition,
			'order' => 'ORDER BY id DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	$ufunc = "recycle";
	$menucolor = '用户回收站';
	include template('manage_users_index');
}else if($action == 'detail')
{
	$id = strval($_GET['id']);
	$condition = array('id' => $id);
	//查询订单的信息
	$userinfo = DB::LimitQuery('jx_users', array(
			'condition' => $condition,
			'one'=>true
	));
	$ubuding = DB::LimitQuery('jx_bindings', array(
			'condition'=>array('mobile'=>$userinfo['mobile']),
			'one'=>true
	));
	$userinfo['payno'] = $ubuding['payno'];
	$userinfo['createtime'] = date('Y-m-d H:i:s',$ubuding['createtime']);
	$ufunc = $_GET['ufunc'];
	if($ufunc == 'list'){
		$menucolor = '用户列表';
	}else if($ufunc == 'recycle'){
		$menucolor = '用户回收站';
	}
	include template('manage_users_detail');
	
}else if($action == 'del'){
	$id = $_POST['chk'];
	$idarr = explode(',',$id);
	foreach ($idarr as $ikey => $ival){
		$uarray = array( 'status' => 0 );
		$productupdate = Table::UpdateCache('jx_users', $ival, $uarray);
	}
	if($productupdate){
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'禁用id为'.$id.'的用户成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'list';
	}else{
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'禁用id为'.$id.'的用户成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'failure';
	}
	exit;
}else if($action == 'reenable'){
	$id = $_POST['chk'];
	$idarr = explode(',',$id);
	foreach ($idarr as $ikey => $ival){
		$uarray = array( 'status' => 1 );
		$productupdate = Table::UpdateCache('jx_users', $ival, $uarray);
	}
	if($productupdate){
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'启用id为'.$id.'的用户成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'recycle';
	}else{
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'启用id为'.$id.'的用户失败';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'failure';
	}
	exit;
}
