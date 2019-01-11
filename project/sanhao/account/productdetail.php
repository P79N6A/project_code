<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$id = intval($_GET['id']);

$condition = array( 'id' => $id);
$product = DB::LimitQuery('jx_products', array(
	'condition' => $condition,
	'one' => true,
));

$now = time();
$diff_time = $left_time = $product['end_time'] - $now;
$left_day = floor($diff_time/86400);
$left_time = $left_time % 86400;
$left_hour = floor($left_time/3600);
$left_time = $left_time % 3600;
$left_minute = floor($left_time/60);
$left_time = $left_time % 60;
if(!empty($product))
{
	$con = array('pid'=>$id);
	//获取商品的图片
	$image = DB::LimitQuery('jx_products_image', array(
		'condition' => $con,
		'order'	=> 'ORDER BY type DESC, id DESC',
	));	
	//获取商品的属性
	$property = DB::LimitQuery('jx_products_property', array(
		'condition' => $con
	));	
	if(!empty($property))
	{
		foreach ($property as $k=>$v)
		{
			$arrcontent = explode(' ',$v['content']);
			$property[$k]['size'] = $arrcontent;
		}
	}
	
	//获取卖家的信息
	$consaler = array( 'id' => $product['uid']);
	$user = DB::LimitQuery('jx_users', array(
		'condition' => $consaler,
		'one'=>true
	));	
	
	//获取卖家的头像
	if($user['headerurl'] != '')
	{
		$user['image'] = str_replace('user/old', 'user/big', $user['headerurl']);
	}
	if($user['type'] == 1)
	{
		$user['email'] = '****'.substr($user['email'], 4);
	}
	else 
	{
		$user['mobile'] = substr($user['mobile'], 0 ,3).'****'.substr($user['mobile'], 7,4);
	}
	//获取商品的评论内容
	$count = Table::Count('jx_comments', $con);
	list($pagesize, $offset, $pagestring) = pagestring($count, 10);
	
	$procomments = DB::LimitQuery('jx_comments', array(
			'condition' => $con,
			'order' => 'ORDER BY id DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	$ucid = '';
	foreach($procomments as $ckey => $cval){
		$ucid = $cval['uid'];
		$procomments[$ckey]['created'] = date('Y-m-d H:i:s',$cval['created']);
	}
	if(!empty($ucid)){
		$ucon = array( "id in ('".$ucid."')");
		$userlists = DB::LimitQuery('jx_users', array(
				'condition' => $ucon,
		));
		foreach($userlists as $ukey => $uval){
			if(empty($uval['headerurl'])){
				$userlists[$ukey]['headerurl'] ='/static/images/50.png';
			}
			$userlists[$ukey]['mobile'] = substr($uval['mobile'], 0 ,3).'****'.substr($uval['mobile'], 7,4);
		}
	}
	
// 	var_dump('<pre>',$userlists);die;
	//获取卖家的其他商品
	$c = array("uid=".$product['uid']." and id<>$id and status=1");
	$other = DB::LimitQuery('jx_products', array(
	'condition' => $c,
	'order' => 'ORDER BY sale_number DESC, id DESC',
	'size' => 4,
	));
	if(!empty($other))
	{
		foreach ($other as $key=>$value)
		{
			$cimage = array('pid'=>$value['id'],'type'=>1);
			$aField = DB::LimitQuery('jx_products_image',array(
			'condition'=>$cimage,
			'one'=>true
			));
			$other[$key]['image'] = $aField['image'];
			$other[$key]['title'] = subtostring($other[$key]['pname'], 7);
		}
	}
}
else 
{
	redirect(WEB_ROOT .'/error.php');
}
$product['description']=str_replace("\n","",$product['description']);
$product['description']=str_replace("\r","",$product['description']);
$product['description']=str_replace("\r\n","",$product['description']);
$productweibo = subtostring($product['pname'].','.$product['description'], 50).'我在@三好网 发起了一个链接，查看请点击：';
$productqone = subtostring($product['pname'].','.$product['description'], 50);

$pagetitle = $product['pname'];
include template('account_productdetail');