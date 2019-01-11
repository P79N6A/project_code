<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');


if ( $_POST ) 
{
	$u['mobile'] = $_POST['mobile'];
	$u['code'] = rand(1000, 9999);
	$u['content'] = '三好网手机注册验证码：'.$u['code'].'。请完成认证。如非本人操作，请忽略本短信。【三好网，随时随地做买卖】';
	$ret = send( $u['mobile'] , $u['content'] );
	//$ret = array();
	if(empty($ret))
	{
		$u['ret'] = 1;
		$u['mid'] = 0;
		$u['cpmid'] = 0;
	}
	else 
	{
		$u['ret'] = $ret->ret;
		$u['mid'] = $ret->mid;
		$u['cpmid'] = $ret->cpmid;
	}
	$u['type'] = 'register';
	$u['addtime'] = time();
	$u['id'] = DB::Insert('jx_smscodes', $u);
	if($u['id'])
	{
		echo 'success';
	}
	else 
	{
		echo 'fail';
	}
	exit;
}
