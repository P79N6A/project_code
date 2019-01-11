<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');
$arr = getparameter($_POST, 'addorder');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}
$correctdate = DIR_ROOT.'/'.date('Y-m-d');
if(!file_exists( $correctdate ))
{
	@mkdir($correctdate, 0777);
}
file_put_contents(DIR_ROOT.'/'.date('Y-m-d').'/'.'1_'.time().'.txt' , print_r( $_POST , true ) ) ;
//商品ID
$product_id = isset($arr['product_id']) ? intval(trim($arr['product_id'])) : "";
//用户id
$user_id = isset($arr['user_id']) ? intval(trim($arr['user_id'])) : "";
//购买数量
$product_quantity = isset($arr['product_quantity']) ? intval(trim($arr['product_quantity'])) : "";
//商品单价
$product_price = isset($arr['product_price']) ? number_format(addslashes(strval(trim($arr['product_price']))),2) : "";
//订单金额
$order_origin = isset($arr['order_origin']) ? number_format(addslashes(strval(trim($arr['order_origin']))),2) : "";
//商户ID
$product_user_id = isset($arr['product_user_id']) ? intval(trim($arr['product_user_id'])) : "";
$address_id = isset($arr['address_id']) ?intval(trim($arr['address_id'])):'';
if(empty($address_id)){
	$address_name = isset($arr['address_name']) ?strval(trim($arr['address_name'])):'';
	$address_mobile = isset($arr['address_mobile']) ?strval(trim($arr['address_mobile'])):'';
	$address_phone = isset($arr['address_phone']) ?strval(trim($arr['address_phone'])):'';
	$address_province = isset($arr['province_id']) ?intval(trim($arr['province_id'])):'';
	$address_city = isset($arr['city_id']) ?intval(trim($arr['city_id'])):'';
	$address_area = isset($arr['area_id']) ?intval(trim($arr['area_id'])):'';
	$address_street = isset($arr['address_street']) ?strval(trim($arr['address_street'])):'';
	$address_code = isset($arr['address_postcode']) ?strval(trim($arr['address_postcode'])):'';
	$default_type = isset($arr['default_type']) ?intval(trim($arr['default_type'])):0;
}else{
	$address = DB::GetTableRow('jx_address', array( "id=".$address_id));
	$address_name = $address['name'];
	$address_mobile = $address['mobile'];
	$address_phone = $address['phone'];
	$address_province = $address['province_id'];
	$address_city = $address['city_id'];
	$address_area = $address['area_id'];
	$address_street = $address['street'];
	$address_code = $address['postcode'];
	$default_type = $address['default_type'];
}
$address_buyer = isset($arr['address_buyer']) ?strval(trim($arr['address_buyer'])):'';
if(!empty($order_origin) && !empty($product_user_id) && !empty($user_id))
{

	$u['pay_id'] = date('YmdHis').rand(1000, 9999);
	$u['uid'] = $user_id;
	$u['sid'] = $product_user_id;
	$u['pid'] = $product_id;
	$u['quantity'] = $product_quantity;
	$u['price'] = $product_price;
	$u['property'] = $product_property;
	$u['origin'] = $order_origin;
	if($product_express != 0)
	{
		$u['express_price'] = $product_express;
	}
	else
	{
		$u['express'] = 'n';
	}
	$u['realname'] = $address_name;
	$u['mobile'] = $address_mobile;
	$u['phone'] = $address_phone;
	$u['province_id'] = $address_province;
	$u['city_id'] = $address_city;
	$u['area_id'] = $address_area;
	$u['street'] = $address_street;
	$u['postcode'] = $address_code;
	$u['remark'] = $address_buyer;
	$u['createtime'] = time();
	$u['id'] = DB::Insert('jx_orders', $u);
	if($u['id'])
	{
		//如果没有地址ID，则添加保存地址，否则修改地址
		if(empty($address_id))
		{
			$u_array['uid'] = $user_id;
			$u_array['name'] = $address_name;
			$u_array['mobile'] = $address_mobile;
			$u_array['phone'] = $address_phone;
			$u_array['province_id'] = $address_province;
			$u_array['city_id'] = $address_city;
			$u_array['area_id'] = $address_area;
			$u_array['street'] = $address_street;
			$u_array['postcode'] = $address_code;
			$u_array['default_type'] = $default_type;
			$u_array['createtime'] = time();
			$u_array['id'] = DB::Insert('jx_address', $u_array);
		}
// 		else
// 		{
// 			$table = new Table('jx_address', $_POST);
// 			$table->pk_value = $address_id;
// 			$table->name = $address_name;
// 			$table->mobile = $address_mobile;
// 			$table->phone = $address_phone;
// 			$table->province_id = $address_province;
// 			$table->city_id = $address_city;
// 			$table->area_id = $address_area;
// 			$table->street = $address_street;
// 			$table->postcode = $address_code;
// 			$up_array = array('name', 'mobile', 'phone', 'province_id', 'city_id', 'area_id', 'street', 'postcode');
// 			$flag = $table->update( $up_array );
// 		}
		$userinfo = Table::Fetch( 'jx_users' , $product_user_id ) ;
		//获取商品信息
		$product = Table::Fetch( 'jx_products' , $product_id ) ;
		$array['ret'] = 100;
		$array['msg'] = "生成订单成功";
		$array['product_user_name'] = $userinfo['nickname'];
		$array['order_pay_id'] = $u['pay_id'];
		$array['order_origin'] = $order_origin;
		$array['product_name'] = $product['pname'];
		$array['product_mobile'] = empty($userinfo['mobile'])?$userinfo['phone']:$userinfo['mobile'];
		echo json_encode($array);exit;
		
	}
	else
	{
		$array['ret'] = 102;
		$array['msg'] = "生成订单失败";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}