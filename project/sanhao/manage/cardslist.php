<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(dirname(__FILE__)) . '/include/function/right.php');

need_manager();
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "list";

if($action == "list")
{
	$condition = array('status = 0');
	$chk = isset($_POST['chk'])?$_POST['chk']:'';
	if(!empty($chk)){
		$condition[] = " id in (".$chk.")";
	}
	$cno = isset($_POST['cno'])?$_POST['cno']:'';
	$startime = isset($_POST['startime'])?strtotime($_POST['startime']):'';
	$endtime = isset($_POST['endtime'])?strtotime($_POST['endtime']):'';
	if(!empty($startime)){
		$newstartime = date('Y-m-d',$startime);
	}
	if(!empty($endtime)){
		$newendtime = date('Y-m-d',$endtime);
	}
	if(!empty($cno)){
		$condition[] = "cno = $cno";
	}
	if(!empty($startime)){
		$condition[] = "startime >= $startime";
	}
	if(!empty($endtime)){
		$condition[] = "endtime = $endtime";
	}
	$count = Table::Count('jx_cards', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	$cards = DB::LimitQuery('jx_cards', array(
			'condition' => $condition,
			'order' => 'ORDER BY id DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
// 	var_dump('<pre>',$condition);die;
	foreach($cards as $mkey => $mval){
		$cards[$mkey]['created'] = date('Y-m-d H:i:s',$mval['created']); 
	}
	if ( strval($_GET['download']))
	{
		$name = "cards_".date('Ymd');
		$kn = array(
				'cno' => '卡号',
				'code' => '卡附加码',
		);
		foreach($cards AS $cid => $one) {
			$one['cno'] = $one['cno'];
			$cno = '';
			for($i=0; $i<strlen($one['code']);$i++){
				$cno .= ord($one['code'][$i]);
			}
			$cards[$cid]['code'] = $cno;
			$one['code'] = (string)$cno;
		}
		down_xls($cards, $kn, $name);
	}
	$menucolor = '开卡管理';
	include template('manage_cards_index');
}else if($action == 'add'){
	//添加新卡
	if($_POST){
		$number = $_POST['number'];
		$stareno = $_POST['stareno'];
	
		$cardArr = array();
		for($dd = 0; $dd < intval($number); $dd++){
			$arr = array_merge(range(0, 9), range('A', 'Z'));
			$arr2 = array_merge(range('d', 'z'));
			
			$cno = array();
			$arr_len = count($arr);
			$arr_len2 = count($arr2);
			for ($i = 0; $i < 4; $i++)
			{
				$rand = mt_rand(0, $arr_len-1);
				$rand2 = mt_rand(0, $arr_len2-1);
				$cno[] = $arr[$rand];
				$cno[] = $arr2[$rand2];
			}
			//shuffle(arr)用于给数组随机排序
			shuffle($cno);
			$cnostr = implode('',$cno);
			$data['cno'] = $stareno;
			$data['code'] = $cnostr;
			$data['status'] = 0;
			$data['created'] = time();
			$stareno = substr($stareno,0,8).(substr($stareno,8,8) + 1);
		
			$flag = DB::Insert('jx_cards', $data);
		}
		if($flag)
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'新开'.$number.'张卡成功';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/cardslist.php?action=list');
		}
		else
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'新开'.$number.'张卡失败';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/cardslist.php?action=add');
		}
	}
	$condition = array('id>0');
	$lastcards = DB::LimitQuery('jx_cards', array(
			'condition' => $condition,
			'order' => 'ORDER BY cno DESC',
			'one'=>true
	));
	if($lastcards){
		$lastcards['cno'] = substr($lastcards['cno'],0,8).(substr($lastcards['cno'],8,8) + 1);
	}else{
		$lastcards['cno'] = '8008201310001001';
	}
	$menucolor = '开卡管理';
	include template('manage_cards_add');
}else if($action == 'printing'){
	//未激活卡列表
	$condition = array('status = 1 or status = 2');
	$cno = isset($_POST['cno'])?$_POST['cno']:'';
	$startime = isset($_POST['startime'])?strtotime($_POST['startime']):'';
	$endtime = isset($_POST['endtime'])?strtotime($_POST['endtime']):'';

	if(!empty($startime)){
		$newstartime = date('Y-m-d',$startime);
	}
	if(!empty($endtime)){
		$newendtime = date('Y-m-d',$endtime);
	}
	if(!empty($cno)){
		$condition[] = "cno = $cno";
	}
	if(!empty($startime)){
		$condition[] = "startime >= $startime";
	}
	if(!empty($endtime)){
		$condition[] = "endtime = $endtime";
	}
// 	var_dump('<pre>',$condition);die;
	$count = Table::Count('jx_cards', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	$cards = DB::LimitQuery('jx_cards', array(
			'condition' => $condition,
			'order' => 'ORDER BY id DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	foreach($cards as $mkey => $mval){
		$cards[$mkey]['created'] = date('Y-m-d H:i:s',$mval['created']);
	}
	$menucolor = '未激活卡管理';
	include template('manage_cards_printing');
}else if($action == 'buy'){
	//购买卡管理
	if($_POST && $_POST['money']){
		$chk = $_POST['chk'];
		if(!isset($chk) || $chk == ''){
			redirect( WEB_ROOT . '/manage/cardslist.php?action=printing');
		}
		$cArr = explode(',',$chk);
		$data['pid'] = date('YmdHis',time()).rand(1000,9999); 
		$data['money'] = $_POST['money']; 
		$data['detail'] = $chk; 
		$data['number'] = count($cArr);
		$data['remark'] = $_POST['remark'];
		$data['status'] = 'unpay';
		$data['total_amount'] = $_POST['money'] * count($cArr);
		$data['validity'] = strtotime($_POST['validity']);
		$data['created'] = time();
		$flag['id'] = DB::Insert('jx_records', $data);
		if($flag['id']){
			//此处用于锁定交响卡操作，暂时关闭
// 			$sql = "update `jx_cards` set status=2 where id in (".$chk.")";
// 			DB::Query($sql);
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'设置交享卡金额信息卡成功';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/cardslist.php?action=cardsorder&id='.$flag['id']);
		}
		else	
		{
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'设置交享卡金额信息卡失败';
			$o['ip'] = Utility::GetRemoteIp();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
			redirect( WEB_ROOT . '/manage/cardslist.php?action=printing');
		}
	}
	$chk = $_POST['cards_arr'];
	$condition = array('id > 0');
	$models = DB::LimitQuery('jx_records', array(
			'condition' => $condition,
			'order' => 'ORDER BY sort DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	$count = count(explode(',',$chk));
	$menucolor = '未激活卡管理';
	include template('manage_cards_buy');
	
}else if($action == 'sold'){
	//已激活卡管理
	$condition = array('status = 3');
	$cno = isset($_POST['cno'])?$_POST['cno']:'';	
	$startime = isset($_POST['startime'])?strtotime($_POST['startime']):'';	
	$endtime = isset($_POST['endtime'])?strtotime($_POST['endtime']):'';	
	if(!empty($startime)){
		$newstartime = date('Y-m-d',$startime);
	}
	if(!empty($endtime)){
		$newendtime = date('Y-m-d',$endtime);
	}
	if(!empty($cno)){
		$condition[] = "cno = $cno";	
	}
	if(!empty($startime)){
		$condition[] = "startime = $startime";
	}
	if(!empty($endtime)){
		$condition[] = "endtime = $endtime";	
	}	
	$count = Table::Count('jx_cards', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	$cards = DB::LimitQuery('jx_cards', array(
			'condition' => $condition,
			'order' => 'ORDER BY id DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	foreach ($cards as $ck => $cv){
		$cards[$ck]['startime'] = date('Y-m-d H:i:s',$cv['startime']);
		$cards[$ck]['endtime'] = date('Y-m-d',$cv['endtime']);
	}
	$menucolor = '已激活卡管理';
	include template('manage_cards_sold');
}elseif ($action == 'purchase'){
	//购买记录
	$condition = array('id > 0');
	$pid = isset($_POST['pid'])?$_POST['pid']:'';
	$status = isset($_POST['status'])?$_POST['status']:'pay';
	$startime = isset($_POST['created'])?strtotime($_POST['created']):'';
	$endtime = isset($_POST['validity'])?strtotime($_POST['validity']):'';
	if(!empty($startime)){
		$newstartime = date('Y-m-d',$startime);
	}
	if(!empty($endtime)){
		$newendtime = date('Y-m-d',$endtime);
	}
	if(!empty($pid)){
		$condition[] = "pid = '".$pid."'";
	}
	if(!empty($status)){
		$condition[] = "status = '".$status."'";
	}
	if(!empty($startime)){
		$condition[] = "startime = $startime";
	}
	if(!empty($endtime)){
		$condition[] = "endtime = $endtime";
	}
	$count = Table::Count('jx_records', $condition);
	list($pagesize, $offset, $pagestring) = pagestring2($count, 20);
	$records = DB::LimitQuery('jx_records', array(
			'condition' => $condition,
			'order' => 'ORDER BY id DESC',
			'size' => $pagesize,
			'offset' => $offset,
	));
	foreach ($records as $ck => $cv){
		$records[$ck]['validity'] = date('Y-m-d H:i:s',$cv['validity']);
		$records[$ck]['created'] = date('Y-m-d H:i:s',$cv['created']);
		if(!empty($cv['paytime'])){
			$records[$ck]['paytime'] = date('Y-m-d H:i:s',$cv['paytime']);
		}else{
			$records[$ck]['paytime'] = '';
		}
	}
	
// 	var_dump('<pre>',$condition);die;
	$menucolor = '购买记录';
	include template('manage_cards_purchase');
}else if($action == 'print_status'){
	//已印刷列表
	$id = $_POST['chk'];
	$idarr = explode(',',$id);
	foreach ($idarr as $ikey => $ival){
		$uarray = array( 'status' => 1 );
		$productupdate = Table::UpdateCache('jx_cards', $ival, $uarray);
	}
	
	if($productupdate){
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'印刷了'.count($idarr).'张卡成功';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'success';
	}else{
		$o['adminid'] = $_SESSION['admin_id'];
		$o['module'] = 2;
		$o['content'] = $login_user['username'].'印刷了'.count($idarr).'张卡失败';
		$o['ip'] = Utility::GetRemoteIp();
		$o['created'] = time();
		$o['id'] = DB::Insert('jx_oplogs', $o);
		echo 'failure';
	}
}else if($action == 'cardsorder'){
	//确认订单
	$id = $_GET['id'];
	if($id == '' || !isset($id)){
		redirect( WEB_ROOT . '/manage/cardslist.php?action=printing');
	}
	$cardslist = DB::GetTableRow('jx_records', array( "id=$id"));
	$cardslist['validity'] = date('Y-m-d',$cardslist['validity']);
	
	$menucolor = '未激活卡管理';
	include template('manage_cards_cardsorder');
}else if($action == 'offline'){
	//线下支付
	$id = $_POST['id'];
	if(isset($id) && !empty($id)){
		$sql = "update `jx_records` set `status`='pay',`payment`='offline',`paytime`=".time()."  where id=".$id;
		$flag = DB::Query($sql);
		if($flag){
			$cardslist = DB::GetTableRow('jx_records', array( "id=$id"));
			$sql2 = "update `jx_cards` set `status`=3 ,`pid`='".$cardslist['pid']."',  `money`=".$cardslist['money'].", `facevalue`=".$cardslist['money'].", `startime`=".$cardslist['paytime'].", `endtime`=".$cardslist['validity']." where id in ( ".$cardslist['detail']." )";
			$flag2 = DB::Query($sql2);
			if($flag2){
				$o['adminid'] = $_SESSION['admin_id'];
				$o['module'] = 2;
				$o['content'] = $login_user['username'].'支付订单('.$cardslist['pid'].')成功';
				$o['ip'] = Utility::GetRemoteIp();
				$o['created'] = time();
				$o['id'] = DB::Insert('jx_oplogs', $o);
				echo 'success';
			}else{
				$sql3 = "update `jx_records` set status='unpay' payment='' paytime=''  where id=".$id;
				 DB::Query($sql3);
				 $o['adminid'] = $_SESSION['admin_id'];
				 $o['module'] = 2;
				 $o['content'] = $login_user['username'].'支付订单('.$cardslist['pid'].')失败';
				 $o['ip'] = Utility::GetRemoteIp();
				 $o['created'] = time();
				 $o['id'] = DB::Insert('jx_oplogs', $o);
				 echo 'failure1';
			}
		}else{
			echo 'failure2';
		}
	}else{
		echo 'failure3';
	}
	exit;
}else if($action == 'seldetail'){
	$id = $_GET['id'];
	if(empty($id)){
		redirect( WEB_ROOT . '/manage/cardslist.php?action=purchase');
	}else{
		$cardslist = DB::GetTableRow('jx_records', array( "id=$id"));
		$condition = array('id in ( '.$cardslist['detail'].' )');
		$carsArr = DB::LimitQuery('jx_cards', array(
				'condition' => $condition,
				'order' => 'ORDER BY id DESC',
		));
		foreach($carsArr as $mkey => $mval){
			$carsArr[$mkey]['created'] = date('Y-m-d H:i:s',$mval['created']);
		}
		$menucolor = '购买记录';
		include template('manage_cards_seldetail');
	}
}else if($action == 'cards_excel_total'){
	$condition = array('status = 0');
	$cards = DB::LimitQuery('jx_cards', array(
			'condition' => $condition,
	));
	$name = "cards_".date('Ymd');
	if ( strval($_GET['download']))
	{
		$kn = array(
				'cno' => '卡号',
				'code' => '卡附加码',
		);
		foreach($cards AS $cid => $one) {
			$one['cno'] = $one['cno'];
			$cno = '';
			for($i=0; $i<strlen($one['code']);$i++){
				$cno .= ord($one['code'][$i]);
			}
			$cards[$cid]['code'] = $cno;
			$one['code'] = (string)$cno;
		}
	}else{
		$kn = array(
				'cno' => '卡号',
		);
		foreach($cards AS $cid => $one) {
			$one['cno'] = $one['cno'];
		}
	}
	
	down_xls($cards, $kn, $name);
	$menucolor = '开卡管理';
}

?>