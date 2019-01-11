<?php
require_once(dirname( dirname(__FILE__) ). '/autoload.php');

$config = require_once(DIR_LIBARAY."/icardpay/KafkaConfig.php");

//转账接口
$merchantId = $config[2]['merchantId'];
$signType = $config[2]['signType'];
$keyFile = $config[2]['keyFile'];
$password = $config[2]['password'];
$merchantKey = $config[2]['merchantKey'];
$payNo = $config[2]['payNo'];
//IP地址
$appip = $config[2]['appip'];


$nowtime = date('YmdHi');

$mobile = '13269311057';
//订单id对应为购买卡时候的订单id
$pay_id = '201310120946454704' ;
$order['origin'] = '0.3';
//转账金额
$amount = $order['origin']*100;
$mac = md5($merchantId.$payNo.$mobile.$pay_id.$nowtime.$merchantKey);
//记录转账接口返回的内容
$transferdate = DIR_ROOT.'/log/icardpay/transfer/'.date('Y-m-d');

if(!file_exists( $transferdate ))
{
	@mkdir($transferdate);
}

//调用支付通转账接口
$url = $appip.'/hk-frt-sys-web/F20111.front';
$data = "merNo=".$merchantId."&payNo=".$payNo."&userMoblieNo=".$mobile."&amount=".$amount."&prdOrdNo=".$pay_id."&TrDt=".$nowtime."&MAC=".$mac;
file_put_contents($transferdate.'/'.$pay_id.'_'.time().'_'.$order['origin'].'.txt' , print_r( $url."?".$data , true ) ) ;
$ret3 = json_decode(interface_post($url, $data)); 
file_put_contents($transferdate.'/'.$pay_id.'_'.time().'.txt' , print_r( $ret3 , true ) ) ;
//记录日志
/***************************/
//end
if($ret3->RSPCD == '00000')
{
	//记录从三好网转出的账户信息
	$u_array['pay_id'] = $pay_id;
	$u_array['amount_transferred'] = $order['origin'];
	$u_array['whereabouts'] = $mobile;
	$u_array['type'] = 'settle';
	$u_array['createtime'] = time();
	$u_array['id'] = DB::Insert('jx_transfer_records', $u_array);
}
else 
{
	//转账失败，记录转账失败的信息
	$u_array['pay_id'] = $pay_id;
	$u_array['amount_transferred'] = $order['origin'];
	$u_array['whereabouts'] = $mobile;
	$u_array['type'] = 'failed';
	$u_array['createtime'] = time();
	$u_array['id'] = DB::Insert('jx_transfer_records', $u_array);
}

exit;