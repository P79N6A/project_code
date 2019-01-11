<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(dirname(__FILE__)) . '/include/function/right.php');

need_manager();
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "list";

if($action == "list")
{
	$condition = array('pid = 0');
	$models = DB::LimitQuery('jx_models', array(
			'condition' => $condition,
			'order' => 'ORDER BY sort DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	foreach($models as $mkey => $mval){
		$models[$mkey]['created'] = date('Y-m-d H:i:s',$mval['created']); 
	}
	$menucolor = '模块管理';
	include template('manage_models_index');
}else if($action == 'add'){
	if($_POST){
		$u['pid'] = $_POST['pid'];
		$u['name'] = $_POST['name'];
		$u['sort'] = $_POST['sort'];
		$u['url'] = $_POST['url'];
		$u['desc'] = $_POST['desc'];
		$u['created'] = time();
		$u['id'] = DB::Insert('jx_models', $u);
		if($u['id'])
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'添加新模版'.$u['name'].'成功';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/modelsmanage.php');
		}
		else
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'添加新模版'.$u['name'].'失败';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/modelsmanage.php?action=add');
		}
	}
	$condition = array('id > 0');
	$models = DB::LimitQuery('jx_models', array(
			'condition' => $condition,
			'order' => 'ORDER BY sort DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	$menucolor = '模块管理';
	include template('manage_models_add');
}else if($action == 'del'){
	$id = $_POST['id'];
	$ainfo = DB::GetTableRow('jx_models', array( "id=".$id));
	$condition = array("pid = $id");
	$models = DB::LimitQuery('jx_models', array(
			'condition' => $condition,
			'order' => 'ORDER BY sort DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	if($ainfo && $ainfo['pid'] == 0){
		if(is_array($models) && count($models)>0){
			echo 'failure';
		}else{
			Table::Delete('jx_models', $id);
			echo 'success';
		}
	}else{
		Table::Delete('jx_models', $id);
		echo 'success';
	}
	exit;
}else if($action == 'detail'){
	$id = $_GET['id'];
	$condition = array("pid = $id");
	$models = DB::LimitQuery('jx_models', array(
			'condition' => $condition,
			'order' => 'ORDER BY sort DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	foreach($models as $mkey => $mval){
		$models[$mkey]['created'] = date('Y-m-d H:i:s',$mval['created']);
	}
	$menucolor = '模块管理';
	include template('manage_models_detail');
}else if($action='update'){
	if($_POST){
		$id = strval($_POST['id']);
		$table = new Table('jx_models', $_POST);
		$table->pk_value = $id;
		$table->name = $_POST['name'];
		$table->sort = $_POST['sort'];
		$table->url = $_POST['url'];
		$table->desc = $_POST['desc'];
		$up_array = array('name', 'sort', 'url', 'desc');
		$flag = $table->update( $up_array );
		if($flag)
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'修改了模版信息成功';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			
			redirect( WEB_ROOT . '/manage/modelsmanage.php');
		}
		else 
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'修改了模版信息失败';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/modelsmanage.php');
		}
	}
	$id = $_GET['id'];
	$ainfo = DB::GetTableRow('jx_models', array( "id=".$id));
	$condition = array('id > 0');
	$models = DB::LimitQuery('jx_models', array(
			'condition' => $condition,
			'order' => 'ORDER BY sort DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	$menucolor = '模块管理';
	include template('manage_models_update');
}



