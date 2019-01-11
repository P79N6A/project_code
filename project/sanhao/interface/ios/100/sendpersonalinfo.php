<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');
// $_POST = array('user_id'=>11,'type'=>'update','is_me'=>2,'user_nick'=>'妞妞','user_mobile'=>'15210968775','user_desc'=>'房价都是垃圾分类的数据阿里范德萨','user_email'=>'lzdll@163.com');
$arr = getparameter($_POST, 'sendpersonalinfo');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}
//商户ID
$user_id = isset($arr['user_id']) ? addslashes(intval(trim($arr['user_id']))) : "";
$type = isset($arr['type']) ? addslashes(strval(trim($arr['type']))) : "";
$is_me = isset($arr['is_me']) ? addslashes(intval(trim($arr['is_me']))) : "2";
$user_url = isset($_FILES['user_url']) ? $_FILES['user_url'] : "";
$nickname = isset($arr['user_name']) ? $arr['user_name'] : "";
$user_desc = isset($arr['user_desc']) ? $arr['user_desc'] : "";
$user_email = isset($arr['user_email']) ? $arr['user_email'] : "";

if(!preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/",$user_email))
{
	$array['ret'] = 105;
	$array['msg'] = "邮箱格式不正确";
	echo json_encode($array);exit;
}
if(!empty($user_id) && $type == 'get')
{
	$condition = array( 'id' => $user_id);
	$user = DB::LimitQuery('jx_users', array(
		'condition' => $condition,
		'one' => true,
	));
	
	if(!empty($user))
	{
		$array['ret'] = 100;
		$array['msg'] = "商户详情";
		$array['user_id'] = $user['id'];
		$array['user_mobile'] = $user['mobile'];
		$array['user_nick'] = $user['nickname'];
		$array['user_email'] = $user['email'];
		$array['user_desc'] = $user['description'];
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "无商户详细信息";
		echo json_encode($array);exit;
	}
}else if(!empty($user_id) && $type == 'update'){
	
	if(!empty($is_me) && $is_me == '1'){
		$img = $user_url['tmp_name'];
		$filename = $user_url['name'];
		$type = $user_url['type'];
		$size = $user_url['size'];
		//验证图片大小
		if( $size==0 || $size >= 5242880 )
		{
			$array['ret'] = 103;
			$array['msg'] = "图片不符合要求";
			echo json_encode($array);exit;
		}
		list($width, $height, $pic_info) = @getimagesize($img);
		if($width <= 100 && $height <= 100)
		{
			$array['ret'] = 104;
			$array['msg'] = "图片不符合要求";
			echo json_encode($array);exit;
		}
		$imgname = explode('.',$filename);
		$imgtime = date('Y-m-d');
		$rand = rand(10000, 999999);
		$md5str = md5(time().$rand);
		$newname = $md5str.'.'.$imgname[1];
		$artwork = IMG_ROOT.'/'.'user/old'.'/'.$imgtime;
		//上传原图
		$relinfo4 = image_upload($img, $artwork, $newname, $type, 0, 0, 1);
		$list = IMG_ROOT.'/'.'user/big'.'/'.$imgtime;
		if(!file_exists( $list ))
		{
			RecursiveMkdir($list);
		}
		//图片进行缩略，大小为100*100
		$url = $list.'/'.$newname;
		$oldimg = $artwork."/".$newname;
		//头像缩略为50*50
		$ret = imageResize($oldimg, 100, 100, $url, true);
		$urlold = 'static/user/old'.'/'.$imgtime.'/'.$newname;
		
		$table = new Table('jx_users', $_POST);
		$table->pk_value = $user_id;
		$table->nickname = $nickname;
		$table->email = $user_email;
		$table->headerurl = $urlold;
		$table->description = $user_desc;
		$up_array = array('nickname', 'email','description','headerurl');
		$flag = $table->update( $up_array );
		if($flag)
		{
			$array['ret'] = 100;
			$array['msg'] = "修改用户信息成功";
			$array['user_id'] = $user_id;
			$array['user_name'] = $nickname;
			$array['user_email'] = $user_email;
			$array['user_desc'] = $user_desc;
			$array['user_url'] = $INI['system']['imgprefix'].'/'.$urlold;
			echo json_encode($array);exit;
		}
		else
		{
			$array['ret'] = 102;
			$array['msg'] = "修改用户信息失败";
			echo json_encode($array);exit;
		}
	}else{
		$table = new Table('jx_users', $_POST);
		$table->pk_value = $user_id;
		$table->nickname = $nickname;
		$table->email = $user_email;
		$table->description = $user_desc;
		$up_array = array('nickname', 'email', 'description');
		$flag = $table->update( $up_array );
		if($flag)
		{
			
			$array['ret'] = 100;
			$array['msg'] = "修改用户信息成功";
			$array['user_id'] = $user_id;
			$array['user_name'] = $nickname;
			$array['user_email'] = $user_email;
			$array['user_desc'] = $user_desc;
			
			echo json_encode($array);exit;
		}
		else
		{
			$array['ret'] = 102;
			$array['msg'] = "修改用户信息失败";
			echo json_encode($array);exit;
		}
	}
// 	var_dump('<pre>',$array);die;
}
else if($type == 'add'){
	if(!empty($is_me) && $is_me == '1'){
		$img = $user_url['tmp_name'];
		$filename = $user_url['name'];
		$type = $user_url['type'];
		$size = $user_url['size'];
		//验证图片大小
		if( $size==0 || $size >= 5242880 )
		{
			$array['ret'] = 103;
			$array['msg'] = "图片不符合要求";
			echo json_encode($array);exit;
		}
		list($width, $height, $pic_info) = @getimagesize($img);
		if($width <= 100 && $height <= 100)
		{
			$array['ret'] = 104;
			$array['msg'] = "图片不符合要求";
			echo json_encode($array);exit;
		}
		$imgname = explode('.',$filename);
		$imgtime = date('Y-m-d');
		$rand = rand(10000, 999999);
		$md5str = md5(time().$rand);
		$newname = $md5str.'.'.$imgname[1];
		$artwork = IMG_ROOT.'/'.'user/old'.'/'.$imgtime;
		//上传原图
		$relinfo4 = image_upload($img, $artwork, $newname, $type, 0, 0, 1);
		$list = IMG_ROOT.'/'.'user/big'.'/'.$imgtime;
		if(!file_exists( $list ))
		{
			RecursiveMkdir($list);
		}
		//图片进行缩略，大小为100*100
		$url = $list.'/'.$newname;
		$oldimg = $artwork."/".$newname;
		//头像缩略为50*50
		$ret = imageResize($oldimg, 100, 100, $url, true);
		$urlold = 'static/user/old'.'/'.$imgtime.'/'.$newname;
		$u['nickname'] = $nickname;
		$u['headerurl'] = $urlold;		
		$u['email'] = $user_email;
		$u['description'] = $user_desc;
		$u['createtime'] = time();
		$u['type'] = 0;
		$u['id'] = DB::Insert('jx_users', $u);
		if($u['id'])
		{
			$m['uid'] = $u['id'];
			$m['type'] = 1;
			$m['is_read'] = 0;
			$m['content'] = "Hi，恭喜你成功开通交享团帐号，在这里你可以随心随意卖东西，还可以淘到超划算的东东哦！一切都可以在交享团搞定，还在等什么，走起~~~";
			$m['created'] = time();
			$m['id'] = DB::Insert('jx_messages', $m);
		
			$array['ret'] = 100;
			$array['msg'] = "注册成功";
			$array['user_id'] = $u['id'];
			$array['user_mobile'] = '';
			$array['user_name'] = $nickname;
			$array['icardpay_payno'] = '';
			echo json_encode($array);exit;
		}
		else
		{
			$array['ret'] = 105;
			$array['msg'] = "注册失败";
			echo json_encode($array);exit;
		}
	}else{
		$u['nickname'] = $nickname;
		$u['email'] = $user_email;
		$u['description'] = $user_desc;
		$u['createtime'] = time();
		$u['type'] = 0;
		$u['id'] = DB::Insert('jx_users', $u);
		if($u['id'])
		{
			$m['uid'] = $u['id'];
			$m['type'] = 1;
			$m['is_read'] = 0;
			$m['content'] = "Hi，恭喜你成功开通交享团帐号，在这里你可以随心随意卖东西，还可以淘到超划算的东东哦！一切都可以在交享团搞定，还在等什么，走起~~~";
			$m['created'] = time();
			$m['id'] = DB::Insert('jx_messages', $m);
		
			$array['ret'] = 100;
			$array['msg'] = "注册成功";
			$array['user_id'] = $u['id'];
			$array['user_mobile'] = '';
			$array['user_name'] = $nickname;
			$array['icardpay_payno'] = '';
			echo json_encode($array);exit;
		}
		else
		{
			$array['ret'] = 105;
			$array['msg'] = "注册失败";
			echo json_encode($array);exit;
		}
	}
}else
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}