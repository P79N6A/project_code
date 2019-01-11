<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'addproposal');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

$merchant_mobile = isset($arr['merchant_mobile']) ? addslashes(strval(trim($arr['merchant_mobile']))) : '';
$content = isset($arr['content']) ? addslashes(strval(trim($arr['content']))) : '';

if(!empty($content) && !empty($merchant_mobile))
{
	$u['content'] = $content;
	$u['mobile'] = $merchant_mobile;
	$u['createtime'] = time();
	$u['id'] = DB::Insert('mr_proposals', $u);
	if($u['id'])
	{
		$array['ret'] = 100;
		$array['msg'] = "意见反馈成功";
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "建议添加失败";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}