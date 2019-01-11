<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(dirname(__FILE__)) . '/include/function/right.php');

need_manager();
need_auth('super');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "list";

if($action == "list")
{
	$condition = array('id > 0 and module != 1');
	$count = Table::Count('jx_oplogs', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	
	$opera = DB::LimitQuery('jx_oplogs', array(
		'condition' => $condition,
		'order' => 'ORDER BY id DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	if(!empty($opera))
	{
		foreach($opera as $key=>$value)
		{
			$usersaler = DB::LimitQuery('jx_manages', array(
					'condition'=>array('id'=>$value['adminid']),
					'one'=>true
			));
			$opera[$key]['admin'] = $usersaler['username'];
		}
	}
	$func = 'list';
	$menucolor = '操作记录';
}elseif ($action == 'record'){
	$condition = array('id > 0 and module = 1');
	$count = Table::Count('jx_oplogs', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	
	$opera = DB::LimitQuery('jx_oplogs', array(
			'condition' => $condition,
			'order' => 'ORDER BY id DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	if(!empty($opera))
	{
		foreach($opera as $key=>$value)
		{
			$usersaler = DB::LimitQuery('jx_manages', array(
					'condition'=>array('id'=>$value['adminid']),
					'one'=>true
			));
			$opera[$key]['admin'] = $usersaler['username'];
		}
	}
	$func = 'record';
	$menucolor = '登录记录';
}
include template('manage_operation_index');