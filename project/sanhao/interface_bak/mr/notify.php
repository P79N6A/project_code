<?php
define('ORDER_ROOT', str_replace('\\','/',dirname(dirname(__FILE__))));
define('NOTIFY_ROOT', rtrim(dirname(ORDER_ROOT),'/'));
$correctdate = NOTIFY_ROOT.'/log/icardpay/mr/'.date('Y-m-d');
if(!file_exists( $correctdate ))
{
	@mkdir($correctdate, 0777);
}
file_put_contents($correctdate.'/'.$_POST['req_id'].'_'.time().'.txt' , print_r( $_POST , true ) ) ;

require_once(dirname(dirname(dirname(__FILE__))) . '/include/appconfig.php');

$result = json_decode($_POST);
//返回状态提示支付成功
if($result['status'] == '0000')
{
	//修改订单的状态并保存支付记录
}
else 
{
	$array['ret'] = 104;
	$array['msg'] = "支付失败";
	echo json_encode($array);exit;
}