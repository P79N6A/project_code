<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'addproposal');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

$mobile = isset($arr['mobile']) ? addslashes(strval(trim($arr['mobile']))) : '';
$content = isset($arr['content']) ? addslashes(strval(trim($arr['content']))) : '';
$type = isset($arr['type']) ? addslashes(strval(trim($arr['type']))) : '';
// $correctdate = DIR_ROOT.'/'.date('Y-m-d');
// if(!file_exists( $correctdate ))
// 	{
// 		@mkdir($correctdate, 0777);
// 	}
// 		file_put_contents(DIR_ROOT.'/'.date('Y-m-d').'/'.'1_'.time().'.txt' , print_r( $_POST , true ) ) ;
if(!empty($content) && !empty($mobile) && !empty($type))
{
	$u['content'] = $content;
	$u['mobile'] = $mobile;
	$u['type'] = $type;
	$u['createtime'] = time();
	$u['id'] = DB::Insert('jx_proposals', $u);
	if($u['id'])
	{
		$array['ret'] = 100;
		$array['msg'] = "意见反馈成功";
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "意见反馈失败";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}