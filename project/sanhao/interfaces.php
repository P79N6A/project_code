<?php
$app = isset($_GET['app']) ? $_GET['app'] : "";
$v = isset($_GET['v']) ? $_GET['v'] : "";
$op = isset($_GET['op']) ? $_GET['op'] : "";

if(!empty($app) && !empty($v) && !empty($op))
{
	if(file_exists(dirname(__FILE__) . "/interface/{$app}/{$v}/{$op}.php"))
	{
		die(require_once(dirname(__FILE__) . "/interface/{$app}/{$v}/{$op}.php"));
	}
	else 
	{
		$array['ret'] = 101;
		$array['msg'] = "{$app}/{$v}/{$op}.php"."不存在";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "接口地址错误";
	echo json_encode($array);exit;
}
