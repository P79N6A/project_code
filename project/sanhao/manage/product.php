<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(dirname(__FILE__)) . '/include/function/right.php');

need_manager();
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "list";

if($action == "list")
{
	/**************在售商品列表*******************/
	//商品名称
	$productname = isset($_REQUEST['productname']) ? $_REQUEST['productname'] : "";
	//商品价格最小值
	$smallprice = isset($_REQUEST['smallprice']) ? $_REQUEST['smallprice'] : "";
	//商品价格最大值
	$bigprice = isset($_REQUEST['bigprice']) ? $_REQUEST['bigprice'] : "";
	//起始时间
	$begintime = isset($_REQUEST['begintime']) ? $_REQUEST['begintime'] : "";
	//终止时间
	$endtime = isset($_REQUEST['endtime']) ? $_REQUEST['endtime'] : "";
	$now = time();
	$condition = array('status'=>1, "end_time is NULL OR end_time > ".$now, "max_number is NULL or max_number > sale_number");
	if(!empty($productname))
	{
		$condition[] = "pname like '%".$productname."%'";
	}
	if(!empty($smallprice))
	{
		$condition[] = "price >= $smallprice";
	}
	if(!empty($bigprice))
	{
		$condition[] = "price <= $bigprice";
	}
	if(!empty($begintime))
	{
		$condition[] = "createtime >= $begintime";
	}
	if(!empty($endtime))
	{
		$condition[] = "createtime <= $endtime";
	}
	
	$count = Table::Count('jx_products', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	
	$product = DB::LimitQuery('jx_products', array(
		'condition' => $condition,
		'order' => 'ORDER BY orderid DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	foreach ($product as $key=>$value)
	{
		$cimage = array('pid'=>$value['id'],'type'=>1);
		$aField = DB::LimitQuery('jx_products_image',array(
				'condition'=>$cimage,
				'one'=>true
		));
		$product[$key]['pic'] = str_replace('product/big', 'product/small', $aField['image']);
	}
	$menucolor = '在线商品';
	$func = 'list';
	include template('manage_product_index');
}
elseif($action == "detail")
{
	/**********在售商品详情************/
	$id = strval($_GET['id']);
	$func = strval($_GET['func']);
	$product = DB::LimitQuery('jx_products', array(
		'condition'=>array('id'=>$id),
		'one'=>true,
	));
	$productimage = DB::LimitQuery('jx_products_image', array(
		'condition'=>array('pid'=>$id),
	));
	//查询卖家的个人信息和支付通账户
	$user = DB::LimitQuery('jx_users', array(
		'condition'=>array('id'=>$product['uid']),
		'one'=>true,
	));
	$usericardpay = DB::LimitQuery('jx_bindings', array(
		'condition'=>array('mobile'=>$user['mobile']),
		'one'=>true,
	));
// 	var_dump('<pre>',$productimage);die;
	if($func == 'list'){
		$menucolor = '在线商品';
	}else if($func == 'offlist'){
		$menucolor = '下架商品';
	}else if($func == 'draftlist'){
		$menucolor = '草稿商品';
	}else if($func == 'recyclelist'){
		$menucolor = '商品回收站';
	}
	include template('manage_product_detail');
}
elseif($action == "offlist")
{
	/**************下架商品列表*******************/
	//商品名称
	$productname = isset($_REQUEST['productname']) ? $_REQUEST['productname'] : "";
	//商品价格最小值
	$smallprice = isset($_REQUEST['smallprice']) ? $_REQUEST['smallprice'] : "";
	//商品价格最大值
	$bigprice = isset($_REQUEST['bigprice']) ? $_REQUEST['bigprice'] : "";
	//起始时间
	$begintime = isset($_REQUEST['begintime']) ? $_REQUEST['begintime'] : "";
	//终止时间
	$endtime = isset($_REQUEST['endtime']) ? $_REQUEST['endtime'] : "";
	$now = time();
	$condition = array("(status = 3 or end_time <= ".$now. " or max_number <= sale_number and max_number > 0) and status != 4");
	if(!empty($productname))
	{
		$condition[] = "pname like '%".$productname."%'";
	}
	if(!empty($smallprice))
	{
		$condition[] = "price >= $smallprice";
	}
	if(!empty($bigprice))
	{
		$condition[] = "price <= $bigprice";
	}
	if(!empty($begintime))
	{
		$condition[] = "createtime >= $begintime";
	}
	if(!empty($endtime))
	{
		$condition[] = "createtime <= $endtime";
	}
	$count = Table::Count('jx_products', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	
	$product = DB::LimitQuery('jx_products', array(
		'condition' => $condition,
		'order' => 'ORDER BY orderid DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	foreach ($product as $key=>$value)
	{
		$cimage = array('pid'=>$value['id'],'type'=>1);
		$aField = DB::LimitQuery('jx_products_image',array(
				'condition'=>$cimage,
				'one'=>true
		));
		$product[$key]['pic'] = str_replace('product/big', 'product/small', $aField['image']);
	}
	$menucolor = '下架商品';
	$func = 'offlist';
	include template('manage_product_index');
}
elseif($action == "draftlist")
{
	/**************草稿商品列表*******************/
	//商品名称
	$productname = isset($_REQUEST['productname']) ? $_REQUEST['productname'] : "";
	//商品价格最小值
	$smallprice = isset($_REQUEST['smallprice']) ? $_REQUEST['smallprice'] : "";
	//商品价格最大值
	$bigprice = isset($_REQUEST['bigprice']) ? $_REQUEST['bigprice'] : "";
	//起始时间
	$begintime = isset($_REQUEST['begintime']) ? $_REQUEST['begintime'] : "";
	//终止时间
	$endtime = isset($_REQUEST['endtime']) ? $_REQUEST['endtime'] : "";
	$now = time();
	$condition = array('status'=>2);
	if(!empty($productname))
	{
		$condition[] = "pname like '%".$productname."%'";
	}
	if(!empty($smallprice))
	{
		$condition[] = "price >= $smallprice";
	}
	if(!empty($bigprice))
	{
		$condition[] = "price <= $bigprice";
	}
	if(!empty($begintime))
	{
		$condition[] = "createtime >= $begintime";
	}
	if(!empty($endtime))
	{
		$condition[] = "createtime <= $endtime";
	}
	
	$count = Table::Count('jx_products', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	
	$product = DB::LimitQuery('jx_products', array(
		'condition' => $condition,
		'order' => 'ORDER BY orderid DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	foreach ($product as $key=>$value)
	{
		$cimage = array('pid'=>$value['id'],'type'=>1);
		$aField = DB::LimitQuery('jx_products_image',array(
				'condition'=>$cimage,
				'one'=>true
		));
		$product[$key]['pic'] = str_replace('product/big', 'product/small', $aField['image']);
	}
	$menucolor = '草稿商品';
	$func = 'draftlist';
	include template('manage_product_index');
}
elseif($action == "recyclelist")
{
	/**************回收站商品列表*******************/
	//商品名称
	$productname = isset($_REQUEST['productname']) ? $_REQUEST['productname'] : "";
	//商品价格最小值
	$smallprice = isset($_REQUEST['smallprice']) ? $_REQUEST['smallprice'] : "";
	//商品价格最大值
	$bigprice = isset($_REQUEST['bigprice']) ? $_REQUEST['bigprice'] : "";
	//起始时间
	$begintime = isset($_REQUEST['begintime']) ? $_REQUEST['begintime'] : "";
	//终止时间
	$endtime = isset($_REQUEST['endtime']) ? $_REQUEST['endtime'] : "";
	$now = time();
	$condition = array('status'=>4);
	if(!empty($productname))
	{
		$condition[] = "pname like '%".$productname."%'";
	}
	if(!empty($smallprice))
	{
		$condition[] = "price >= $smallprice";
	}
	if(!empty($bigprice))
	{
		$condition[] = "price <= $bigprice";
	}
	if(!empty($begintime))
	{
		$condition[] = "createtime >= $begintime";
	}
	if(!empty($endtime))
	{
		$condition[] = "createtime <= $endtime";
	}
	
	$count = Table::Count('jx_products', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	
	$product = DB::LimitQuery('jx_products', array(
		'condition' => $condition,
		'order' => 'ORDER BY orderid DESC',
		'size' => $pagesize,
		'offset' => $offset,
	));
	foreach ($product as $key=>$value)
	{
		$cimage = array('pid'=>$value['id'],'type'=>1);
		$aField = DB::LimitQuery('jx_products_image',array(
				'condition'=>$cimage,
				'one'=>true
		));
		$product[$key]['pic'] = str_replace('product/big', 'product/small', $aField['image']);
	}
	$menucolor = '商品回收站';
	$func = 'recyclelist';
	include template('manage_product_index');
}
elseif($action == "placedrecycle")
{
	$id = $_POST['chk'];
	//循环下架
	$idarr = explode(',',$id);
	foreach ($idarr as $ikey => $ival){
		$uarray = array( 'status' => 4 );
		$productupdate = Table::UpdateCache('jx_products', $ival, $uarray);
	}
	
	if($productupdate){
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'下架了在售商品id为'.$id.'的商品成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'success';
	}else{
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'下架了在售商品id为'.$id.'的商品失败';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'failure';
	}
	// 	redirect( WEB_ROOT . '/manage/product.php?action=list');
}
elseif($action == "delproduct")
{
	/**************删除在售商品列表页面的商品*******************/
	$id = $_POST['chk'];
	//循环删除
	$idarr = explode(',',$id);
	foreach ($idarr as $ikey => $ival){
		$prochk = Table::Delete('jx_products', $ival);
	}
	if($prochk){
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'删除了回收站中id为'.$id.'的商品成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'success';
	}else{
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'删除了回收站中id为'.$id.'的商品失败';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'failure';
	}
	exit;
}
elseif($action == "offproduct")
{
	/**************下架在售商品列表页面的商品*******************/
	$id = $_POST['chk'];
	//循环下架
	$idarr = explode(',',$id);
	foreach ($idarr as $ikey => $ival){
		$uarray = array( 'status' => 3 );
		$productupdate = Table::UpdateCache('jx_products', $ival, $uarray);
	}
	if($productupdate){
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'删除了下架商品id为'.$id.'的商品成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'success';
	}else{
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'删除了下架商品id为'.$id.'的商品成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'failure';
	}
// 	redirect( WEB_ROOT . '/manage/product.php?action=list');
}
elseif($action == "reduction")
{
	/**************还原商品回收站页面的商品*******************/
	$id = $_POST['chk'];
	//循环上架
	$idarr = explode(',',$id);
	foreach ($idarr as $ikey => $ival){
		$uarray = array( 'status' => 1 );
		$productupdate = Table::UpdateCache('jx_products', $ival, $uarray);
	}
	if($productupdate){
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'上架了id为'.$id.'的商品成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'success';
	}else{
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'上架了id为'.$id.'的商品成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'failure';
	}
	
}
elseif ($action == "updatesort"){
	$id = strval($_POST['id']);
	$table = new Table('jx_products', $_POST);
	$table->pk_value = $id;
	$table->orderid = $_POST['mealnumber'];
	$up_array = array('orderid');
	$flag = $table->update( $up_array );
	if($flag){
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'修改了id为'.$id.'的商品排序成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'success';
	}else{
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'修改了id为'.$id.'的商品排序成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'failure';
	}
	exit;
}else if ($action == 'productban'){
	$id = $_POST['chk'];
	//循环删除
	$idarr = explode(',',$id);
	foreach ($idarr as $ikey => $ival){
		Table::Delete('jx_products', $ival);
		Table::Delete('jx_products_image', $ival, 'pid');
		Table::Delete('jx_products_property', $ival, 'pid');
	}
	echo 'success';exit;
}