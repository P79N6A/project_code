<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$condition = array('type'=>1);
$version = DB::LimitQuery('mr_versions', array(
	'condition'=>$condition,
	'order' => 'ORDER BY id DESC',
	'one'=>true,
));

if(!empty($version))
{
	$array['ret'] = 100;
	$array['msg'] = "成功";
	$array['type'] = $version['type'];
	$array['version'] = $version['version'];
	$array['appurl'] = $version['appurl'];
	$array['status'] = $version['status'];
	$array['content'] = $version['content'];
	echo json_encode($array);exit;
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "无数据";
	echo json_encode($array);exit;
}