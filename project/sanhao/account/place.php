<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
need_checksns();

$c = array( 'uid' => $login_user_id );
$aAddress = DB::LimitQuery('jx_address' , array(
		'condition' => $c,
		'one'=>true
	));
if(!empty($aAddress))
{
	$condition = array( 'pID' => 0);
	$province = DB::LimitQuery('jx_areas', array(
			'condition' => $condition,
		));
	//查询省份下所属的城市
	$concity = array( 'pID' => $aAddress['province_id'] );
	$city = DB::LimitQuery('jx_areas', array(
			'condition' => $concity,
	));
	//查询城市下所属的地区
	$conarea = array( 'pID' => $aAddress['city_id'] );
	$area = DB::LimitQuery('jx_areas', array(
			'condition' => $conarea,
	));
}
else 
{
	$condition = array( 'pID' => 0);
	$province = DB::LimitQuery('jx_areas', array(
			'condition' => $condition,
		));
}
$pagetitle = '收货地址页';
include template('account_place');
