<?php
require_once(dirname( dirname(__FILE__) ). '/autoload.php');

//1.判断参数是否都存在
if( empty( $array_notify['card_code'] ) ) {
	echo json_encode(array('ret'=>'1008','msg'=>'重要参数不能为空'));exit;
}

//2.根据卡磁信息进行解密，判断卡号状态、余额信息、有效期
$code = $array_notify['card_code'] ;
$extracode = Crypt3Des::decrypt( $code );
//取前16位为卡号
$cno = substr( $extracode , 0 , 16 ) ;
$extracode = substr( $extracode , 16 );


$cardinfo = DB::GetTableRow('jx_cards' , array(
				'cno'=>$cno,
				'status'=>3
			));

if(!empty($cardinfo))
{			
	//卡的附加码
	$cardcode = $cardinfo['code'];
	//然后将卡的附加码转换成ASCII 码
	$codecno = '';
	for($i=0; $i<strlen($cardcode);$i++)
	{
		$codecno .= ord($cardcode[$i]);
	}
	if($extracode != $codecno)
	{
		echo json_encode(array('ret'=>'1006','msg'=>'交享卡附加码信息不正确'));exit;
	}
}
else 
{
	echo json_encode(array('ret'=>'1005','msg'=>'交享卡数据为空或信息不正确'));exit;
}
$result = array(
	'ret'=>'100',
	'msg'=>'success',
	'card_no'=>$cno,
	'card_balance'=>$cardinfo['money'],
	'card_valid'=>date('Y-m-d',$cardinfo['endtime']),
);
//组签名信息
//$tmp = array_fill( $result ) ;
//$tmp = sort( $tmp );
//$tmp = array_fill( $tmp );
//$signstr = '';
//foreach ( $tmp as $key => $val ){
//	$signstr .= $val ;
//}
//$sign = md5( $signstr . $key ) ;
//$result['sign'] = $sign ;


echo json_encode( $result ) ;exit;