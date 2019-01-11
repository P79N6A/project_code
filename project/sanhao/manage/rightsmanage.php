<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(dirname(__FILE__)) . '/include/function/right.php');

need_manager();
$menucolor = '管理员列表';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "list";
if($action == "list"){
	$uid = isset($_GET['rights'])?$_GET['rights']:'';
	$condition = array('pid = 0');
	$aRes = DB::LimitQuery('jx_models', array(
			'condition' => $condition,
			'order' => 'ORDER BY sort DESC',
	));
	foreach ($aRes as $rkey => $rval){
		$pid = $rval['id'];
		$condition = array("pid = $pid");
		$sChk[$pid] = DB::LimitQuery('jx_models', array(
				'condition' => $condition,
				'order' => 'ORDER BY sort DESC',
		));
	}
	$con = array('pid > 0');
	$sChk = DB::LimitQuery('jx_models', array(
			'condition' => $con,
			'order' => 'ORDER BY sort DESC',
	));
	if( $_SESSION['admin_id'] != 1){
		foreach($sChk as $skey =>$sval){
			if($sval['name'] == '模块管理'){
				unset($sChk[$skey]);
			}
		}
	}
	$ainfo = DB::GetTableRow('jx_rights', array( "uid=".$uid));
	$rightsinfo = unserialize($ainfo['rights']);
// 	var_dump('<pre>',$sChk);die;
	
	include template('manage_rights_index');
}else if($action == 'add'){
	if($_POST){
		//将权限序列化
		$u['uid'] = $_POST['uid'];
		Table::Delete('jx_rights', $u['uid'],'uid');
		$u['rights'] = serialize($_POST['rights']);
		$u['created'] = time();
		$u['id'] = DB::Insert('jx_rights', $u);
		if($u['id'])
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'设置权限成功';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/manager.php?action=list');
		}
		else
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'设置权限失败';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/manager.php?action=list');
		}
// 		var_dump($_POST);die;
	}
}

?>