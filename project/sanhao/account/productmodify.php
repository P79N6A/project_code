<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
if( $_POST )
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
	$table->description =$aproduct->productdescription;
	$table->price = $aproduct->price;
	if(!empty($aproduct->max_number))
	{
		if(!empty($product['max_number']))
		{
			if(!empty($product['sale_number'])){
				if(($product['max_number'] == $aproduct->max_number) && ($product['sale_number'] == $aproduct->max_number) && ($product['max_number'] == $product['sale_number']))
				{
					$table->max_number = $product['max_number']+$aproduct->max_number;
				}
				else if(($product['max_number'] != $aproduct->max_number))
				{
					$table->max_number = $product['sale_number']+$aproduct->max_number;
				}
			}else{
				$table->max_number = $aproduct->max_number;
			}
		}
		else 
		{
			if(!empty($product['sale_number'])){
				$table->max_number = $product['sale_number']+$aproduct->max_number;
			}
			else
			{
				$table->max_number = $aproduct->max_number;
			}
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
	$table->status = 1;
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
		echo 'success';
	}
	else 
	{
		echo 'fail';
	}
	exit;
}
$id = intval($_GET['id']);
$condition = array( 'id' => $id);
$product = DB::LimitQuery('jx_products', array(
	'condition' => $condition,
	'one' => true,
));
if($product['end_time'] != '')
{
	$product['endtime'] = date('Y-m-d',$product['end_time']);
}


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
	$picarr = array();
	if(!empty($image))
	{
		foreach ($image as $key=>$value)
		{
			$picarr[$key]['id'] = $value['id'];
			$picarr[$key]['picurl'] = $value['image'];
			$picarr[$key]['type'] = $value['type'];
		}
	}
	//获取商品的属性
	$property = DB::LimitQuery('jx_products_property', array(
		'condition' => $con
	));	
	$propertyarr = array();
	$icount = count($property);
	if($icount == 1)
	{
		$propertyarr[0]['id'] = 'one';
		$propertyarr[0]['name'] = $property[0]['name'];
		$propertyarr[0]['content'] = $property[0]['content'];
	}
	else if($icount == 2)
	{
		$propertyarr[0]['id'] = 'one';
		$propertyarr[0]['name'] = $property[0]['name'];
		$propertyarr[0]['content'] = $property[0]['content'];
		$propertyarr[1]['id'] = 'two';
		$propertyarr[1]['name'] = $property[1]['name'];
		$propertyarr[1]['content'] = $property[1]['content'];
	}

	$productarr = array();
	if(!empty($product['pname']))
	{
		$productarr['productname'] = $product['pname'];
	}
	if(!empty($product['description']))
	{
		$productarr['productdescription'] = str_replace( '\r\n' , '' , mysql_escape_string( $product['description'] ) );
	}
	if(!empty($product['price']))
	{
		$productarr['price'] = $product['price'];
	}
	$productarr['image'] = $picarr;
	if(!empty($product['max_number']))
	{
		if($product['max_number'] == $product['sale_number'])
		{
			$productarr['max_number'] = 0;
		}
		else 
		{
			$productarr['max_number'] = $product['max_number']-$product['sale_number'];
		}
	}
	if(!empty($product['old_price']))
	{
		$productarr['old_price'] = $product['old_price'];
	}
	if(!empty($product['express_price']))
	{
		$productarr['express_price'] = $product['express_price'];
	}
	if(!empty($product['end_time']))
	{
		$productarr['end_time'] = $product['end_time'];
	}
	$productarr['property'] = $propertyarr;
	$sproduct = json_encode($productarr);
}
else 
{
	redirect(WEB_ROOT .'/index.php');
}

$pagetitle = '编辑商品';
include template('account_productmodify');