<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/include/appconfig.php');

// $_POST = array('user_id'=>23,'requesttime'=>'1384412165','type'=>'more');//,'product_id'=>50,'requesttime'=>'1384412165','type'=>'refresh'
$arr = getparameter($_POST, 'getorderlist');
if($arr['ret'] != 100) {
	exit(json_encode($arr));
}
$correctdate = DIR_ROOT.'/'.date('Y-m-d');
if(!file_exists( $correctdate ))
		{
			@mkdir($correctdate, 0777);
		}
	file_put_contents(DIR_ROOT.'/'.date('Y-m-d').'/'.'1_'.time().'.txt' , print_r( $_POST , true ) ) ;
//商户ID
$user_id = isset($arr['user_id']) ? addslashes(intval(trim($arr['user_id']))) : "";
$order_id = isset($arr['product_id']) ? addslashes(intval(trim($arr['product_id']))) : "";
//如果不传时间则默认调用当前的时间
$now = time();
if(!empty($arr['requesttime']))
{
	$begintime = trim($arr['requesttime']);
}
else 
{
	$begintime = $now;
}
//滑动方式，如果不传值，默认为more,往下拉，刷新refresh
$type = isset($arr['type']) ? strval(trim($arr['type'])) : "more";

if(!empty($begintime) && !empty($type) )
{
	if(!empty($order_id)){
		$condition[] = "pid = $order_id";
	}
	if($type == 'more')
	{
		$condition[] = "createtime < $begintime";
		$condition[] = "sid= $user_id";
	}
	else 
	{
		$condition[] = "createtime > $begintime";
		$condition[] = "sid= $user_id";
	}
	$orders = DB::LimitQuery('jx_orders', array(
		'condition' => $condition,
		'order' => 'ORDER BY createtime DESC',
		'size' => 20,
	));
	
	if(!empty($orders))
	{
		$parr = array();
		$uarr = array();
		foreach($orders as $okey =>$oval){
			if(!in_array($oval['pid'],$parr)){
				array_push($parr,$oval['pid']);
			}
			if(!in_array($oval['uid'],$uarr)){
				array_push($uarr,$oval['uid']);
			}
		}
		if(!empty($parr)){
			$strid = implode(',',$parr);
			$con[] = "id in ( $strid )";
			$con2[] = "pid in ( $strid )";
			$productlist = DB::LimitQuery('jx_products', array(
					'condition' => $con,
					'order' => 'ORDER BY createtime DESC',
					'size' => 20,
			));
			
			$cimage = array("pid in ( $strid ) and type=1");
			$aField = DB::LimitQuery('jx_products_image',array(
					'condition'=>$cimage,
			));
		}
		if(!empty($uarr)){
			$uid = implode(',',$uarr);
			$ucon[] = "id in ( $uid )";
			$userlist = DB::LimitQuery('jx_users', array(
					'condition' => $ucon,
					'order' => 'ORDER BY createtime DESC',
					'size' => 20,
			));
		}
		$alist = array();
		foreach ($orders as $key=>$value)
		{
			$alist[$key]['order_id'] = $value['id'];
			$alist[$key]['pay_id'] = $value['pay_id'];
			$alist[$key]['order_price'] = $value['origin'];
			foreach($productlist as $pkey => $pval){
				if($value['pid'] == $pval['id']){
					$alist[$key]['product_name'] = $pval['pname'];
					$alist[$key]['product_desc'] = $pval['description'];
					$alist[$key]['product_price'] = $pval['price'];
				}
			}
			foreach($aField as $akey =>$aval){
				if($aval['pid'] == $value['pid']){
					$alist[$key]['product_url'] = $INI['system']['imgprefix'].'/'.str_replace('product/big', 'product/small', $aval['image']);
				}
			}
			foreach($userlist as $ukey =>$uval){
				if($uval['id'] == $value['uid']){
					$alist[$key]['buyers_nickname'] = $uval['nickname'];
					$alist[$key]['buyers_url'] = $INI['system']['imgprefix'].'/'.$uval['headerurl'];
				}
			}
			$alist[$key]['order_realname'] = $value['realname'];
			$alist[$key]['order_mobile'] = $value['mobile'];
			$alist[$key]['order_phone'] = $value['phone'];
			$alist[$key]['province_id'] = $value['province_id'];
			$alist[$key]['city_id'] = $value['city_id'];
			$alist[$key]['area_id'] = $value['area_id'];
			$alist[$key]['order_street'] = $value['street'];
			$alist[$key]['postcode'] = $value['postcode'];
			$alist[$key]['express_name'] = $value['express_name'];
			$alist[$key]['express_id'] = $value['express_id'];
			$alist[$key]['remark'] = $value['remark'];
			$alist[$key]['order_number'] = $value['quantity'];	
			$alist[$key]['order_status'] = $value['state'];	
			$alist[$key]['order_create_time'] = $value['createtime'];
			
		}
// 		var_dump('<pre>',$orders);die;
		$array = array(
			'ret' => 100,
			'msg' => '订单列表',
			'product_list' => $alist
		);
		echo json_encode($array);exit;
	}
	else 
	{
		$array['ret'] = 102;
		$array['msg'] = "无订单数据";
		echo json_encode($array);exit;
	}
}
else 
{
	$array['ret'] = 101;
	$array['msg'] = "参数错误";
	echo json_encode($array);exit;
}