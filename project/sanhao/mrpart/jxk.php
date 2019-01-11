<?php
require_once(dirname( dirname(__FILE__) ). '/autoload.php');

define('ORDER_ROOT', str_replace('\\','/',dirname(dirname(__FILE__))));
$correctdate = ORDER_ROOT.'/log/jxk/'.$_GET['service'].date('Y-m-d');

if(!file_exists( $correctdate ))
{
	@mkdir($correctdate, 0777);
}

file_put_contents($correctdate.'/'.time().'.txt' , print_r( $_GET , true ) ) ;

$array_notify = $_GET;
//根据提交参数进行验签
if(!empty($array_notify) && is_array( $array_notify ))
{
	unset( $array_notify['sign'] ) ;
	$paramkey = array_keys( $array_notify ) ;
	sort( $paramkey ) ;
	$signstr = '' ;
	foreach ( $paramkey as $key => $val ){
		$signstr .= $array_notify[$val] ;
	}
	//系统分配的密匙
	$key = "9964DYByKL967c3308imytCB";
	//签名
	$sign = md5($signstr.$key);
	if($sign == $_GET['sign'])
	{
		//根据参数service的值调用对应的接口处理文件
		if( !isset( $array_notify['service'] ) || empty( $array_notify['service'] )){
			echo json_encode(array('ret'=>'1003','msg'=>'接口名称不能空'));exit;
		}
		$service = $array_notify['service'] ;
		if( file_exists( $service.'.php' ) ){
			require_once( $service.'.php' ) ;
		}else {
			echo json_encode(array('ret'=>'1004','msg'=>'接口名称错误'));exit;
		}
	}
	else 
	{
		echo json_encode(array('ret'=>'1002','msg'=>'签名无效'));exit;
	}
}
else 
{
	echo json_encode(array('ret'=>'1001','msg'=>'参数为空'));exit;
}