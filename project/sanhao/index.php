<?php
require_once(dirname(__FILE__) . '/app.php');

$nav = 'index';
$pagetitle = '首页';

$now = time();
$condition = array('status'=>1, "end_time is NULL OR end_time > ".$now, "max_number is NULL or max_number > sale_number");
$count = Table::Count('jx_products', $condition);
if($count > 0)
{
	list($pagesize, $offset, $pagestring) = pagestring($count, 60);
	$aproductlist = DB::LimitQuery('jx_products', array(
			'condition' => $condition,
			'order'=>'ORDER BY orderid DESC,createtime DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	$today = date('md');
	foreach ($aproductlist as $key=>$value)
	{
		$date = date('md', $value['createtime']);
		//当天发布的商品
		if($today == $date)
		{
			$aproductlist[$key]['today'] = 'y';
		}
		else 
		{
			$aproductlist[$key]['today'] = 'n';
		}
		//获取商品的图片
		$cimage = array('pid'=>$value['id'],'type'=>1);
		$aField = DB::LimitQuery('jx_products_image',array(
				'condition'=>$cimage,
				'one'=>true
		));
		$aproductlist[$key]['pic'] = str_replace('product/big', 'product/small', $aField['image']);
	}
}

include template('index');

