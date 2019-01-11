<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');
require_once(DIR_LIBARAY."/icardpay/lib/Rsa.class.php");
require_once(DIR_LIBARAY."/icardpay/lib/Processing.class.php");
require_once(DIR_LIBARAY."/icardpay/lib/payInfo.class.php");
define('ORDER_ROOT', str_replace('\\','/',dirname(dirname(dirname(__FILE__)))));
$_POST['txnCod'] = 'MerchantmerchantPay';
$RSA = new Rsa();
$PIF = new payInfo();
$PIF->init();

//$RSA->setPriKey("/cer/mob-cacert.pfx", "tempus");    //获取私钥
//$RSA->setPubKey("/cer/mob-cacert.pem");   //获取公钥
$config = require_once(DIR_LIBARAY."/icardpay/KafkaConfig.php");
$merchantId = $config[2]['merchantId'];
$signType = $config[2]['signType'];
$keyFile = $config[2]['keyFile'];
$password = $config[2]['password'];
$merchantKey = $config[2]['merchantKey'];
$correctdate = ORDER_ROOT.'/log/icardpay/correct/'.date('Y-m-d');
if(!file_exists( $correctdate ))
{
	@mkdir($correctdate, 0777);
}

	file_put_contents(ORDER_ROOT.'/log/icardpay/correct/'.date('Y-m-d').'/'.$_POST['orderId'].'_'.time().'.txt' , print_r( $_POST , true ) ) ;

if($_POST['versionId'] == 3) 
{
	//接收数据
    $PIF->setParameter("versionId",    $_POST['versionId']);    //服务版本号
    $PIF->setParameter("merchantId",   $_POST['merchantId']);   //商户编号
    $PIF->setParameter("orderId",      $_POST['orderId']);      //商品订单号
    $PIF->setParameter("settleDate",   $_POST['settleDate']);   //对账日期
    $PIF->setParameter("completeDate", $_POST['completeDate']); //完成时间
    $PIF->setParameter("status",       $_POST['status']);       //账单状态
    $PIF->setParameter("notifyTyp",    $_POST['notifyTyp']);    //通知类型
    $PIF->setParameter("payOrdNo",     $_POST['payOrdNo']);     //支付系统交易号
    $PIF->setParameter("orderAmt",     $_POST['orderAmt']);     //订单总金额
    $PIF->setParameter("signType",     $_POST['signType']);     //签名方式
	
    if($_POST['status'] == 1)
    {
	     //订单编号
		$pay_id = $_POST['orderId'];
		$time = time();
		$sql = "update `jx_records` set `status`='pay',`payment`='online' ,`paytime`=".$time." where `pid`='".$pay_id."'";
		DB::Query($sql);
		$condition = array('pid' => $pay_id);
		$order = DB::LimitQuery('jx_records', array(
			'condition' => $condition,
			'one'=>true
		));
		$order_id = $order['id'];
		
		//修改交享卡状态和信息
		$sql2 = "update `jx_cards` set `pid`='".$order['pid']."' ,`status`=3,`money`=".$order['money'].",`facevalue`=".$order['money'].",`endtime`=".$order['validity']." ,`startime`=".$order['paytime']." where `id` in (".$order['detail'].")";
		$flag2 = DB::Query($sql2);
		file_put_contents(ORDER_ROOT.'/log/icardpay/correct/'.date('Y-m-d').'/'.$_POST['orderId'].'_3'.time().'.txt' , print_r( $sql2 , true ) ) ;
		//记录转入金额到三好网的相关信息
	
    	if($flag2)
		{
			//记录从三好网转出的账户信息
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'支付交享卡订单('.$pay_id.')成功';
			$o['ip'] = getLoginIP();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
		}
		else 
		{
			//转账失败，记录转账失败的信息
			$o['adminid'] = $_SESSION['admin_id'];
			$o['module'] = 3;
			$o['content'] = $login_user['username'].'支付交享卡订单('.$pay_id.')失败';
			$o['ip'] = getLoginIP();
			$o['created'] = time();
			$o['id'] = DB::Insert('jx_oplogs', $o);
		}			
    }
    else 
    {
    	$_POST['error'] = '订单'.$_POST['orderId'].'支付失败';
    	$errordate = WWW_ROOT.'/log/icardpay/error/'.date('Y-m-d');
		if(!file_exists( $errordate ))
		{
			RecursiveMkdir($errordate);
		}
		file_put_contents($errordate.'/'.$_POST['orderId'].'_'.time().'.txt' , print_r( $_POST , true ) ) ;
		die('error');
    }
    if( $signType == 'MD5'){
	    //验签
	    $signature = $_POST['signature'];          //获取签名信息
	    //$PIF->setParameter("signature", "");                   //清空收到报文中的签名信息
	    $data = $PIF->createData();                            //组织需要验签的数据
	    $checkFlag = $RSA->getMd5Verify($data, $signature , $merchantKey );    //验签  
	    //echo $RSA->getDebugInfo();
    }else if( $signType=='CFCA' || $signType=='ZJCA' ){
    	//验签
	    $signature = $_POST['signature'];          //获取签名信息
	    //$PIF->setParameter("signature", "");                   //清空收到报文中的签名信息
	    $data = $PIF->createData();                            //组织需要验签的数据
	    $checkFlag = $RSA->getSslVerify($data, $signature);    //验签  
    }
    if($checkFlag) {
        $PIF->cause = "签名验证成功";
    } else {
        $PIF->status = "2";       //消息处理状态
        $PIF->cause ="签名失败";  //接收回执消息处理失败原因
    }
	
}
else 
{
	$PIF->status = "2";                   //消息处理状态
    $PIF->cause ="数据版本不符或接收错误";  //接收回执消息处理失败原因
	$_POST['error'] = '订单'.$_POST['orderId'].'返回的版本号不正确';
	$errordate = WWW_ROOT.'/log/icardpay/error/'.date('Y-m-d');
	if(!file_exists( $errordate ))
	{
		RecursiveMkdir($errordate);
	}
	die('error');
}

//设置cause的值
$PIF->setParameter("cause",     '');     //签名方式
$responseData = "versionId=".$PIF->getParameter("versionId")
                   ."&merchantId=".$PIF->getParameter("merchantId")
                   ."&orderId=".$PIF->getParameter("orderId")
                   ."&status=".$PIF->getParameter("status")
                   ."&cause=".$PIF->getParameter("cause")
                   ."&signType=".$signType;
//获取signature的值
$signture = md5($responseData.$merchantKey);  


//重新组织数据
$data = array();
$data['versionId'] = $PIF->getParameter("versionId");
$data['merchantId'] = $PIF->getParameter("merchantId");
$data['orderId'] = $PIF->getParameter("orderId");
$data['status'] = $PIF->getParameter("status");
$data['cause'] = $PIF->getParameter("cause");
$data['signType'] = $signType;
$data['signature'] = $signture;

$responseData = $PIF->createXml($data);
echo $responseData;
// echo rawurlencode($responseData);
?>