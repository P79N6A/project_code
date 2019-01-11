<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$post = file_get_contents('php://input') ;
if( $post ){
	$xml = XMLtoArray( $post ) ;
	if( !empty( $xml ) && is_array( $xml ) ){
		//将下行状态报告插入到数据库里
		$u['mid'] = $xml['SYNCPacket']['mid'] ;
		$u['cpmid'] = $xml['SYNCPacket']['cpmid'] ;
		$u['port'] = $xml['SYNCPacket']['port'] ;
		$u['mobile'] = $xml['SYNCPacket']['mobile'] ;
		$u['msg'] = $xml['SYNCPacket']['msg'] ;
		$u['area'] = $xml['SYNCPacket']['area'] ;
		$u['city'] = $xml['SYNCPacket']['city'] ;
		$u['type'] = $xml['SYNCPacket']['type'] ;
		$u['channel'] = $xml['SYNCPacket']['channel'] ;
		$u['reserve'] = empty( $xml['SYNCPacket']['reserve'] ) ? 0 : $xml['SYNCPacket']['reserve'] ;
		$u['created'] = time();
		
		$u['id'] = DB::Insert('jx_smslogs', $u);
		$result = 0 ;
		$mid = $xml['SYNCPacket']['mid'];
		$cpmid = $xml['SYNCPacket']['cpmid'] ;
	}
}else{
	$mid = "" ;
	$cpmid = "" ;
	$result = 100 ;
}

echo '<?xml version="1.0" encoding="UTF-8"?>
	<SYNCResponse>
		<mid>'.$mid.'</mid>
		<cpmid>'.$cpmid.'</cpmid>
		<result>'.$result.'</result>
	</SYNCResponse>';
exit;