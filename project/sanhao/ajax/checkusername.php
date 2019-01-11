<?php 
require_once(dirname(dirname(__FILE__)) . '/app.php');

$action = strval($_GET['action']);

if($action == "checkusername")
{
	$username = strval($_POST['username']);
	$user = DB::GetTableRow('jx_users', array( "mobile='".$username."'"));
	if(!$user)
	{
		//判断该手机号当天获取短信验证码的次数
		//当天0点0分0秒
		$begintime = strtotime(date('Y-m-d 00:00:00'));
		$endtime = strtotime(date('Y-m-d 23:59:59'));
		$condition = array("mobile='".$username."' and type='register' and addtime >= $begintime and addtime <= $endtime");
		$count = Table::Count('jx_smscodes', $condition);
		if($count >= 3)
		{
			echo 'morethree';
		}
		else 
		{
			echo 'success';
		}
	}
	else 
	{
		echo 'exist';
	}
	exit;
}
else if($action == 'checkrepassmobile')
{
	$username = strval($_POST['username']);
	$user = DB::GetTableRow('jx_users', array( "mobile='".$username."'"));
	if($user)
	{
		$begintime = strtotime(date('Y-m-d 00:00:00'));
		$endtime = strtotime(date('Y-m-d 23:59:59'));
		$condition = array("mobile='".$username."' and type='repass' and addtime >= $begintime and addtime <= $endtime");
		$count = Table::Count('jx_smscodes', $condition);
		if($count >= 3)
		{
			echo 'morethree';
		}
		else 
		{
			echo 'success';
		}
	}
	else 
	{
		echo 'noexist';
	}
	exit;
}
else if($action == "checksmscode")
{
	$mobile = strval($_POST['mobile']); 
	$code = strval($_POST['code']); 
	$condition = array("mobile='".$mobile."' and type='register'");
	$aField = DB::LimitQuery('jx_smscodes', array(
		'condition' => $condition,
		'one'=>true,
		'order' => 'ORDER BY id DESC',
	));
	if($aField['code'] == $code)
	{
		if(time()-$aField['addtime'] <= 30*60)
		{
			echo 'success';
		}
		else 
		{
			echo 'later';
		}
	}
	else 
	{
		echo 'fail';
	}
	exit;
}
else if($action == "checkrepasssmscode")
{
	$mobile = strval($_POST['mobile']); 
	$code = strval($_POST['code']); 
	$condition = array("mobile='".$mobile."' and type='repass'");
	$aField = DB::LimitQuery('jx_smscodes', array(
		'condition' => $condition,
		'one'=>true,
		'order' => 'ORDER BY id DESC',
	));
	if($aField['code'] == $code)
	{
		if(time()-$aField['addtime'] <= 30*60)
		{
			echo 'success';
		}
		else 
		{
			echo 'later';
		}
	}
	else 
	{
		echo 'fail';
	}
	exit;
}
else if($action == "checklogin")
{
	$username = strval($_POST['username']);
	$password = strval(ZUser::GenPassword($_POST['password']));
	$user = DB::GetTableRow('jx_users', array( "mobile='".$username."'"));
	if(!$user)
	{
		echo 'noexist';
		exit;
	}
	else 
	{
		$login = DB::GetTableRow('jx_users', array( "mobile='".$username."'",'password'=>$password));
		if(!$login)
		{
			echo 'fail';
			exit;
		}
		else 
		{	
			Session::Set('user_id', $login['id']);
			Session::Set('type', $login['type']);
			if($login['type'] == 1)
			{
				Session::Set('email', $login['email']);
			}
			else 
			{
				Session::Set('mobile', $login['mobile']);
			}
			echo 'success';
			exit;
		}
	}
}
else if($action == "checknickname")
{
	$nickname = strval($_POST['nickname']);
	$uid = intval($_POST['uid']);
	$user = DB::GetTableRow('jx_users', array( "nickname='".$nickname."'"));
	if(!$user)
	{
		echo 'noexist';
		exit;
	}
	else 
	{
		if($user['id'] != $uid)
		{
			echo 'exist';
			exit;
		}
		else 
		{
			echo 'noexist';
			exit;
		}
	}
}
else if($action == "uploadfile")
{
	$filename = $_FILES['filename']['name'];
	$img = $_FILES['filename']['tmp_name'];
	$type = $_FILES['filename']['type'];
	$size = $_FILES['filename']['size'];
	//验证图片大小
	if( $size==0 || $size >= 5242880 )
	{
		echo 1;exit;
	}
	list($width, $height, $pic_info) = @getimagesize($img);
	if($width <= 100 && $height <= 100)
	{
		echo 2;exit;
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
	//获取缩略后的图片的宽高
	$str = $urlold.'|'.$width.'|'.$height;
	if($relinfo4) 
	{
		echo json_encode($str);
	} 
	else
	{
		//上传失败
		echo 0;
	}
	exit;
}
else if($action == "uploadimage")
{
	$filename = $_FILES['Filedata']['name'];
	$img = $_FILES['Filedata']['tmp_name'];
	//$type = $_FILES['Filedata']['type'];
	$size = $_FILES['Filedata']['size'];
	//验证图片大小
	if( $size==0 || $size >= 5242880 )
	{
		echo 1;exit;
	}
	list($width, $height, $pic_info) = @getimagesize($img);
	if($width < 200 && $height < 150)
	{
		echo 2;exit;
	}
	$imgname = explode('.',$filename);
	if($imgname[1] == 'jpg' || $imgname[1] == 'JPG')
	{
		$type = 'image/jpeg';
	}
	else if($imgname[1] == 'jpeg' || $imgname[1] == 'JPEG')
	{
		$type = 'image/pjpeg';
	}
	else if($imgname[1] == 'png' || $imgname[1] == 'PNG')
	{
		$type = 'image/png';
	}
	$imgtime = date('Y-m-d');
	$rand = rand(10000, 999999);
	$md5str = md5(time().$rand);
	$newname = $md5str.'.'.$imgname[1];
	$artwork = IMG_ROOT.'/'.'product/old'.'/'.$imgtime;
	//上传原图
	$relinfo4 = image_upload($img, $artwork, $newname, $type, 0, 0, 1);
	$list = IMG_ROOT.'/'.'product/big'.'/'.$imgtime;
	if(!file_exists( $list ))
	{
		RecursiveMkdir($list);
	}
	//图片进行缩略，大小为500*375
	$url = $list.'/'.$newname;
	$oldimg = $artwork."/".$newname;
	$ret = imageProduct($oldimg, 500, 375, $url, true, true);
	//list($widthsmall, $heightsmall, $pic_infosmall) = @getimagesize($url);
	//图片进行缩略，大小为320*240
	$small = IMG_ROOT.'/'.'product/small'.'/'.$imgtime;
	if(!file_exists( $small ))
	{
		RecursiveMkdir($small);
	}
	$urlsmall = $small.'/'.$newname;
	$retsmall = imageProduct($oldimg, 320, 240, $urlsmall, true, true);
	$str = 'static/product/big'.'/'.$imgtime.'/'.$newname;
	if($retsmall) 
	{
		echo json_encode($str);
	} 
	else
	{
		//上传失败
		echo 0;
	}
	exit;
}
else if($action == 'updatepersonal')
{
	$id = $_POST['id'];
	$table = new Table('jx_users', $_POST);
	$pos = strpos($_POST['headurl'], 'http:');
	if($pos === false)
	{
		$table->headerurl = '/'.$_POST['headurl'];
	}
	else 
	{
		$table->headerurl = $_POST['headurl'];
	}
	$table->nickname = $_POST['nickname'];
	$table->email = $_POST['email'];
	$table->mobile = $_POST['mobile'];
	$table->qq = $_POST['qq'];
	$table->website = $_POST['website'];
	$table->description = $_POST['description'];
	$table->type = $_POST['usertype'];
	$table->updatetime = time();
	
	//上传了图像，对图片进行缩略，大小为30*30
	if($table->headerurl != '')
	{
		$imgtime = date('Y-m-d');
		$list = IMG_ROOT.'/'.'user/small'.'/'.$imgtime;
		if(!file_exists( $list ))
		{
			RecursiveMkdir($list);
		}
		//新图片的路径
		$url = str_replace('user/old', 'user/small', $table->headerurl);
		$oldimg = WWW_ROOT.'/'.$table->headerurl;
		$ret = imageResize($oldimg, 50, 50, WWW_ROOT.'/'.$url, true);
	}
	//如果type值为1，为邮箱注册，此时邮箱不能改，如果为2，手机号码注册，手机号码不能改
	if($table->type == 1)
	{
		$up_array = array('mobile', 'headerurl', 'nickname', 'website', 'qq' , 'description','type', 'updatetime');
	}
	else 
	{
		$up_array = array('email', 'headerurl', 'nickname', 'website', 'qq' ,'description','type', 'updatetime');
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
else if($action == "updatepassword")
{
	$id = $_POST['id'];
	$table = new Table('jx_users', $_POST);
	$table->password = ZUser::GenPassword($_POST['password']);
	$up_array = array('password');
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
else if($action == "unbindicardpay")
{
	//不管是卖家还是买家，只要订单不是处于删除状态，都不能解绑
	$condition = array("(sid = $login_user_id or uid = $login_user_id)", "state = 'pay' or state = 'complete'");
	$order = DB::LimitQuery('jx_orders', array(
				'condition' => $condition
	));
	if(empty($order))
	{
		$conditionorder = array("(sid = $login_user_id or uid = $login_user_id)", 'state'=>'unpay');
		$ordergoing = DB::LimitQuery('jx_orders', array(
				'condition' => $conditionorder
		));
		if(empty($ordergoing))
		{
			//判断有没有出售中的商品
			$now = time();
			$conditionproduct = array('uid'=>$login_user_id, 'status'=>1, "end_time > $now or end_time is NULL");
			$product = DB::LimitQuery('jx_products', array(
					'condition' => $conditionproduct
			));
			if(empty($product))
			{
				echo 'success';exit;
			}
			else 
			{
				echo 'haveproduct';exit;
			}
		}
		else 
		{
			echo 'havegoingorder';
		}
	}
	else 
	{
		echo 'havesaleorder';exit;
	}
}
else if($action == "unbindqq")
{
	//QQ解绑
	$condition = array( 'uid' => $login_user_id, 'sns_type' => 'qq' );
	$user = DB::LimitQuery('jx_users_sns', array(
				'condition' => $condition,
				'one' => true,
	));
	Table::Delete('jx_users_sns', $user['id']);
	echo 'success';
	exit;
}
else if($action == "unbindweibo")
{
	//新浪微博解绑
	$condition = array( 'uid' => $login_user_id, 'sns_type' => 'weibo');
	$user = DB::LimitQuery('jx_users_sns', array(
				'condition' => $condition,
				'one' => true,
	));
	Table::Delete('jx_users_sns', $user['id']);
	echo 'success';
	exit;
}
else if($action == 'updaterepassword')
{
	$mobile = $_POST['mobile'];
	//先根据手机号查询出用户的ID
	$user = Table::Fetch('jx_users', $mobile, 'mobile');
	$table = new Table('jx_users', $_POST);
	$table->pk_value = $user['id'];
	$table->password = ZUser::GenPassword($_POST['password']);
	$up_array = array('password');
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
else if($action == 'addproductdraft')
{
	$sproduct = $_POST['product'];
	$aproduct = json_decode($sproduct);
	$u['uid'] = $login_user_id;
	$u['pname'] = $aproduct->productname;
	$u['description'] =mysql_escape_string($aproduct->productdescription);
	$u['price'] = $aproduct->price;
	$u['type'] = 1;
	if(!empty($aproduct->max_number))
	{
		$u['max_number'] = $aproduct->max_number;
	}
	if(!empty($aproduct->old_price))
	{
		$u['old_price'] = $aproduct->old_price;
	}
	if(!empty($aproduct->express_price))
	{
		$u['express_price'] = $aproduct->express_price;
	}
	if(!empty($aproduct->end_time))
	{
		$u['end_time'] = $aproduct->end_time;
	}
	$u['status'] = 2;
	$u['createtime'] = time();
	$u['id'] = DB::Insert('jx_products', $u);
	if($u['id'])
	{
		//获取传递过来的图片信息
		$aImage = $aproduct->image;
		foreach ($aImage as $key=>$value)
		{
			$productimage['pid'] = $u['id'];
			$productimage['image'] = $value->picurl;
			$productimage['type'] = $value->type;
			$productimage['createtime'] = time();
			DB::Insert('jx_products_image', $productimage);
		}
		//获取传递过来的商品属性值
		$apropertype = $aproduct->property;
		$icount = count($apropertype);
		if($icount > 0)
		{
			foreach ($apropertype as $k=>$v)
			{
				$property['pid'] = $u['id'];
				$property['name'] = $v->name;
				$property['content'] = $v->content;
				$property['createtime'] = time();
				DB::Insert('jx_products_property', $property);
			}
		}
		echo $u['id'].'|'.date('H:i', $u['createtime']);
	}
	else 
	{
		echo 'fail';
	}
	exit;
}
else if($action == 'updateproduct')
{
	$id = $_POST['id'];
	$condition = array( 'id' => $id);
	$product = DB::LimitQuery('jx_products', array(
		'condition' => $condition,
		'one' => true,
	));
	$sproduct = $_POST['product'];
	$aproduct = json_decode($sproduct);
	$table = new Table('jx_products', $_POST);
	$table->pname = $aproduct->productname;
	$table->description =mysql_escape_string($aproduct->productdescription);
	$table->price = $aproduct->price;
	if(!empty($aproduct->max_number))
	{
		if($product['max_number'] == $product['sale_number'])
		{
			$table->max_number = $product['max_number']+$aproduct->max_number;
		}
		else if(!empty($product['sale_number']))
		{
			$table->max_number = $product['sale_number']+$aproduct->max_number;
		}
		else 
		{
			$table->max_number = $aproduct->max_number;
		}
	}
	else 
	{
		$table->max_number = NULL;
	}
	if(!empty($aproduct->old_price))
	{
		$table->old_price = $aproduct->old_price;
	}
	else 
	{
		$table->old_price = NULL;
	}
	if(!empty($aproduct->express_price))
	{
		$table->express_price = $aproduct->express_price;
	}
	else 
	{
		$table->express_price= NULL;
	}
	if(!empty($aproduct->end_time))
	{
		$table->end_time = $aproduct->end_time;
	}
	else 
	{
		$table->end_time = NULL;
	}
	$table->status = 2;
	$table->modifytime = time();
	
	$up_array = array('pname', 'description', 'price', 'max_number', 'old_price','express_price', 'end_time', 'status', 'modifytime');
	$flag = $table->update( $up_array );
	if($flag)
	{
		//先删除以前的图片，在保存传递过来的图片信息
		Table::Delete('jx_products_image', $id, 'pid');
		//获取传递过来的图片信息
		$aImage = $aproduct->image;
		foreach ($aImage as $key=>$value)
		{
			$productimage['pid'] = $id;
			$productimage['image'] = $value->picurl;
			$productimage['type'] = $value->type;
			$productimage['createtime'] = time();
			DB::Insert('jx_products_image', $productimage);
		}
		//先删除以前的属性，在保存传递过来的商品属性
		Table::Delete('jx_products_property', $id, 'pid');
		$apropertype = $aproduct->property;
		$icount = count($apropertype);
		if($icount > 0)
		{
			foreach ($apropertype as $k=>$v)
			{
				$property['pid'] = $id;
				$property['name'] = $v->name;
				$property['content'] = $v->content;
				$property['createtime'] = time();
				DB::Insert('jx_products_property', $property);
			}
		}
		echo $id.'|'.date('H:i',$table->modifytime);
	}
	else 
	{
		echo 'fail';
	}
	exit;
}
else if($action == 'delproduct')
{
	$id = $_POST['id'];
	Table::Delete('jx_products', $id);
	Table::Delete('jx_products_image', $id, 'pid');
	Table::Delete('jx_products_property', $id, 'pid');
	echo 'success';exit;
}
else if($action == 'shelvesproduct')
{
	$id = $_POST['id'];
// 	$product = DB::GetTableRow('jx_products', array( "id=$id"));
	$table = new Table('jx_products', $_POST);
	$table->status = 3;
	$up_array = array('status');
	$flag = $table->update( $up_array );
	if($flag){
		echo 'success';exit;
	}else{
		echo 'failure';exit;
	}
}
else if($action == 'getcity')
{
	$id = $_GET['id'];
	$result = array( 
		'error'=>0
	);
	if(empty($id))
	{
		echo json_encode( $result );
		exit;
	}
	$condition = array( 'pID' => $id);
	$city = DB::LimitQuery('jx_areas', array(
			'condition' => $condition,
	));
	if( !empty( $city ) )
	{
		$result['data'] = $city ;	
	}
	else
	{
		$result['error'] = 1 ;
	}
	echo json_encode($result);exit;
}
else if($action == 'getarea')
{
	$id = $_GET['id'];
	$result = array( 
		'error'=>0
	);
	if(empty($id))
	{
		echo json_encode( $result );
		exit;
	}
	$condition = array( 'pID' => $id);
	$city = DB::LimitQuery('jx_areas', array(
			'condition' => $condition,
	));
	if( !empty( $city ) )
	{
		$result['data'] = $city ;	
	}
	else
	{
		$result['error'] = 1 ;
	}
	echo json_encode($result);exit;
}
else if($action == 'updatepersonaladdress')
{
	$id = $_POST['id'];
	//如果传递过来的id为空，则是第一次添加地址，否则修改地址
	if(empty($id))
	{
		$u['uid'] = $login_user_id;
		$u['name'] = $_POST['name'];
		$u['mobile'] = $_POST['mobile'];
		$u['phone'] = $_POST['phone'];
		$u['province_id'] = $_POST['province'];
		$u['city_id'] = $_POST['city'];
		$u['area_id'] = $_POST['area'];
		$u['street'] = $_POST['street'];
		$u['postcode'] = $_POST['zip'];
		$u['default_type'] = 1;
		$u['createtime'] = time();
		$u['id'] = DB::Insert('jx_address', $u);
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
	else 
	{
		$table = new Table('jx_address', $_POST);
		$table->name = $_POST['name'];
		$table->mobile = $_POST['mobile'];
		$table->phone = $_POST['phone'];
		$table->province_id = $_POST['province'];
		$table->city_id = $_POST['city'];
		$table->area_id = $_POST['area'];
		$table->street = $_POST['street'];
		$table->postcode = $_POST['zip'];
		$up_array = array('name', 'mobile', 'phone', 'province_id', 'city_id', 'area_id', 'street', 'postcode');
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
}
else if($action == "checkislogin")
{
	if($login_user_id > 0)
	{
		echo 'yeslogin';
	}
	else if($login_user_id == '-1')
	{
		$condition = array( 'sns_nickname' => $_SESSION["mobile"], 'sns_type' => $_SESSION['sns_type'] );
		$aField = DB::LimitQuery('jx_users_sns', array(
			'condition' => $condition,
			'one' => true,
		));
		echo $aField['id'];
		exit;
	}
	else 
	{
		echo 'nologin';
	}
	exit;
}
else if($action == 'dialoglogin')
{
	$id = trim(strval($_GET['id']));
	$detail = isset($_GET['detail'])?trim(strval($_GET['detail'])):'';
	$html = render('ajax_dialog_login');
	json($html, 'dialog');
}
else if($action == 'dialogbindicardpay')
{
	$html = render('ajax_dialog_bindicardpay');
	json($html, 'dialog');
}
else if($action == "dialogsms")
{
	$mobile = trim(strval($_GET['mobile']));
	$type = trim(strval($_GET['type']));
	$html = render('ajax_dialog_sms');
	json($html, 'dialog');
}
else if($action == "dialogrepass")
{
	$mobile = trim(strval($_GET['mobile']));
	$html = render('ajax_dialog_repass');
	json($html, 'dialog');
}
else if($action == "dialogunbindqq")
{
	$html = render('ajax_dialog_unbindqq');
	json($html, 'dialog');
}
else if($action == "dialogunbindweibo")
{
	$html = render('ajax_dialog_unbindweibo');
	json($html, 'dialog');
}
else if($action == "dialogfeedback")
{
	if($login_user_id > 0)
	{
		$login = $login_user_id;
	}
	else 
	{
		$login = 'nologin';
	}
	$html = render('ajax_dialog_feedback');
	json($html, 'dialog');
}
else if($action == 'dialogshareproduct')
{
	$id = trim(strval($_GET['id']));
	$condition = array( 'id' => $id);
	$product = DB::LimitQuery('jx_products', array(
		'condition' => $condition,
		'one' => true,
	));
	
	$product['description']=str_replace("\n","",$product['description']);
	$product['description']=str_replace("\r","",$product['description']);
	$product['description']=str_replace("\r\n","",$product['description']);
	$productweibo = subtostring($product['pname'].','.$product['description'], 50).'我在@三好网 发起了一个链接，查看请点击：';
	$productqone = subtostring($product['pname'].','.$product['description'], 50);
	
	
	$con = array('pid'=>$id);
	//获取商品的图片
	$image = DB::LimitQuery('jx_products_image', array(
		'condition' => $con,
		'order'	=> 'ORDER BY type DESC, id DESC',
	));	
	$html = render('ajax_dialog_shareproduct');
	json($html, 'dialog');
}
else if($action == 'checkbindicardpay')
{
	//获取绑定关系表
	$binding = DB::LimitQuery('jx_bindings', array(
	'condition' => array('mobile' => $_SESSION['mobile']),
	'one'=>true
	));
	//如果有记录，说明已经绑定，则获取用户的支付通信息,否则说明没有绑定
	if(!empty($binding))
	{
		echo 'bindicardpay';
	}
	else 
	{
		echo 'nobindicardpay';
	}
	exit;
}
else if($action == "checkauthor")
{
	$id = trim(strval($_POST['id']));
	$condition = array( 'id' => $id);
	$product = DB::LimitQuery('jx_products', array(
		'condition' => $condition,
		'one' => true,
	));
	if($product['uid'] == $login_user_id)
	{
		echo 'isauthor';
	}
	else 
	{
		//判断购买者是否绑定支付通，如果绑定了，才可以购买
		//获取绑定关系表
		$binding = DB::LimitQuery('jx_bindings', array(
		'condition' => array('mobile' => $_SESSION['mobile']),
		'one'=>true
		));
		//如果有记录，说明已经绑定，则获取用户的支付通信息,否则说明没有绑定
		if(!empty($binding))
		{
			echo 'bindicardpay';
		}
		else 
		{
			echo 'nobindicardpay';
		}
	}
	exit;
}
else if($action == "addorder") 
{
	$sbuyinfo = $_POST['buystrinformation'];
	$abuyinfo = json_decode($sbuyinfo);
	//如果order_id为空，则为生成订单，否则修改订单
	if(empty($abuyinfo->order_id))
	{
		$u['pay_id'] = date('YmdHis').rand(1000, 9999);
		$u['uid'] = $login_user_id;
		$u['sid'] = $abuyinfo->saler_id;
		$u['pid'] = $abuyinfo->product_id;
		$u['quantity'] = $abuyinfo->buy_num;
		$u['price'] = $abuyinfo->product_price;
		$u['property'] = $abuyinfo->product_property;
		$u['origin'] = $abuyinfo->total_price;
		if($abuyinfo->product_express != 0)
		{
			$u['express_price'] = $abuyinfo->product_express;
		}
		else 
		{
			$u['express'] = 'n';
		}
		$u['realname'] = $abuyinfo->address_name;
		$u['mobile'] = $abuyinfo->address_mobile;
		$u['phone'] = $abuyinfo->address_phone;
		$u['province_id'] = $abuyinfo->address_province;
		$u['city_id'] = $abuyinfo->address_city;
		$u['area_id'] = $abuyinfo->address_area;
		$u['street'] = $abuyinfo->address_street;
		$u['postcode'] = $abuyinfo->address_zip;
		$u['remark'] = $abuyinfo->address_buyer;
		$u['createtime'] = time();
		$u['id'] = DB::Insert('jx_orders', $u);
		if($u['id'])
		{
			//如果没有地址ID，则添加保存地址，否则修改地址
			if(empty($abuyinfo->address_id))
			{
				$u_array['uid'] = $login_user_id;
				$u_array['name'] = $abuyinfo->address_name;
				$u_array['mobile'] = $abuyinfo->address_mobile;
				$u_array['phone'] = $abuyinfo->address_phone;
				$u_array['province_id'] = $abuyinfo->address_province;
				$u_array['city_id'] = $abuyinfo->address_city;
				$u_array['area_id'] = $abuyinfo->address_area;
				$u_array['street'] = $abuyinfo->address_street;
				$u_array['postcode'] = $abuyinfo->address_zip;
				$u_array['default_type'] = 1;
				$u_array['createtime'] = time();
				$u_array['id'] = DB::Insert('jx_address', $u_array);
				if($u_array['id'])
				{
					echo $u['id'];
				}
				else 
				{
					echo 'fail';
				}
				exit;
			}
			else 
			{
				$table = new Table('jx_address', $_POST);
				$table->pk_value = $abuyinfo->address_id;
				$table->name = $abuyinfo->address_name;
				$table->mobile = $abuyinfo->address_mobile;
				$table->phone = $abuyinfo->address_phone;
				$table->province_id = $abuyinfo->address_province;
				$table->city_id = $abuyinfo->address_city;
				$table->area_id = $abuyinfo->address_area;
				$table->street = $abuyinfo->address_street;
				$table->postcode = $abuyinfo->address_zip;
				$up_array = array('name', 'mobile', 'phone', 'province_id', 'city_id', 'area_id', 'street', 'postcode');
				$flag = $table->update( $up_array );
				if($flag)
				{
					echo $u['id'];
				}
				else 
				{
					echo 'fail';
				}
				exit;
			}
		}
		else 
		{
			echo 'fail';
			exit;
		}
	}
	else 
	{
		$table = new Table('jx_orders', $_POST);
		$table->pk_value = $abuyinfo->order_id;
		$table->quantity = $abuyinfo->buy_num;
		$table->property = $abuyinfo->product_property;
		$table->origin = $abuyinfo->total_price;
		$table->remark = $abuyinfo->address_buyer;
		$table->realname = $abuyinfo->address_name;
		$table->mobile = $abuyinfo->address_mobile;
		$table->phone = $abuyinfo->address_phone;
		$table->province_id = $abuyinfo->address_province;
		$table->city_id = $abuyinfo->address_city;
		$table->area_id = $abuyinfo->address_area;
		$table->street = $abuyinfo->address_street;
		$table->postcode = $abuyinfo->address_zip;
		$up_array = array('quantity', 'property', 'origin', 'realname', 'mobile', 'phone', 'province_id', 'city_id', 'area_id', 'street', 'postcode', 'remark');
		$flag = $table->update( $up_array );
		if($flag)
		{
			//修改送货地址
			$table_address = new Table('jx_address', $_POST);
			$table_address->pk_value = $abuyinfo->address_id;
			$table_address->name = $abuyinfo->address_name;
			$table_address->mobile = $abuyinfo->address_mobile;
			$table_address->phone = $abuyinfo->address_phone;
			$table_address->province_id = $abuyinfo->address_province;
			$table_address->city_id = $abuyinfo->address_city;
			$table_address->area_id = $abuyinfo->address_area;
			$table_address->street = $abuyinfo->address_street;
			$table_address->postcode = $abuyinfo->address_zip;
			$up_array_address = array('name', 'mobile', 'phone', 'province_id', 'city_id', 'area_id', 'street', 'postcode');
			$flag_address = $table->update( $up_array_address );
			if($flag_address)
			{
				echo $abuyinfo->order_id;
			}
			else 
			{
				echo 'fail';
			}
			exit;
		}
		else 
		{
			echo 'fail';
			exit;
		}
	}
}
else if($action == 'delorder')
{
	$id = $_POST['id'];
	//修改订单的状态
	$table = new Table('jx_orders', $_POST);
	$table->state = 'del';
	$up_array= array('state');
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
else if($action == 'addexpress')
{
	$table = new Table('jx_orders', $_POST);
	$table->pk_value = $_POST['order_id'];
	$table->express_name = $_POST['express_name'];
	$table->express_id = $_POST['express_id'];
	$table->state = 'complete';
	$table->shiptime = time();
	$up_array = array('express_name', 'express_id', 'state', 'shiptime');
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
else if($action == 'detail_cmments')
{
	
	if(isset($login_user_id) && $login_user_id > 0)
	{
		if($_POST){
			$pid = $_POST['pid'];
			$product = Table::Fetch('jx_products', $pid, 'id');
			if($product['uid'] == $login_user_id){
				echo 'onself';			
			}else{
				$str = $_POST['comment'];
				$expre = ZUser::getExpression();
				if(strpos($str,'[') !== false){
					foreach($expre as $exkey => $exval){
						if(strpos($str,$exkey) !== false){
							$str = str_replace($exkey,"<img src=".$exval." title=".$exkey.">",$str);
						}
					}
				}
				$u['pid'] = $_POST['pid'];
				$u['uid'] = $login_user_id;
				$u['comment'] = $str;
				$u['created'] = time();
				$u['id'] = DB::Insert('jx_comments', $u);
				if($u['id']){
					$user = Table::Fetch('jx_users', $login_user_id, 'id');
					$text = '';
					if(!empty($user['headerurl'])){
						$text .= $user['headerurl'];
					}else{
						$text .='/static/images/50.png';
					}
					$user['mobile'] = substr($user['mobile'], 0 ,3).'****'.substr($user['mobile'], 7,4);
					$text .= '|'.$user['mobile'];
					$text .= '|'.date('Y-m-d H:i:s',$u['created']);
					$text .= '|'.$user['nickname'];
					$text .= '|'.$str;
					echo $text;
		// 			var_dump('<pre>',$user);
				}else{
					echo 'failure';
				}
			}
		}else{
			echo 'nocomment';
		}
	}
	else
	{
		echo 'nologin';
	}
	exit;
}
else if($action == 'reply_comments')
{
	//此判断处理部分内容为回复评论信息提交处理,当前已屏蔽
	$id = $_POST['cid'];
	$commentlist = Table::Fetch('jx_comments', $id, 'id');
	if($commentlist){
		$str = $_POST['comment'];
		$expre = ZUser::getExpression();
		if(strpos($str,'[') !== false){
			foreach($expre as $exkey => $exval){
				if(strpos($str,$exkey) !== false){
					$str = str_replace($exkey,"<img src=".$exval." title=".$exkey.">",$str);
				}
			}
		}
		$u['pid'] = $commentlist['pid'];
		$u['uid'] = $login_user_id;
		$u['comment'] = $str;
		$u['created'] = time();
// 					var_dump('<pre>',$u);die;
		
		$u['id'] = DB::Insert('jx_comments', $u);
		if($u['id']){
			$user = Table::Fetch('jx_users', $login_user_id, 'id');
			$text = '';
			if(!empty($user['headerurl'])){
				$text .= $user['headerurl'];
			}else{
				$text .='/static/images/50.png';
			}
			$user['mobile'] = substr($user['mobile'], 0 ,3).'****'.substr($user['mobile'], 7,4);
			$text .= '|'.$user['mobile'];
			$text .= '|'.date('Y-m-d H:i:s',$u['created']);
			$text .= '|'.$user['nickname'];
			$text .= '|'.$str;
			echo $text;
		}else{
			echo 'failure';
		}
	}else{
		echo 'failure';
	}
	
	exit;
}
else if($action == 'getphiz'){
	$phiz = array() ;
	$phizarr = ZUser::getExpression() ;
	$i=0;
	foreach ( $phizarr as $key => $val ){
		$phiz[$i]['title'] = $key ;
		$phiz[$i]['url'] = $val ;
		$i++;
	}
	$data = json_encode( $phiz ) ;
	echo $data;
	exit;
}
?>