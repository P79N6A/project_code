<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(dirname(__FILE__)) . '/include/function/right.php');

need_manager();
need_auth('super');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "list";
if($action == "list")
{
	$condition = array('id > 0');
	$count = Table::Count('jx_feedbacks', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	
	$manager = DB::LimitQuery('jx_feedbacks', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	//用于高亮显示
	$menucolor = '意见反馈';
	include template('manage_feedback_index');
}
elseif($action == "reply")
{
	if($_POST){
		//只有超级管理员才可以修改管理员
		$id = strval($_POST['id']);
		$table = new Table('jx_feedbacks', $_POST);
		$table->pk_value = $id;
		$table->reply = $_POST['reply'];
		$table->replytime = time();
		
		$up_array = array('reply', 'replytime');
		$flag = $table->update( $up_array );
		if($flag)
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'回复了id为'.$id.'的意见成功';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			$feedbackcolor = 1;
			redirect( WEB_ROOT . '/manage/feedback.php');
		}
		else 
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'回复了id为'.$id.'的意见失败';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			$feedbackcolor = 1;
			redirect( WEB_ROOT . '/manage/feedback.php?action=reply&id='.$id);
		}
	}else{
		$id = $_GET['id'];
		$condition = array( 'id' => $id);
		$manage = DB::LimitQuery('jx_feedbacks', array(
			'condition' => $condition,
		));
		$menucolor = '意见反馈';
		include template('manage_feedback_reply');
	}
}