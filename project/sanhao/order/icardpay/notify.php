<?php
define('ORDER_ROOT', str_replace('\\','/',dirname(dirname(__FILE__))));
define('NOTIFY_ROOT', rtrim(dirname(ORDER_ROOT),'/'));
$correctdate = NOTIFY_ROOT.'/log/icardpay/correct/'.date('Y-m-d');
if(!file_exists( $correctdate ))
{
	@mkdir($correctdate, 0777);
}
file_put_contents($correctdate.'/'.$_POST['orderId'].'_'.time().'.txt' , print_r( $_POST , true ) ) ;


require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

require_once(DIR_LIBARAY."/icardpay/lib/Rsa.class.php");
require_once(DIR_LIBARAY."/icardpay/lib/Processing.class.php");
require_once(DIR_LIBARAY."/icardpay/lib/payInfo.class.php");

$_POST['txnCod'] = 'MerchantmerchantPay';
$RSA = new Rsa();
$PIF = new payInfo();
$PIF->init();
//$RSA->setPriKey("/cer/mob-cacert.pfx", "tempus");    //获取私钥
//$RSA->setPubKey("/cer/mob-cacert.pem");   //获取公钥

$config = require_once(DIR_LIBARAY."/icardpay/KafkaConfig.php");
$merchantId = $config[1]['merchantId'];
$signType = $config[1]['signType'];
$keyFile = $config[1]['keyFile'];
$password = $config[1]['password'];
$merchantKey = $config[1]['merchantKey'];


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
		$condition = array('pay_id' => $pay_id);
		$order = DB::LimitQuery('jx_orders', array(
			'condition' => $condition,
			'one'=>true
		));
		$order_id = $order['id'];
		//修改订单状态
		$uarray = array( 'state' => 'pay');
		Table::UpdateCache('jx_orders', $order_id, $uarray);
		
		//修改商品已售数量
		$sql = "update `jx_products` set sale_number=sale_number+'".$order['quantity']."' where id=".$order['pid'];
		DB::Query($sql);
		//付款人(买家)的手机号，根据订单号获取买家的手机号，在获取支付通账号
		$userbuyer = DB::LimitQuery('jx_users', array(
			'condition' => array('id' => $order['uid']),
			'one'=>true
		));
		$buyerpayno = DB::LimitQuery('jx_bindings', array(
			'condition' => array('mobile' => $userbuyer['mobile']),
			'one'=>true
		));
		
		//记录转入金额到三好网的相关信息
		$u['pay_id'] = $pay_id;
		$u['payOrdNo'] = $_POST['payOrdNo'];
		$u['transfer_amount'] = $order['origin'];
		$u['source'] = $buyerpayno['payno'];
		$u['type'] = 'pay';
		$u['createtime'] = time();
		$u['id'] = DB::Insert('jx_financial_records', $u);
		if($u['id'])
		{
			//转账接口
			$payNo = $config[1]['payNo'];
			//IP地址
			$appip = $config[1]['appip'];
			$nowtime = date('YmdHi');
			//收款人(卖家)的手机号,根据订单号获取卖家的id,然后在获取手机号
			$user = DB::LimitQuery('jx_users', array(
				'condition' => array('id' => $order['sid']),
				'one'=>true
			));
			//查询绑定关系表中对应的支付通账号
			$binding = DB::LimitQuery('jx_bindings', array(
				'condition' => array('mobile' => $user['mobile']),
				'one'=>true
			));
			$mobile = $binding['payno'];
			//转账金额
			$amount = $order['origin']*99;
			$mac = md5($merchantId.$payNo.$mobile.$pay_id.$nowtime.$merchantKey);
			
			
			//调用支付通转账接口
			$url = $appip.'/hk-frt-sys-web/F20111.front';
			$data = "merNo=".$merchantId."&payNo=".$payNo."&userMoblieNo=".$mobile."&amount=".$amount."&prdOrdNo=".$pay_id."&TrDt=".$nowtime."&MAC=".$mac;
			$ret = json_decode(interface_post($url, $data)); 
			//记录转账接口返回的内容
			$transferdate = WWW_ROOT.'/log/icardpay/transfer/'.date('Y-m-d');
			if(!file_exists( $transferdate ))
			{
				RecursiveMkdir($transferdate);
			}
			//file_put_contents($transferdate.'/'.$_POST['orderId'].'_'.time().'url.txt' , print_r( $url."?".$data , true ) ) ;
			file_put_contents($transferdate.'/'.$_POST['orderId'].'_'.time().'.txt' , print_r( $ret , true ) ) ;
			//转账成功
			if($ret->RSPCD == '00000')
			{
				//记录从三好网转出的账户信息
				$u_array['pay_id'] = $pay_id;
				$u_array['payOrdNo'] = $_POST['payOrdNo'];
				$u_array['amount_transferred'] = $order['origin'];
				$u_array['whereabouts'] = $mobile;
				$u_array['type'] = 'settle';
				$u_array['createtime'] = time();
				$u_array['id'] = DB::Insert('jx_financial_records', $u_array);
			}
			else 
			{
				//转账失败，记录转账失败的信息
				$u_array['pay_id'] = $pay_id;
				$u_array['payOrdNo'] = $_POST['payOrdNo'];
				$u_array['amount_transferred'] = $order['origin'];
				$u_array['whereabouts'] = $mobile;
				$u_array['type'] = 'failed';
				$u_array['createtime'] = time();
				$u_array['id'] = DB::Insert('jx_financial_records', $u_array);
			}
		}
		//file_put_contents(DIR_LIBARAY.'/'.$_POST['orderId'].'.txt' , print_r( $ret , true ) ) ;
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
	file_put_contents($errordate.'/'.$_POST['orderId'].'_'.time().'.txt' , print_r( $_POST , true ) ) ;
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
//echo rawurlencode($responseData);
?>