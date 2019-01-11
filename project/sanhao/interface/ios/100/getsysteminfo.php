<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');
// $_POST = array('user_id'=>30);
$arr = getparameter($_POST, 'getsysteminfo');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}
$uid = isset($arr['user_id']) ? addslashes(intval(trim($arr['user_id']))) : "";
$now = time();
if(!empty($arr['requesttime']))
{
	$begintime = trim($arr['requesttime']);
}
else
{
	$begintime = $now;
}

//滑动方式，如果不传值，默认为more,往下拉，刷新refresh
$type = isset($arr['type']) ? strval(trim($arr['type'])) : "more";

if(!empty($begintime) && !empty($type) )
{
	$condition[] = "uid=$uid";
	if($type == 'more')
	{
		$condition[] = "created < $begintime";
	}
	else
	{
		$condition[] = "created > $begintime";
	}
	$message = DB::LimitQuery('jx_messages', array(
			'condition'=>$condition,
			'order' => 'ORDER BY created DESC',
	));
	$alist = array();
	
	foreach ($message as $key=>$value){
		$alist[$key]['content'] = $value['content'];
		$alist[$key]['createtime'] = $value['created'];
	}
// 	var_dump('<pre>',$array);die;
	if(!empty($alist))
	{
		$array['ret'] = 100;
		$array['msg'] = "成功";
		$array['systeminfo'] = $alist;
	
		echo json_encode($array);exit;
	}
	else
	{
		$array['ret'] = 102;
		$array['msg'] = "无数据";
		echo json_encode($array);exit;
	}
}else{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}
