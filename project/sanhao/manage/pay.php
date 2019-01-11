<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');
require_once(dirname(__FILE__) . '/paybank.php');
require_once(DIR_LIBARAY."/icardpay/lib/Rsa.class.php");
require_once(DIR_LIBARAY."/icardpay/lib/Processing.class.php");
require_once(DIR_LIBARAY."/icardpay/lib/creOrderForm.class.php");

ob_start();
need_login();
if (is_post())
{
	$order_id = abs(intval($_POST['order_id']));
}

if(!$order_id || !($order = Table::Fetch('jx_records', $order_id))) {
	redirect( WEB_ROOT. '/cardslist.php');
}
///////////////////////////////////////////////////////////////////////////////
//支付通后台支付接口

global $INI;if(!$order) return null;
$pay_id = $order['pid'];
// $paytype = $order['paytype'];

// var_dump('<pre>',$order);die;

$_POST['txnCod'] = 'MerchantmerchantPay';
$nof = new creOrderForm();
$nof->init();

$config = require(DIR_LIBARAY."/icardpay/KafkaConfig.php");
$merchantId = $config[2]['merchantId'];
$signType = $config[2]['signType'];
$keyFile = $config[2]['keyFile'];
$password = $config[2]['password'];
$merchantKey = $config[2]['merchantKey'];

//biztype交易类型，以前为B3，现改为C3
$nof->setParameter("merchantId",   $merchantId);
$nof->setParameter("orderId",      $pay_id);
$nof->setParameter("orderAmount",  $order['total_amount']*100);
$nof->setParameter("orderDate",    date('Y-m-d', $order['created']));
$nof->setParameter("currency",     'RMB');
$nof->setParameter("transType",    '0102');
$nof->setParameter("retUrl",       $INI['system']['wwwprefix'].'/manage/icardpay/notify.php');
$nof->setParameter("bizType",      'C3');//C3
$nof->setParameter("returnUrl",    $INI['system']['wwwprefix'].'/manage/icardpay/return.php');
$nof->setParameter("prdDisUrl",    $INI['system']['wwwprefix']);
$nof->setParameter("prdName",      strtoupper(bin2hex('三好网卡')));
$nof->setParameter("prdShortName", '');
$nof->setParameter("prdDesc",      strtoupper(bin2hex($order['remark'])));
$nof->setParameter("merRemark",    '');
$nof->setParameter("rptType",      '');
$nof->setParameter("prdUnitPrice", $order['money']*100);
$nof->setParameter("buyCount",     $order['number']);
$nof->setParameter("defPayWay",    '');
$nof->setParameter("buyMobNo",     '');
$nof->setParameter("cpsFlg",       '0');
$nof->setParameter("signType",     $signType);
//$nof->setParameter("token",     $_SESSION['token']);

if($signType=='MD5'){ //MD5加密
	//设置参与MD5加签的字段
	$paraArr = array("versionId","merchantId","orderId","orderAmount","orderDate","currency","transType","retUrl","bizType","returnUrl","prdDisUrl","prdName","prdShortName","prdDesc","merRemark","rptType","prdUnitPrice","buyCount","defPayWay","buyMobNo","cpsFlg","signType");
	//对指定字段进行加签
	$md5Str = $nof->getMD5($paraArr,$merchantKey);
	$nof->setParameter("signature", $md5Str);//添加签名
} else if($signType=='CFCA' || $signType=='ZJCA'){ //证书加签
	//组织数据
	$data = $nof->createData();
	echo '加签数据【'.$data.'】<BR>';

	$rsa = new Rsa();
	$rsa->setPriKey($keyFile, $password);    //获取私钥

	$cov = iconv("UTF-8","GB2312",$data);
	$signmessage = $rsa->getSslSign($cov);//签名
	if(!$rsa->isContinue()) {
		exit("签名失败");
	}
	echo '签名数据【'.$signmessage.'】<BR>';
	$nof->setParameter("signature", $signmessage);//添加签名
} else {
	exit("签名类型有误");
}
//签名重新组织数据，准备与服务器通讯
$data = $nof->createPostData();
// var_dump('<pre>',$data);die;
//和服务器通讯，发送表单
// $nof->sendPay($data);
$redUrl = $config[2]['appip'].'/hkrtcms/merchant/pay/MerchantmerchantPay.do';
// echo $data;die;
if(empty($data)) {
	$nof->debugInfo = "数据为空";
} else {
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: '.$redUrl.'?'.$data);
}

//////////////////////////////////////////////////////////////////////////////