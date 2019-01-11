<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
need_checksns();
if ( $_POST )
{
	$id = $_POST['id'];
	$table = new Table('jx_users', $_POST);
	$table->headerurl = $_POST['headurl'];
	$table->nickname = $_POST['nickname'];
	$table->email = $_POST['email'];
	$table->mobile = $_POST['mobile'];
	$table->website = $_POST['website'];
	$table->description = $_POST['description'];
	$table->type = $_POST['usertype'];
	$table->updatetime = time();
	
	//上传了图像，对图片进行缩略，大小为30*30
	if(!empty($table->headerurl))
	{
		$imgtime = date('Y-m-d');
		$list = IMG_ROOT.'/'.'user/small'.'/'.$imgtime;
		if(!file_exists( $list ))
		{
			RecursiveMkdir($list);
		}
		//新图片的路径
		$url = str_replace('user/old', 'user/small', $table->headerurl);
		$oldimg = $table->headerurl;
		$ret = imageResize($oldimg, 30, 30, $url, true);
	}
	//如果type值为1，为邮箱注册，此时邮箱不能改，如果为2，手机号码注册，手机号码不能改
	if($table->type == 1)
	{
		$up_array = array('mobile', 'headerurl', 'nickname', 'website', 'description','type', 'updatetime');
	}
	else 
	{
		$up_array = array('email', 'headerurl', 'nickname', 'website', 'description','type', 'updatetime');
	}
	$flag = $table->update( $up_array );
	if($flag)
	{
		echo 'success';
	}
	else 
	{
		echo 'fail';
	}
	exit;
}

$condition = array( 'id' => $login_user_id);
$aField = DB::LimitQuery('jx_users', array(
		'condition' => $condition,
		'one' => true,
	));

$pagetitle = '个人资料页';
include template('account_personal');