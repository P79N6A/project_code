<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

// $_POST = array('product_id'=>86,'product_max_number'=>'345');
$arr = getparameter($_POST, 'modifyingproductinfo');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}
$product_id = isset($arr['product_id']) ? intval(trim($arr['product_id'])) : "";
//isme如果是1，表示没有更新图片，图片路径参数不会传，如果是2，则表示更新了图片
$is_me = isset($arr['is_me']) ? intval(trim($arr['is_me'])) : "";
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
		$product = DB::LimitQuery('jx_products', array(
				'condition' => array('id' => $product_id),
				'one'=>true
		));
		$max_number = $product['sale_number']+addslashes(intval(trim($arr['product_max_number'])));
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
//经度
if(!empty($arr['longitude']))
{
	$longitude = addslashes(strval(trim($arr['longitude'])));
}
else
{
	$longitude = NULL;
}
//纬度
if(!empty($arr['latitude']))
{
	$latitude = addslashes(strval(trim($arr['latitude'])));
}
else
{
	$latitude = NULL;
}
if(!empty($product_id) && !empty($is_me) && !empty($product_name) && !empty($product_price))
{
	if($is_me == 2)
	{
		$table = new Table('jx_products', $_POST);
		$table->pk_value = $product_id;
		$table->pname = $product_name;
		$table->price = $product_price;
		$table->url = $urlold;
		$table->description = $product_desc;
		$table->max_number = $max_number;
		$table->end_time = $end_time;
		$table->express_price = $express_price;
		$table->status = 1;
		
		$up_array = array('pname', 'price', 'description', 'max_number', 'end_time', 'express_price', 'status');
		$flag = $table->update( $up_array );
		if($flag)
		{
			$pimage = DB::LimitQuery('jx_products_image', array(
					'condition' => array('pid' => $product_id),
					'one'=>true
			));
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
			$list = IMG_ROOT.'/'.'product/small'.'/'.$imgtime;
			if(!file_exists( $list ))
			{
				RecursiveMkdir($list);
			}
			//图片进行缩略，大小为300*300
			$url = $list.'/'.$newname;
			$oldimg = $artwork."/".$newname;
			$ret = imageResize($oldimg, 300, 300, $url, true);
			$urlold = 'static/product/big'.'/'.$imgtime.'/'.$newname;
			
			$product_img = new Table('jx_products_image',$_POST);
			$product_img->pk_value = $pimage['id'];
			$product_img->image = $urlold;
			$up_image = array('image');
			$flag2 = $product_img->update( $up_image );
			if($flag2){
				$array['ret'] = 100;
				$array['msg'] = "修改商品成功";
				$array['product_url'] = $INI['system']['imgprefix'].'/'.$urlold;
				echo json_encode($array);exit;
			}else{
				$array['ret'] = 106;
				$array['msg'] = " 图片修改失败";
				echo json_encode($array);exit;
			}
			
		}
		else 
		{
			$array['ret'] = 102;
			$array['msg'] = "修改商品失败";
			echo json_encode($array);exit;
		}
	}
	else 
	{
		$table = new Table('jx_products', $_POST);
		$table->pk_value = $product_id;
		$table->pname = $product_name;
		$table->price = $product_price;
		$table->description = $product_desc;
		$table->max_number = $max_number;
		$table->end_time = $end_time;
		$table->express_price = $express_price;
		$table->status = 1;
		
		$up_array = array('pname', 'price', 'description', 'max_number', 'end_time', 'express_price', 'status');
		$flag = $table->update( $up_array );
		if($flag)
		{
			$array['ret'] = 100;
			$array['msg'] = "修改商品成功";
			echo json_encode($array);exit;
		}
		else 
		{
			$array['ret'] = 102;
			$array['msg'] = "修改商品失败";
			echo json_encode($array);exit;
		}
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}