<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

$arr = getparameter($_POST, 'addproduct');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//商户ID
$merchant_id = isset($arr['merchant_id']) ? addslashes(strval(trim($arr['merchant_id']))) : "";
//商品图片
$product_url = isset($_FILES['product_url']) ? $_FILES['product_url'] : "";
//商品名称
$product_name = isset($arr['product_name']) ? addslashes(strval(trim($arr['product_name']))) : "";
//商品价格
$product_price = isset($arr['product_price']) ? addslashes(strval(trim($arr['product_price']))) : "";
if(!preg_match("/^\d{0,8}\.{0,1}(\d{1,2})?$/",$product_price))
{
	$array['ret'] = 104;
	$array['msg'] = "商品价格格式不正确";
	echo json_encode($array);exit;
}
//截止时间
if(!empty($arr['product_end_time']))
{
	$end_time =  strtotime(trim($arr['product_end_time']."23:59:59"));
}
else 
{
	$end_time = NULL;
}
//可售数量
if(!empty($arr['product_max_number']))
{
	if(!preg_match("/^[0-9]*[1-9][0-9]*$/",$arr['product_max_number']))
	{
		$array['ret'] = 103;
		$array['msg'] = "商品可售数量格式不正确";
		echo json_encode($array);exit;
	}
	else 
	{
		$max_number = addslashes(strval(trim($arr['product_max_number'])));
	}
}
else 
{
	$max_number = NULL;
}
//快递费用
if(!empty($arr['product_express_price']))
{
	if(!preg_match("/^\d{0,8}\.{0,1}(\d{1,2})?$/",$arr['product_express_price']))
	{
		$array['ret'] = 105;
		$array['msg'] = "快递费用格式不正确";
		echo json_encode($array);exit;
	}
	else
	{
		$express_price = addslashes(strval(trim($arr['product_express_price'])));
	}
}
else 
{
	$express_price = NULL;
}
//描述
if(!empty($arr['product_desc']))
{
	$product_desc = addslashes(strval(trim($arr['product_desc'])));
}
else 
{
	$product_desc = NULL;
}

if(!empty($merchant_id) && !empty($product_url) && !empty($product_name) && !empty($product_price))
{
	//图片上传
	$img = $product_url['tmp_name'];
	$filename = $product_url['name'];
	$type = $product_url['type'];
	$imgname = explode('.',$filename);
	$imgtime = date('Y-m-d');
	$rand = rand(10000, 999999);
	$md5str = md5(time().$rand);
	$newname = $md5str.'.'.$imgname[1];
	$artwork = IMG_ROOT.'/'.'product/big'.'/'.$imgtime;
	//上传原图
	$relinfo4 = image_upload($img, $artwork, $newname, $type, 0, 0, 1);
	if(!file_exists( $artwork.'/'.$newname ))
	{
		$array['ret'] = 106;
		$array['msg'] = "图片上传失败";
		echo json_encode($array);exit;
	}
	$list = IMG_ROOT.'/'.'product/small'.'/'.$imgtime;
	if(!file_exists( $list ))
	{
		RecursiveMkdir($list);
	}
	//图片进行缩略，大小为300*300
	$url = $list.'/'.$newname;
	$oldimg = $artwork."/".$newname;
	$ret = imageResize($oldimg, 300, 300, $url, true);
	$urlold = 'static/product/small'.'/'.$imgtime.'/'.$newname;
	
	$u['mid'] = $merchant_id;
	$u['name'] = $product_name;
	$u['price'] = $product_price;
	$u['url'] = $urlold;
	$u['desc'] = $product_desc;
	$u['type'] = 2;
	$u['max_number'] = $max_number;
	$u['end_time'] = $end_time;
	$u['express_price'] = $express_price;
	$u['number'] = "P100".rand(10000,99999);
	$u['comefrom'] = 2;
	$u['createtime'] = time();
	$u['id'] = DB::Insert('mr_products', $u);
	if($u['id'])
	{
		$array['ret'] = 100;
		$array['msg'] = "商品添加成功";
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "商品添加失败";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}