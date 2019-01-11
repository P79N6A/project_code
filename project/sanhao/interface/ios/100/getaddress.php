<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');
$arr = getparameter($_POST, 'getaddress');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}

//商户ID
$user_id = isset($arr['user_id']) ? addslashes(strval(trim($arr['user_id']))) : "";
$type = isset($arr['type']) ? addslashes(strval(trim($arr['type']))) : "get";
$province_id = isset($arr['province_id']) ? intval(trim($arr['province_id'])) : "";
$city_id = isset($arr['city_id']) ? intval(trim($arr['city_id'])) : "";
$area_id = isset($arr['area_id']) ? intval(trim($arr['area_id'])) : "";
$default_type = isset($arr['default_type']) ? intval(trim($arr['default_type'])) :0;
$postcode = isset($arr['postcode']) ? intval(trim($arr['postcode'])) : "";
$aid = isset($arr['aid']) ? intval(trim($arr['aid'])) : "";
$street = isset($arr['street']) ?strval(trim($arr['street'])):'';
$mobile = isset($arr['mobile']) ?strval(trim($arr['mobile'])):'';
$name = isset($arr['name']) ?strval(trim($arr['name'])):'';
if(!empty($user_id) && $type == 'get')
{
	$condition = array( 'uid' => $user_id);
	$address = DB::LimitQuery('jx_address', array(
		'condition' => $condition,
	));
// 	$areas = DB::LimitQuery('jx_areas', array(
// 			'condition' => $condition,
// 	));
	
	if(!empty($address))
	{
		$array['ret'] = 100;
		$array['msg'] = "地址详情";
		$array['areas'] = $address;
// 		var_dump($array);die;
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "无地址详细信息";
		echo json_encode($array);exit;
	}
}
else if(!empty($user_id) && $type == 'add' && !empty($province_id) && !empty($name) && !empty($mobile) && !empty($street) && !empty($postcode) && !empty($area_id) && !empty($city_id) && !empty($province_id))
{
	$u['uid'] = $user_id;
	$u['name'] = $_POST['name'];
	$u['mobile'] = $_POST['mobile'];
	$u['phone'] = $_POST['phone'];
	$u['province_id'] = $_POST['province_id'];
	$u['city_id'] = $_POST['city_id'];
	$u['area_id'] = $_POST['area_id'];
	$u['street'] = $_POST['street'];
	$u['postcode'] = $_POST['postcode'];
	$u['default_type'] = $_POST['default_type'];
	$u['createtime'] = time();
// 	var_dump('<pre>',$u);die;
	$u['id'] = DB::Insert('jx_address', $u);
	if($u['id']){
		$array['ret'] = 100;
		$array['msg'] = "添加成功";
		echo json_encode($array);exit;
	}else{
		$array['ret'] = 104;
		$array['msg'] = "添加失败";
		echo json_encode($array);exit;
	}
}
else if(!empty($user_id) && $type == 'update' && !empty($province_id) && !empty($aid) && !empty($name) && !empty($mobile) && !empty($street) && !empty($postcode) && !empty($area_id) && !empty($city_id) && !empty($province_id)){
	$id = intval($_POST['aid']);
	if(!empty($id)){
		$table = new Table('jx_address', $_POST);
		$table->pk_value = $id;
		$table->name = $_POST['name'];
		$table->mobile = $_POST['mobile'];
		$table->phone = $_POST['phone'];
		$table->province_id = $_POST['province_id'];
		$table->city_id = $_POST['city_id'];
		$table->area_id = $_POST['area_id'];
		$table->street = $_POST['street'];
		$table->postcode = $_POST['postcode'];
		$table->default_type = $_POST['default_type'];
		$up_array = array('name', 'mobile', 'phone', 'province_id', 'city_id', 'area_id', 'street', 'postcode', 'default_type');
		$flag = $table->update( $up_array );
		if($flag){
			$array['ret'] = 100;
			$array['msg'] = "修改成功";
			echo json_encode($array);exit;
		}else{
			$array['ret'] = 105;
			$array['msg'] = "修改失败";
			echo json_encode($array);exit;
		}
	}else{
		$array['ret'] = 103;
		$array['msg'] = "参数错误";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}