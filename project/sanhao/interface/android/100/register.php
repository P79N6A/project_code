<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'register');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//手机号
$mobile = isset($arr['merchant_mobile']) ? addslashes(strval(trim($arr['merchant_mobile']))) : '';
//密码
$password = isset($arr['merchant_password']) ? addslashes(strval(trim($arr['merchant_password']))) : '';
//商户姓名
$name = isset($arr['merchant_name']) ? addslashes(strval(trim($arr['merchant_name']))) : "";
//身份证号
$identity = isset($arr['merchant_identity']) ? addslashes(strval(trim($arr['merchant_identity']))) : "";
//身份证正面照片
$front_photo = isset($_FILES['front_photo']) ? $_FILES['front_photo'] : "";
//身份证背面照片
$back_photo = isset($_FILES['back_photo']) ? $_FILES['back_photo'] : "";
//身份证与本人合照照片
$photo = isset($_FILES['photo']) ? $_FILES['photo'] : "";
//省份ID
$province_id = isset($arr['province_id']) ? addslashes(intval(trim($arr['province_id']))) : "";
//城市ID
$city_id = isset($arr['city_id']) ? addslashes(intval(trim($arr['city_id']))) : "";
//地区ID
$area_id = isset($arr['area_id']) ? addslashes(intval(trim($arr['area_id']))) : "";
//详细地址
$address = isset($arr['address']) ? addslashes(strval(trim($arr['address']))) : "";
//设备编号
$device_number = isset($arr['device_number']) ? addslashes(strval(trim($arr['device_number']))) : "";
//开户银行
$bank = isset($arr['bank_id']) ? addslashes(strval(trim($arr['bank_id']))) : "";
//银行卡号
$card_number = isset($arr['card_number']) ? addslashes(strval(trim($arr['card_number']))) : "";
//取现密码
$card_password = isset($arr['card_password']) ? addslashes(strval(trim($arr['card_password']))) : "";

if(!preg_match("/^(1(([358][0-9])|(47)))\d{8}$/",$mobile))
{
	$array['ret'] = 106;
	$array['msg'] = "手机号码格式不正确";
	echo json_encode($array);exit;
}
$merchant_mobile = DB::GetTableRow('mr_merchants', array( "mobile='".$mobile."'"));
if($merchant_mobile)
{
	$array['ret'] = 112;
	$array['msg'] = "该手机号已注册";
	echo json_encode($array);exit;
}
if(!checkIdenCard($identity))
{
	$array['ret'] = 104;
	$array['msg'] = "身份证号格式不正确";
	echo json_encode($array);exit;
}
$merchant = DB::GetTableRow('mr_merchants', array( "identity='".$identity."'"));
if($merchant)
{
	$array['ret'] = 111;
	$array['msg'] = "该身份证号已注册";
	echo json_encode($array);exit;
}
$depassword = Crypt3Des::decrypt($password);
if(!preg_match("/^[A-Za-z0-9_-]{6,20}$/",$depassword))
{
	$array['ret'] = 107;
	$array['msg'] = "密码为6-20位数字,字母,_或-";
	echo json_encode($array);exit;
}
$decard_password = Crypt3Des::decrypt($card_password);
if(!preg_match("/^[A-Za-z0-9_-]{6,20}$/",$decard_password))
{
	$array['ret'] = 108;
	$array['msg'] = "取现密码为6-20位数字,字母,_或-";
	echo json_encode($array);exit;
}
if(!preg_match("/^[0-9]{12,20}$/",$card_number))
{
	$array['ret'] = 109;
	$array['msg'] = "银行卡号为12-20位数字";
	echo json_encode($array);exit;
}
if(!preg_match("/^[0-9]{14,16}$/",$device_number))
{
	$array['ret'] = 110;
	$array['msg'] = "设备号为14-16位数字";
	echo json_encode($array);exit;
}
$merchant_device_number = DB::GetTableRow('mr_merchants', array( "device_number='".$device_number."'"));
if($merchant_device_number)
{
	$array['ret'] = 113;
	$array['msg'] = "该设备号已注册";
	echo json_encode($array);exit;
}

if(!empty($mobile) && !empty($password) && !empty($name) && !empty($identity) && !empty($front_photo) && !empty($back_photo) && !empty($photo) && !empty($province_id) && !empty($city_id) && !empty($area_id) && !empty($address) && !empty($device_number) && !empty($bank) && !empty($card_number) && !empty($card_password))
{
	$imgtime = date('Y-m-d');
	//上传身份证正面图片
	$imgfront = $front_photo['tmp_name'];
	$filenamefront = $front_photo['name'];
	$typefront = $front_photo['type'];
	$imgnamefront = explode('.',$filenamefront);
	$randfront = rand(10000, 999999);
	$md5strfront = md5(time().$randfront);
	$newnamefront = $md5strfront.'.'.$imgnamefront[1];
	$artworkfront = IMG_ROOT.'/'.'merchant/front'.'/'.$imgtime;
	$relinfofront = image_upload($imgfront, $artworkfront, $newnamefront, $typefront, 0, 0, 1);
	$front_photo_url = 'static/merchant/front'.'/'.$imgtime.'/'.$newnamefront;
	if(!file_exists( $artworkfront.'/'.$newnamefront ))
	{
		$array['ret'] = 114;
		$array['msg'] = "身份证正面图片上传失败";
		echo json_encode($array);exit;
	}
	
	//上传身份证背面图片
	$imgback = $back_photo['tmp_name'];
	$filenameback = $back_photo['name'];
	$typeback = $back_photo['type'];
	$imgnameback = explode('.',$filenameback);
	$randback = rand(10000, 999999);
	$md5strback = md5(time().$randback);
	$newnameback = $md5strback.'.'.$imgnameback[1];
	$artworkback = IMG_ROOT.'/'.'merchant/back'.'/'.$imgtime;
	$relinfoback = image_upload($imgback, $artworkback, $newnameback, $typeback, 0, 0, 1);
	$back_photo_url = 'static/merchant/back'.'/'.$imgtime.'/'.$newnameback;
	if(!file_exists( $artworkback.'/'.$newnameback ))
	{
		$array['ret'] = 115;
		$array['msg'] = "身份证背面图片上传失败";
		echo json_encode($array);exit;
	}
	
	//上传身份证与本人合照图片
	$imgphoto = $photo['tmp_name'];
	$filenamephoto = $photo['name'];
	$typephoto = $photo['type'];
	$imgnamephoto = explode('.',$filenamephoto);
	$randphoto = rand(10000, 999999);
	$md5strphoto = md5(time().$randphoto);
	$newnamephoto = $md5strphoto.'.'.$imgnamephoto[1];
	$artworkphoto = IMG_ROOT.'/'.'merchant/photo'.'/'.$imgtime;
	$relinfophoto = image_upload($imgphoto, $artworkphoto, $newnamephoto, $typephoto, 0, 0, 1);
	$photo_url = 'static/merchant/photo'.'/'.$imgtime.'/'.$newnamephoto;
	if(!file_exists( $artworkphoto.'/'.$newnamephoto ))
	{
		$array['ret'] = 116;
		$array['msg'] = "身份证与本人合照图片上传失败";
		echo json_encode($array);exit;
	}
	
	$u['mobile'] = $mobile;
	$u['password'] = $password;
	$u['merchant_name'] = $name;
	$u['identity'] = $identity;
	$u['front_photo'] = $front_photo_url;
	$u['back_photo'] = $back_photo_url;
	$u['photo'] = $photo_url;
	$u['province_id'] = $province_id;
	$u['city_id'] = $city_id;
	$u['area_id'] = $area_id;
	$u['address'] = $address;
	$u['bank'] = $bank;
	$u['card_number'] = $card_number;
	$u['card_password'] = $card_password;
	$u['device_number'] = $device_number;
	$u['createtime'] = time();
	$u['id'] = DB::Insert('mr_merchants', $u);
	if($u['id'])
	{
		//调用MR注册接口
		$app_key = $INI['system']['app_key'];
		$version = '1.0';
		$service_type = 'icardpay.mr.pos.user.reg';
		$mobile = $u['mobile'];
		$login_pwd = $u['password'];
		$settle_pwd = $u['card_password'];
		$real_name = $u['merchant_name'];
		$idcard_no = $u['identity'];
		$cred_img_a = base64_encode(file_get_contents($INI['system']['imgprefix'].'/'.$u['front_photo']));
		$cred_img_b = base64_encode(file_get_contents($INI['system']['imgprefix'].'/'.$u['back_photo']));
		$cred_img_c = base64_encode(file_get_contents($INI['system']['imgprefix'].'/'.$u['photo']));
		$bank_id = $u['bank'];
		$card_no = $u['card_number'];
		$terminal_id = $u['device_number'];
		//系统分配的密匙
		$key = $INI['system']['key'];
		//签名
		$sign = md5($app_key.$bank_id.$card_no.$cred_img_a.$cred_img_b.$cred_img_c.$idcard_no.$login_pwd.$mobile.$real_name.$service_type.$settle_pwd.$terminal_id.$version.$key);
		$url = $INI['system']['url'];
		$data = 'app_key='.$app_key.'&bank_id='.$bank_id.'&card_no='.$card_no.'&cred_img_a='.rawurlencode($cred_img_a).'&cred_img_b='.rawurlencode($cred_img_b).'&cred_img_c='.rawurlencode($cred_img_c).'&idcard_no='.$idcard_no.'&login_pwd='.rawurlencode($login_pwd).'&mobile='.$mobile.'&real_name='.rawurlencode($real_name).'&service_type='.$service_type.'&settle_pwd='.rawurlencode($settle_pwd).'&terminal_id='.$terminal_id.'&version='.$version.'&sign='.$sign;
		define('ORDER_ROOT', str_replace('\\','/',dirname(dirname(dirname(dirname(__FILE__))))));
		$correctdate = ORDER_ROOT.'/log/icardpay/mr/'.date('Y-m-d');
		
		if(!file_exists( $correctdate ))
		{
			@mkdir($correctdate, 0777);
		}
		file_put_contents($correctdate.'/'.$mobile.'_url.txt' , print_r( $url."?".$data , true ) ) ;
		$ret = json_decode(interface_post($url, $data));
		file_put_contents($correctdate.'/'.$mobile.'.txt' , print_r( $ret , true ) ) ;
		//对回传回来的数据验签
		if($ret->rsp_code == '0000')
		{
			//说明商户已在MR那边注册成功，修改商户的状态
			$table = new Table('mr_merchants', $_POST);
			$table->pk_value = $u['id'];
			$table->status = 'y';
			$table->msg = $ret->rsp_msg;
			$up_array = array('status', 'msg');
			$flag = $table->update( $up_array );
			$array['ret'] = 100;
			$array['msg'] = "注册成功";
			$array['user_id'] = $u['id'];
			$array['user_mobile'] = $mobile;
			echo json_encode($array);exit;
		}
		else 
		{
			//说明商户已在MR那边注册失败，修改失败的原因
			$table = new Table('mr_merchants', $_POST);
			$table->pk_value = $u['id'];
			$table->msg = $ret->rsp_msg;
			$up_array = array('msg');
			$flag = $table->update( $up_array );
			$array['ret'] = 100;
			$array['msg'] = "注册成功";
			$array['user_id'] = $u['id'];
			$array['user_mobile'] = $mobile;
			echo json_encode($array);exit;
		}
	}
	else 
	{
		$array['ret'] = 105;
		$array['msg'] = "注册失败";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}


