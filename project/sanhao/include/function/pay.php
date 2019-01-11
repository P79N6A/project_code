<?php
//支付通支付接口
function pay_team_icardpay($order){
	global $INI;if(!$order) return null;
	$product = Table::Fetch('jx_products', $order['pid']);
	$order_id = $order['id'];
	$pay_id = $order['pay_id'];
	$paytype = $order['paytype'];

	ob_start();
	require_once(DIR_LIBARAY."/icardpay/lib/Rsa.class.php");
	require_once(DIR_LIBARAY."/icardpay/lib/Processing.class.php");
	require_once(DIR_LIBARAY."/icardpay/lib/creOrderForm.class.php");
	
	$_POST['txnCod'] = 'MerchantmerchantPay';
	$nof = new creOrderForm();
	$nof->init();
	
	$config = require(DIR_LIBARAY."/icardpay/KafkaConfig.php");
	$merchantId = $config[1]['merchantId'];
    $signType = $config[1]['signType'];
    $keyFile = $config[1]['keyFile'];
    $password = $config[1]['password'];
    $merchantKey = $config[1]['merchantKey'];
    
    //biztype交易类型，以前为B3，现改为C3
    $nof->setParameter("merchantId",   $merchantId);
    $nof->setParameter("orderId",      $pay_id);
    $nof->setParameter("orderAmount",  $order['origin']*100);
    $nof->setParameter("orderDate",    date('Y-m-d', $order['createtime']));
    $nof->setParameter("currency",     'RMB');
    $nof->setParameter("transType",    '0102');
    $nof->setParameter("retUrl",       $INI['system']['wwwprefix'].'/order/icardpay/notify.php');
    $nof->setParameter("bizType",      'C3');
    $nof->setParameter("returnUrl",    $INI['system']['wwwprefix'].'/order/icardpay/return.php');
    $nof->setParameter("prdDisUrl",    $INI['system']['wwwprefix']);
    $nof->setParameter("prdName",      strtoupper(bin2hex($product['pname'])));
    $nof->setParameter("prdShortName", '');
    $nof->setParameter("prdDesc",      strtoupper(bin2hex($product['description'])));
    $nof->setParameter("merRemark",    '');
    $nof->setParameter("rptType",      '');
    $nof->setParameter("prdUnitPrice", $order['price']*100);
    $nof->setParameter("buyCount",     $order['quantity']);
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
        if(!$rsa->isContinue()) {exit("签名失败");}
        echo '签名数据【'.$signmessage.'】<BR>';
        $nof->setParameter("signature", $signmessage);//添加签名
    } else {
        exit("签名类型有误");
    }
    //签名重新组织数据，准备与服务器通讯
    $data = $nof->createPostData(); 
    //和服务器通讯，发送表单
    $nof->sendPay($data);   
}

//交行订单支付接口
function pay_team_comm( $total_money, $order ){
	global $INI; if(!$order) return null;
	$total_money = $order['origin'] ;
	$order_id = $order['id'];
	$pay_id = $order['pay_id'];
	$payservice = $order['service'] ;
	require_once(DIR_LIBARAY."/bocom/java/Java.inc"); 
    $here= DIR_LIBARAY . "/bocom" ;

    //java_set_library_path("/usr/comm/lib"); //设置java开发包路径
    java_set_library_path(DIR_LIBARAY."/bocom/lib"); //设置java开发包路径
    java_set_file_encoding("GBK");      //设置java编码
	
    //获得java对象
    $BOCOMSetting=java("com.bocom.netpay.b2cAPI.BOCOMSetting");
    $client=new java("com.bocom.netpay.b2cAPI.BOCOMB2CClient");
    $ret=$client->initialize(DIR_LIBARAY."/bocom/ini/B2CMerchant.xml");
	$ret = java_values($ret);
    if ($ret != "0")
    {
	    $err=$client->getLastErr();
	    //为正确显示中文对返回java变量进行转换，如果用java_set_file_encoding进行过转换则不用再次转换
	    //$err = java_values($err->getBytes("GBK")); 
	    $err=java_values($err);
	    echo "初始化失败,错误信息：" . $err . "<br>";
	    exit(1);
    }

    //获得表单传过来的数据
    $interfaceVersion= "1.0.0.0";
    $merID=java_values($BOCOMSetting->MerchantID); //商户号为固定
    $orderid=$pay_id;
    $orderDate=date("Ymd",$order['createtime']);//订单创建日期
    $orderTime=date("His",$order['createtime']);//订单创建时间
    $tranType="0";
    $amount=$total_money;
    $curType="CNY";
    $orderContent="--";
    $orderMono="partnercomment";
    $phdFlag="0";
    $notifyType="1";
    $merURL=$INI['system']['wwwprefix']. '/order/comm/notify.php';
    $goodsURL=$INI['system']['wwwprefix']. '/order/comm/return.php';
    $jumpSeconds=5;
    $payBatchNo="";
    $proxyMerName="";
    $proxyMerType="";
    $proxyMerCredentials="";
    $netType="0";
    $source="";
	$issBankNo = $payservice;
//	if( $payservice == "UPOP" ){
//		$reqUrl = $BOCOMSetting->ShortPayURL ;
//	}else{
		$reqUrl = $BOCOMSetting->OrderURL ;
//	}
    //连接字符串
    $source=$interfaceVersion . "|" . $merID . "|" . $orderid . "|" . $orderDate . "|" . $orderTime . "|"
                . $tranType . "|" . $amount . "|" . $curType . "|" . $orderContent . "|" . $orderMono . "|"
                . $phdFlag . "|" . $notifyType . "|" . $merURL . "|" . $goodsURL . "|" . $jumpSeconds . "|"
                . $payBatchNo . "|" . $proxyMerName . "|" . $proxyMerType . "|" . $proxyMerCredentials . "|"
                . $netType;
	file_put_contents('/home/shopping/1.txt', $source );
    $sourceMsg=new java("java.lang.String", $source);

    //下为生成数字签名
    $nss=new java("com.bocom.netpay.b2cAPI.NetSignServer");

    $merchantDN=$BOCOMSetting->MerchantCertDN;
    $nss->NSSetPlainText($sourceMsg->getBytes("GBK"));

    $bSignMsg=$nss->NSDetachedSign($merchantDN);
    $signMsg=new java("java.lang.String", $bSignMsg, "GBK");
    return render('block_pay_comm', array(
				'reqUrl' => $reqUrl,
				'interfaceVersion' => $interfaceVersion,
				'merID' => $merID,
				'orderid' => $orderid,
				'orderDate' => $orderDate,
				'orderTime' => $orderTime,
				'tranType' => $tranType,
				'amount' => $amount,
				'curType' => $curType,
				'orderContent' => $orderContent,
				'orderMono' => $orderMono,
				'phdFlag' => $phdFlag,
				'notifyType' => $notifyType,
				'merURL' => $merURL,
				'goodsURL' => $goodsURL,
				'jumpSeconds' => $jumpSeconds,
				'payBatchNo' => $payBatchNo,
				'proxyMerName' => $proxyMerName,
				'proxyMerType' => $proxyMerType,
				'proxyMerCredentials' => $proxyMerCredentials,
				'netType' => $netType,
				'signMsg' => $signMsg,
				'issBankNo' => $issBankNo,
				));
}
//交行充值支付接口
function pay_charge_comm( $total_money, $charge_id, $title, $payservice){
	global $INI; if($total_money<=0||!$title) return null;
	
	$pay_id = $charge_id;
	
	require_once(DIR_LIBARAY."/bocom/java/Java.inc"); 
    $here= DIR_LIBARAY . "/bocom" ;

    java_set_library_path("/usr/comm/lib"); //设置java开发包路径
    java_set_file_encoding("GBK");      //设置java编码

    //获得java对象
    $BOCOMSetting=java("com.bocom.netpay.b2cAPI.BOCOMSetting");
    $client=new java("com.bocom.netpay.b2cAPI.BOCOMB2CClient");
    $ret=$client->initialize(DIR_LIBARAY."/bocom/ini/B2CMerchant.xml");
	$ret = java_values($ret);
    if ($ret != "0")
    {
	    $err=$client->getLastErr();
	    //为正确显示中文对返回java变量进行转换，如果用java_set_file_encoding进行过转换则不用再次转换
	    //$err = java_values($err->getBytes("GBK")); 
	    $err=java_values($err);
	    echo "初始化失败,错误信息：" . $err . "<br>";
	    exit(1);
    }

    //获得表单传过来的数据
    $interfaceVersion= "1.0.0.0";
    $merID=java_values($BOCOMSetting->MerchantID); //商户号为固定
    $orderid=$pay_id;
    $orderDate=date("Ymd",time());//订单创建日期
    $orderTime="";//订单创建时间
    $tranType="0";
    $amount=$total_money;
    $curType="CNY";
    $orderContent="--";
    $orderMono="----";
    $phdFlag="0";
    $notifyType="1";
    $merURL=$INI['system']['wwwprefix']. '/order/comm/notify.php';
    $goodsURL=$INI['system']['wwwprefix']. '/order/comm/return.php';
    $jumpSeconds=5;
    $payBatchNo="";
    $proxyMerName="";
    $proxyMerType="";
    $proxyMerCredentials="";
    $netType="0";
    $source="";
	$issBankNo = $payservice;
//	if( $payservice == "UPOP" ){
//		$reqUrl = $BOCOMSetting->ShortPayURL ;
//	}else{
		$reqUrl = $BOCOMSetting->OrderURL ;
//	}
    //连接字符串
    $source=$interfaceVersion . "|" . $merID . "|" . $orderid . "|" . $orderDate . "|" . $orderTime . "|"
                . $tranType . "|" . $amount . "|" . $curType . "|" . $orderContent . "|" . $orderMono . "|"
                . $phdFlag . "|" . $notifyType . "|" . $merURL . "|" . $goodsURL . "|" . $jumpSeconds . "|"
                . $payBatchNo . "|" . $proxyMerName . "|" . $proxyMerType . "|" . $proxyMerCredentials . "|"
                . $netType;

    $sourceMsg=new java("java.lang.String", $source);

    //下为生成数字签名
    $nss=new java("com.bocom.netpay.b2cAPI.NetSignServer");

    $merchantDN=$BOCOMSetting->MerchantCertDN;
    $nss->NSSetPlainText($sourceMsg->getBytes("GBK"));

    $bSignMsg=$nss->NSDetachedSign($merchantDN);
    $signMsg=new java("java.lang.String", $bSignMsg, "GBK");
    return render('block_pay_comm', array(
				'reqUrl' => $reqUrl,
				'interfaceVersion' => $interfaceVersion,
				'merID' => $merID,
				'orderid' => $orderid,
				'orderDate' => $orderDate,
				'orderTime' => $orderTime,
				'tranType' => $tranType,
				'amount' => $amount,
				'curType' => $curType,
				'orderContent' => $orderContent,
				'orderMono' => $orderMono,
				'phdFlag' => $phdFlag,
				'notifyType' => $notifyType,
				'merURL' => $merURL,
				'goodsURL' => $goodsURL,
				'jumpSeconds' => $jumpSeconds,
				'payBatchNo' => $payBatchNo,
				'proxyMerName' => $proxyMerName,
				'proxyMerType' => $proxyMerType,
				'proxyMerCredentials' => $proxyMerCredentials,
				'netType' => $netType,
				'signMsg' => $signMsg,
				'issBankNo' => $issBankNo,
				));
}
//一键支付签约
function contract_by_onekey(){
	global $INI,$login_user;
	$username = $login_user['username'] ;
	require_once(DIR_LIBARAY."/bocom/java/Java.inc"); 
    $here= DIR_LIBARAY . "/bocom" ;

    java_set_library_path("/usr/comm/lib"); //设置java开发包路径
    java_set_file_encoding("GBK");      //设置java编码

    //获得java对象
    $BOCOMSetting=java("com.bocom.netpay.b2cAPI.BOCOMSetting");
    $client=new java("com.bocom.netpay.b2cAPI.BOCOMB2CClient");
    $ret=$client->initialize(DIR_LIBARAY."/bocom/ini/B2CMerchant.xml");
	$ret = java_values($ret);
    if ($ret != "0")
    {
	    $err=$client->getLastErr();
	    //为正确显示中文对返回java变量进行转换，如果用java_set_file_encoding进行过转换则不用再次转换
	    //$err = java_values($err->getBytes("GBK")); 
	    $err=java_values($err);
	    echo "初始化失败,错误信息：" . $err . "<br>";
	    exit(1);
    }

    //获得表单传过来的数据
    $interfaceVersion= "1.0.0.0";
    $merAgreeNo = $username ;
    $merID = java_values($BOCOMSetting->MerchantID); //商户号为固定
    $accName = $username;
    $certType = '15';
    $certNo = '' ;
    $merURL = $INI['system']['wwwprefix']. '/order/onekey/sign_return.php';
    $notifyURL = '';
    $merComment = '' ;
    $onekeyTranType = '1' ;
    $netType="0";
	$reqUrl = $BOCOMSetting->OrderURL ;
    //连接字符串
    $source = $interfaceVersion . "|" . $merAgreeNo . "|" . $merID . "|" . $accName . "|" . $certType . "|"
                . $certNo . "|" . $merURL . "|" . $notifyURL . "|" . $merComment . "|" . $onekeyTranType . "|"
                . $netType ;

    $sourceMsg=new java("java.lang.String", $source);

    //下为生成数字签名
    $nss=new java("com.bocom.netpay.b2cAPI.NetSignServer");

    $merchantDN=$BOCOMSetting->MerchantCertDN;
    $nss->NSSetPlainText($sourceMsg->getBytes("GBK"));

    $bSignMsg=$nss->NSDetachedSign($merchantDN);
    $signMsg=new java("java.lang.String", $bSignMsg, "GBK");
    return render('block_pay_onekey', array(
				'reqUrl' => $reqUrl,
				'interfaceVersion' => $interfaceVersion,
				'merID' => $merID,
				'merAgreeNo' => $merAgreeNo,
				'accName' => $accName,
				'certType' => $certType,
				'certNo' => $certNo,
				'merURL' => $merURL,
				'notifyURL' => $notifyURL,
				'merComment' => $merComment,
				'onekeyTranType' => $onekeyTranType,
				'netType' => $netType,
				'signData' => $signMsg
				));
}
//一键支付取消
function cancel_by_onekey(){
	global $INI,$login_user;
	$username = $login_user['username'] ;
	//根据用户名解除签约
	$signinfo = Table::Fetch( 'sign_bocom' , $username , 'meragreeno' ) ;
	
	require_once(DIR_LIBARAY."/bocom/java/Java.inc"); 
    $here= DIR_LIBARAY . "/bocom" ;

    java_set_library_path("/usr/comm/lib"); //设置java开发包路径
    java_set_file_encoding("GBK");      //设置java编码

    //获得java对象
    $BOCOMSetting=java("com.bocom.netpay.b2cAPI.BOCOMSetting");
    $client=new java("com.bocom.netpay.b2cAPI.BOCOMB2CClient");
    $ret=$client->initialize(DIR_LIBARAY."/bocom/ini/B2CMerchant.xml");
	$ret = java_values($ret);
    if ($ret != "0")
    {
	    $err=$client->getLastErr();
	    //为正确显示中文对返回java变量进行转换，如果用java_set_file_encoding进行过转换则不用再次转换
	    //$err = java_values($err->getBytes("GBK")); 
	    $err=java_values($err);
	    echo "初始化失败,错误信息：" . $err . "<br>";
	    exit(1);
    }

    //获得用户的协议号
    $agreeNo = $signinfo['ptcid'] ;
    $resp = $client->cancelAgree( $agreeNo ) ;
    if ($resp == null) {
        $err = $client->getLastErr();
        echo "交易错误信息：". $err."<br>";
    }else {
        $code = $resp->getRetCode(); //得到交易返回码
        $msg = $resp->getErrorMessage();
        $msg = java_values($msg->getBytes("UTF-8"));
        echo "交易返回码：". $code ."<br>" ;
        echo "交易错误信息：". $msg ."<br>" ;
        
        if ("000000" == $code ) { //表示交易成功
			$result = $resp->getOpResult();
			$data['retCode'] = java_values( $result->getValueByName("retCode") ) ;
			$data['retMsg'] = java_values( $result->getValueByName("errMsg") ) ;
            //删除签约表信息
            Table::Delete( 'sign_bocom' , $signinfo['ptcid'] , 'ptcid' );
        }
    }
    
    
    exit;
}
//发送客户验证短信
function onekey_sendsmspaymsg( $total_money , $order , $onekey ){
	if($total_money<=0||!$order || !$onekey ) return null;
	global $INI,$login_user;
	$username = $login_user['username'] ;
	require_once(DIR_LIBARAY."/bocom/java/Java.inc"); 
    $here= DIR_LIBARAY . "/bocom" ;

    java_set_library_path("/usr/comm/lib"); //设置java开发包路径
    java_set_file_encoding("GBK");      //设置java编码

    //获得java对象
    $BOCOMSetting=java("com.bocom.netpay.b2cAPI.BOCOMSetting");
    $client=new java("com.bocom.netpay.b2cAPI.BOCOMB2CClient");
    $ret=$client->initialize(DIR_LIBARAY."/bocom/ini/B2CMerchant.xml");
	$ret = java_values($ret);
    if ($ret != "0")
    {
	    $err=$client->getLastErr();
	    //为正确显示中文对返回java变量进行转换，如果用java_set_file_encoding进行过转换则不用再次转换
	    //$err = java_values($err->getBytes("GBK")); 
	    $err=java_values($err);
	    echo "初始化失败,错误信息：" . $err . "<br>";
	    exit(1);
    }
	$data = array() ;
    
    //获得用户的协议号
    $agreeNo = $onekey['ptcid'] ; //协议号
    $merAgreeNo = $onekey['meragreeno'] ;//商户检索协议号
    $amount = $total_money ;// 交易金额
    $applyTime = date( 'YmdHis' , time() ) ; //交易时间
    $netType = "0" ;
    $resp = $client->sendSmsPayMsg1($agreeNo,$merAgreeNo,$amount,$applyTime,$netType) ;
    
    if ($resp == null) {
        $err = $client->getLastErr();
//        echo "交易错误信息：". $err."<br>";
    }else {
        $code = $resp->getRetCode(); //得到交易返回码
        $msg = $resp->getErrorMessage();
        $msg = java_values($msg->getBytes("UTF-8"));
        file_put_contents( 'sign.txt' , $code.":".$msg ) ;
        if ( "000000" == $code ) {
			$result = $resp->getOpResult();
			$data['sessionID'] = java_values( $result->getValueByName("sessionID") ) ;
			$data['retCode'] = java_values( $result->getValueByName("retCode") ) ;
			$data['errMsg'] = java_values( $result->getValueByName("errMsg") ) ;
        }
    }
    file_put_contents( 'ret.txt' , print_r( $data , true ) ) ;
    return $data ;
}
//重发客户验证短信
function onekey_resendsmspaymsg( $sid ){
	global $INI,$login_user;
	if( !$sid ) return null;
	$data = array() ;
	require_once(DIR_LIBARAY."/bocom/java/Java.inc"); 
    $here= DIR_LIBARAY . "/bocom" ;

    java_set_library_path("/usr/comm/lib"); //设置java开发包路径
    java_set_file_encoding("GBK");      //设置java编码

    //获得java对象
    $BOCOMSetting=java("com.bocom.netpay.b2cAPI.BOCOMSetting");
    $client=new java("com.bocom.netpay.b2cAPI.BOCOMB2CClient");
    $ret=$client->initialize(DIR_LIBARAY."/bocom/ini/B2CMerchant.xml");
	$ret = java_values($ret);
    if ($ret != "0")
    {
	    $err=$client->getLastErr();
	    //为正确显示中文对返回java变量进行转换，如果用java_set_file_encoding进行过转换则不用再次转换
	    //$err = java_values($err->getBytes("GBK")); 
	    $err=java_values($err);
	    echo "初始化失败,错误信息：" . $err . "<br>";
	    exit(1);
    }

    
    //识别码
    $sessionID = $sid ;
    $resp = $client->resend( $sessionID ) ;
    if (rep == null) {
        $err = $client->getLastErr();
        echo "交易错误信息：". $err."<br>";
    }
    else {
        $code = $resp->getRetCode(); //得到交易返回码
        $msg = $resp->getErrorMessage();
        $msg = java_values($msg->getBytes("UTF-8"));
                
        if ("000000" == $code ) { //表示交易成功
			$result = $resp->getOpResult();
			$data['sessionID'] = java_values( $result->getValueByName('sessionID') ) ;
			$data['sendCount'] = java_values( $result->getValueByName('sendCount') ) ;
			$data['retCode'] = java_values( $result->getValueByName('retCode') ) ;
			$data['errMsg'] = java_values( $result->getValueByName('errMsg') ) ;
        }
    }
    file_put_contents( 'resend.txt' , print_r( $data , true ) ) ;
    return  $data ;
}
//一键支付短信版
function pay_team_duanxin( $total_money , $order , $sessionID , $dynPassword ){
	global $INI, $login_user;
	$data = array() ;//结果返回数组
	if( $total_money<=0 || !$order || !$sessionID || !$dynPassword ) return null;
	//$team = Table::Fetch('team', $order['team_id']);
	$order_id = $order['id'];
	$pay_id = $order['pay_id'];
	$payservice = $order['service'] ;
	$username = $login_user['username'] ;
	$signinfo = Table::Fetch('sign_bocom' , $username , "meragreeno" ) ;
	
	require_once(DIR_LIBARAY."/bocom/java/Java.inc"); 
    $here= DIR_LIBARAY . "/bocom" ;

    java_set_library_path("/usr/comm/lib"); //设置java开发包路径
    java_set_file_encoding("GBK");      //设置java编码

    //获得java对象
    $BOCOMSetting=java("com.bocom.netpay.b2cAPI.BOCOMSetting");
    $client=new java("com.bocom.netpay.b2cAPI.BOCOMB2CClient");
    $ret=$client->initialize(DIR_LIBARAY."/bocom/ini/B2CMerchant.xml");
	$ret = java_values($ret);
    if ($ret != "0")
    {
	    $err=$client->getLastErr();
	    //为正确显示中文对返回java变量进行转换，如果用java_set_file_encoding进行过转换则不用再次转换
	    //$err = java_values($err->getBytes("GBK")); 
	    $err=java_values($err);
	    echo "初始化失败,错误信息：" . $err . "<br>";
	    exit(1);
    }
	$ord = new java("com.bocom.netpay.b2cAPI.B2COrder");
	$ord->setElement( "interfaceVersion" , "1.0.2.0" );//版本号
	$ord->setElement( "dynPassword" , $dynPassword );//手机动态密码
	$ord->setElement( "sessionID" , $sessionID );//识别码
	$ord->setElement( "agreeNo" , $signinfo['ptcid'] );//协议号
	$ord->setElement( "merAgreeNo" , $signinfo['meragreeno'] );//协议检索号
	$ord->setElement( "merID" , java_values($BOCOMSetting->MerchantID) );//商户ID
	$ord->setElement( "orderNo" , $pay_id );//订单号
	$ord->setElement( "orderDate" , date("Ymd",$order['create_time']) );//订单日期
	$ord->setElement( "orderTime" , "" );//订单时间
	$ord->setElement( "tranType" , "0" );//交易类别 0 B2C
	$ord->setElement( "amount" , $total_money );//交易金额
	$ord->setElement( "curType" , "CNY" );//交易币种 CNY
	$ord->setElement( "orderContent" , "----" );//订单内容
	$ord->setElement( "orderMono" , "testpartner" );//商家备注
	$ord->setElement( "phdFlag" , "1" );//物流配送标志 0非物流 1物流
	$ord->setElement( "notifyType" , "0" );//通知方式  0不通知 1通知 2页面跳转
	$ord->setElement( "merURL" , "" );//主动通知URL
	$ord->setElement( "payBatchNo" , "" );//商户批次号
	$ord->setElement( "proxyMerName" , "" );//代理商名称
	$ord->setElement( "proxyMerType" , "" );//代理商证件类型
	$ord->setElement( "proxyMerCredentials" , "" );//代理商证件号码
	$ord->setElement( "netType" , "0" );//渠道编号
    
    
	$resp = $client->smsPay( $ord ) ;
	file_put_contents( 'cli.txt' , $resp ) ;
	if( $resp == null ){
		return null;
	}else {
		$code = $resp->getRetCode() ;
		$msg = $resp->getErrorMessage() ;
		$msg = java_values($msg->getBytes("UTF-8"));
		file_put_contents( 'sms.txt' , $code.":".$msg ) ;
		if( $code == "000000" ){
			$result = $resp->getOpResult() ;
			$data['merID'] = java_values( $result->getValueByName('merID') ) ;
			$data['orderNo'] = java_values( $result->getValueByName('orderNo') ) ;
			$data['amount'] = java_values( $result->getValueByName('amount') ) ;
			$data['curType'] = java_values( $result->getValueByName('curType') ) ;
			$data['batchNo'] = java_values( $result->getValueByName('batchNo') ) ;
			$data['payBatchNo'] = java_values( $result->getValueByName('payBatchNo') ) ;
			$data['tranDate'] = java_values( $result->getValueByName('tranDate') ) ;
			$data['tranTime'] = java_values( $result->getValueByName('tranTime') ) ;
			$data['serialNo'] = java_values( $result->getValueByName('serialNo') ) ;
			$data['tranState'] = java_values( $result->getValueByName('tranState') ) ;
			$data['feeSum'] = java_values( $result->getValueByName('feeSum') ) ;
			$data['cardType'] = java_values( $result->getValueByName('cardType') ) ;
			$data['bankcomment'] = java_values( $result->getValueByName('bankcomment') ) ;
			
		}
	}
	file_put_contents( 'data.txt' , print_r( $data , true ) ) ;
    return $data ;
}
//一键支付直付版接口
function pay_team_zhifu( $total_money , $order ){
	global $INI, $login_user;
	if($total_money<=0||!$order) return null;
	$team = Table::Fetch('team', $order['team_id']);
	$order_id = $order['id'];
	$pay_id = $order['pay_id'];
	$payservice = $order['service'] ;
	$username = $login_user['username'] ;
	$signinfo = Table::Fetch('sign_bocom' , $username , "meragreeno" ) ;
	
	require_once(DIR_LIBARAY."/bocom/java/Java.inc"); 
    $here= DIR_LIBARAY . "/bocom" ;

    java_set_library_path("/usr/comm/lib"); //设置java开发包路径
    java_set_file_encoding("GBK");      //设置java编码

    //获得java对象
    $BOCOMSetting=java("com.bocom.netpay.b2cAPI.BOCOMSetting");
    $client=new java("com.bocom.netpay.b2cAPI.BOCOMB2CClient");
    $ret=$client->initialize(DIR_LIBARAY."/bocom/ini/B2CMerchant.xml");
	$ret = java_values($ret);
    if ($ret != "0")
    {
	    $err=$client->getLastErr();
	    //为正确显示中文对返回java变量进行转换，如果用java_set_file_encoding进行过转换则不用再次转换
	    //$err = java_values($err->getBytes("GBK")); 
	    $err=java_values($err);
	    echo "初始化失败,错误信息：" . $err . "<br>";
	    exit(1);
    }
	$ord = new java("com.bocom.netpay.b2cAPI.B2COrder");
	$ord->setElement( "interfaceVersion" , "1.0.2.0" );//版本号
	$ord->setElement( "agreeNo" , $signinfo["ptcid"] );//协议号
	$ord->setElement( "merAgreeNo" , $signinfo['meragreeno'] );//协议检索号
	$ord->setElement( "cardNo" , $signinfo['cardnomask'] );//卡号
	$ord->setElement( "cardExpDate" , "" );
	$ord->setElement( "custName" , "海盐" );//户名
	$ord->setElement( "merID" , java_values($BOCOMSetting->MerchantID) );//商户ID
	$ord->setElement( "orderNo" , $pay_id );//订单号
	$ord->setElement( "orderDate" , date("Ymd",$order['create_time']) );//订单日期
	$ord->setElement( "orderTime" , "" );//订单时间
	$ord->setElement( "tranType" , "0" );//交易类别 0 B2C
	$ord->setElement( "amount" , $total_money );//交易金额
	$ord->setElement( "curType" , "CNY" );//交易币种 CNY
	$ord->setElement( "orderContent" , "----" );//订单内容
	$ord->setElement( "orderMono" , "testpartner" );//商家备注
	$ord->setElement( "phdFlag" , "1" );//物流配送标志 0非物流 1物流
	$ord->setElement( "notifyType" , "1" );//通知方式  0不通知 1通知 2页面跳转
	$ord->setElement( "merURL" , $INI['system']['wwwprefix']. '/order/onekey/notify.php' );//主动通知URL
	$ord->setElement( "payBatchNo" , "" );//商户批次号
	$ord->setElement( "proxyMerName" , "" );//代理商名称
	$ord->setElement( "proxyMerType" , "" );//代理商证件类型
	$ord->setElement( "proxyMerCredentials" , "" );//代理商证件号码
	$ord->setElement( "netType" , "0" );//渠道编号
    
    
	$resp = $client->oneKeyPay( $ord ) ;
	if( $resp == null ){
		echo "出错啦" ;
	}else {
		$code = $resp->getRetCode() ;
		$msg = $resp->getErrorMessage() ;
		$msg = java_values($msg->getBytes("UTF-8"));
		
		if( $code == '000000' ){
			$result = $resp->getOpResult() ;
			$data['merID'] = java_values( $result->getValueByName('merID') ) ;
			$data['orderNo'] = java_values( $result->getValueByName('orderNo') ) ;
			$data['amount'] = java_values( $result->getValueByName('amount') ) ;
			$data['curType'] = java_values( $result->getValueByName('curType') ) ;
			$data['batchNo'] = java_values( $result->getValueByName('batchNo') ) ;
			$data['payBatchNo'] = java_values( $result->getValueByName('payBatchNo') ) ;
			$data['tranDate'] = java_values( $result->getValueByName('tranDate') ) ;
			$data['tranTime'] = java_values( $result->getValueByName('tranTime') ) ;
			$data['serialNo'] = java_values( $result->getValueByName('serialNo') ) ;
			$data['tranState'] = java_values( $result->getValueByName('tranState') ) ;
			$data['feeSum'] = java_values( $result->getValueByName('feeSum') ) ;
			$data['cardType'] = java_values( $result->getValueByName('cardType') ) ;
			$data['bankcomment'] = java_values( $result->getValueByName('bankcomment') ) ;
			print_r( $data ) ;
		}
	}
	
    echo $code . ":" . $msg ;exit;
}
///////////////////////////////////////////////////////////////////////////////////////
/* payment: alipay */
function pay_team_alipay($total_money, $order) {
	global $INI; if($total_money<=0||!$order) return null;
	
	require_once(DIR_LIBARAY."/alipay/alipay.KafkaConfig.php");
	require_once(DIR_LIBARAY."/alipay/lib/alipay_submit.class.php");
	
	$order_id = $order['id'];
	$pay_id = $order['pay_id'];

	/* param */
	//支付类型
	$payment_type = "1";
	//服务器异步通知页面路径
	$notify_url = $INI['system']['wwwprefix'] . '/order/alipay/notify_url.php';
	//需http://格式的完整路径，不能加?id=123这类自定义参数
	
	//页面跳转同步通知页面路径
	$return_url = $INI['system']['wwwprefix'] . '/order/alipay/return_url.php';
	//需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

	//卖家支付宝帐户
    $seller_email = $alipay_config['seller_email'];
    //必填

	//商户订单号
    $out_trade_no = $order['pay_id'];
    //商户网站订单系统中唯一订单号，必填

    //订单名称
    $subject = '先花花还款';
    //必填
    
   	//付款金额
    $total_fee = $total_money;
    //必填
    
    //订单描述
    $body = '先花花还款';
    //商品展示地址
    $show_url = '';
    //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html

    //防钓鱼时间戳
    $anti_phishing_key = "";
    //若要使用请调用类文件submit中的query_timestamp函数

    //客户端的IP地址
    $exter_invoke_ip = "";
    //非局域网的外网IP地址，如：221.0.0.1
    
    $it_b_pay = '10m';

	/************************************************************/

	//构造要请求的参数数组，无需改动
	$parameter = array(
			"service" => "create_direct_pay_by_user",
			"partner" => trim($alipay_config['partner']),
			"payment_type"	=> $payment_type,
			"notify_url"	=> $notify_url,
			"return_url"	=> $return_url,
			"seller_email"	=> $seller_email,
			"out_trade_no"	=> $out_trade_no,
			"subject"	=> $subject,
			"total_fee"	=> $total_fee,
			"body"	=> $body,
			"show_url"	=> $show_url,
			"anti_phishing_key"	=> $anti_phishing_key,
			"it_b_pay"        => $it_b_pay,
			"exter_invoke_ip"	=> $exter_invoke_ip,
			"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
	);
	
	//建立请求
	$alipaySubmit = new AlipaySubmit($alipay_config);
	$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
	echo $html_text;
}

function pay_charge_alipay($total_money, $charge_id, $title) {
	global $INI; if($total_money<=0||!$title) return null;
	$order_id = 'charge';

	/* param */
	$_input_charset = 'utf-8';
	$service = 'create_direct_pay_by_user';
	$partner = $INI['alipay']['mid'];
	$security_code = $INI['alipay']['sec'];
	$seller_email = $INI['alipay']['acc'];
	$itbpay = strval($INI['alipay']['itbpay']);

	$sign_type = 'MD5';
	$out_trade_no = $charge_id;

	$return_url = $INI['system']['wwwprefix'] . '/order/alipay/return.php';
	$notify_url = $INI['system']['wwwprefix'] . '/order/alipay/notify.php';
	$show_url = $INI['system']['wwwprefix'] . "/credit/index.php";

	$subject = $title;
	$body = $show_url;
	$quantity = 1;
	$it_b_pay = '10m';

	$parameter = array(
			"service"         => $service,
			"partner"         => $partner,      
			"return_url"      => $return_url,  
			"notify_url"      => $notify_url, 
			"_input_charset"  => $_input_charset, 
			"subject"         => $subject,  	 
			"body"            => $body,     	
			"out_trade_no"    => $out_trade_no,
			"total_fee"       => $total_money,  
			"payment_type"    => "1",
			"show_url"        => $show_url,
			"it_b_pay"        => $it_b_pay,
			"seller_email"    => $seller_email,  
			);
        if(!empty($_SESSION['ali_token'])) $parameter['token'] = $_SESSION['ali_token'];
	//if ($itbpay) $parameter['it_b_pay'] = $itbpay;
	$alipay = new AlipayService($parameter, $security_code, $sign_type);
	$sign = $alipay->Get_Sign();
	$reqUrl = $alipay->create_url();
	return $reqUrl ;
//	return render('block_pay_alipay', array(
//				'order_id' => $order_id,
//				'reqUrl' => $reqUrl,
//				));
}

/* payment: tenpay */
function pay_team_tenpay($total_money, $order) {
	global $INI; if($total_money<=0||!$order) return null;
	$team = Table::Fetch('team', $order['team_id']);
	$order_id = $order['id'];

	$v_mid = $INI['tenpay']['mid'];
	$v_url = $INI['system']['wwwprefix']. '/order/tenpay/return.php';
	$key   = $INI['tenpay']['sec'];
	$v_oid = $order['pay_id'];
	$v_amount = strval($total_money * 100);
	$v_moneytype = $INI['system']['currencyname'];
	$text = $v_amount.$v_moneytype.$v_oid.$v_mid.$v_url.$key;

	/* must */
	$sp_billno = $v_oid;
	$transaction_id = $v_mid. date('Ymd'). date('His') .rand(1000,9999);
	$desc = mb_convert_encoding($team['title'], 'GBK', 'UTF-8');
	/* end */

	$reqHandler = new PayRequestHandler();
	$reqHandler->init();
	$reqHandler->setKey($key);
	$reqHandler->setParameter("bargainor_id", $v_mid);
	$reqHandler->setParameter("cs", "GBK");
	$reqHandler->setParameter("sp_billno", $sp_billno);
	$reqHandler->setParameter("transaction_id", $transaction_id);
	$reqHandler->setParameter("total_fee", $v_amount);
	$reqHandler->setParameter("return_url", $v_url);
	$reqHandler->setParameter("desc", $desc);
	$reqHandler->setParameter("spbill_create_ip", Utility::GetRemoteIp());
	$reqUrl = $reqHandler->getRequestURL();

	if(is_post()&&$_POST['paytype']!='tenpay') {
		$reqHandler->setParameter('bank_type', pay_getqqbank($_POST['paytype']));
		$reqUrl = $reqHandler->getRequestURL();
		redirect( $reqUrl );
	}

//	$html = render('block_pay_tenpay', array(
//				'order_id' => $order_id,
//				'reqUrl' => $reqUrl,
//				));
//				
//	//需要将render的WEB_ROOT替换成空
//	$html = preg_replace( '/\\'.WEB_ROOT.'/', '', $html);
	return $reqUrl ;
}

function pay_charge_tenpay($total_money, $charge_id, $title) {
	global $INI; if($total_money<=0||!$title) return null;
	$order_id = 'charge';

	$v_mid = $INI['tenpay']['mid'];
	$v_url = $INI['system']['wwwprefix']. '/order/tenpay/return.php';
	$key   = $INI['tenpay']['sec'];
	$v_oid = $charge_id;
	$v_amount = strval($total_money * 100);
	$v_moneytype = $INI['system']['currencyname'];
	$text = $v_amount.$v_moneytype.$v_oid.$v_mid.$v_url.$key;

	/* must */
	$sp_billno = $v_oid;
	$transaction_id = $v_mid. date('Ymd'). date('His') .rand(1000,9999);
	$desc = mb_convert_encoding($title, 'GBK', 'UTF-8');
	/* end */

	$reqHandler = new PayRequestHandler();
	$reqHandler->init();
	$reqHandler->setKey($key);
	$reqHandler->setParameter("bargainor_id", $v_mid);
	$reqHandler->setParameter("cs", "GBK");
	$reqHandler->setParameter("sp_billno", $sp_billno);
	$reqHandler->setParameter("transaction_id", $transaction_id);
	$reqHandler->setParameter("total_fee", $v_amount);
	$reqHandler->setParameter("return_url", $v_url);
	$reqHandler->setParameter("desc", $desc);
	$reqHandler->setParameter("spbill_create_ip", Utility::GetRemoteIp());
	$reqUrl = $reqHandler->getRequestURL();

	if(is_post()&&$_POST['paytype']!='tenpay') {
		$reqHandler->setParameter('bank_type', pay_getqqbank($_POST['paytype']));
		$reqUrl = $reqHandler->getRequestURL();
		redirect( $reqUrl );
	}

	return render('block_pay_tenpay', array(
				'order_id' => $order_id,
				'reqUrl' => $reqUrl,
				));
}
/* payment: sdopay */
function pay_team_sdopay($total_money, $order) {
	global $INI; if($total_money<=0||!$order) return null;
 	$team = Table::Fetch('team', $order['team_id']);
        $version = '3.0';
	$order_id = $order['id'];
	$merid = $INI['sdopay']['mid'];  
        //密钥
	$security_code = $INI['sdopay']['sec'];
        //支付渠道
        $paychannel='14';
        $sign_type = 'MD5';
        //交易号
	$orderid = $order['pay_id'];
        //echo $orderid;
        //exit;
        //返回地址
	$return_url = $INI['system']['wwwprefix'] . '/order/sdopay/return.php';
	//服务器终端发货通知地址
        $notify_url = $INI['system']['wwwprefix'] . '/order/sdopay/notify.php';
	
        $ordertime = date("YmdHis");
        $curtype="RMB";//货币类型，目前仅支持"RMB"
        $notifytype="http";//发货通知方式：http,https,tcp等等
        $signtype="2";//签名方式2  MD5。
        $prono='';
        $prodesc= '';
        $remark1='';
        $remark2='';
        $dfchannel = '';
        $producturl = '';
        //echo $_POST['paytype'];
        //exit;
        if(is_post()&&$_POST['paytype']!='sdopay') {
        $actionUrl = 'http://netpay.sdo.com/paygate/ibankpay.aspx';
        $banks = explode("-", $_POST['paytype']);
        $paychannel ="04";
        $bank = $banks[0];
        }else {
        $actionUrl = 'http://netpay.sdo.com/paygate/default.aspx';
        $bank = '';  
        }
    
        $data=$total_money.$orderid.$merid.$meruesr.$paychannel.$return_url.$notify_url.$backurl.$ordertime.$curtype.$notifytype.$signtype.$prono.$prodesc.$remark1.$remark2.$bank.$dfchannel.$producturl;

        $mac = md5($data.$security_code);
	return render('block_pay_sdopay', array(
                'actionUrl' => $actionUrl,
                'version' => $version,
                'amount' => $total_money,  
		'order_id' => $order_id,
                'orderid' => $orderid,
                'paychannel' => $paychannel,
		'return_url' => $return_url,
		'notifyurl' => $notify_url,
                'merid' => $merid,
		'ordertime' => $ordertime,
		'curtype' => $curtype,
		'notifytype' => $notifytype,
		'signtype' => $signtype,
                'prono' => $prono,
                'remark1' => $remark1,
                'remark2' => $remark2,
                'bank' => $bank,
                'mac' => $mac,
			));
}

//盛付通在线充值方式
function pay_charge_sdopay($total_money, $charge_id, $title) {
	global $INI; if($total_money<=0||!$title) return null;
	$version = '3.0';
        $order_id = 'charge';
        //$total_money=number_format($total_money,2);
        /* param */
        //商户
        $merid = $INI['sdopay']['mid'];
        //密钥
        $security_code = $INI['sdopay']['sec'];
        //支付渠道
        $paychannel='14';
        $sign_type = 'MD5';
        //交易号
        $orderid = $charge_id;
        //echo $orderid;
        //exit;
        //返回地址
        $return_url = $INI['system']['wwwprefix'] . '/order/sdopay/return.php';
        //服务器终端发货通知地址
        $notify_url = $INI['system']['wwwprefix'] . '/order/sdopay/notify.php';
        //echo $return_url;
        //exit;
        $backurl ='';
        $ordertime = date("YmdHis");
        //$prodesc = $title;
        $curtype="RMB";//货币类型，目前仅支持"RMB"
        $notifytype="http";//发货通知方式：http,https,tcp等等
        $signtype="2";//签名方式2  MD5。 
        $prono ='';
        $prodesc= '';
        $remark1 ='';
        $remark2 ='';
        $dfchannel = '';
        $producturl = '';
        if(is_post()&&$_POST['paytype']!='sdopay') {
        $actionUrl = 'http://netpay.sdo.com/paygate/ibankpay.aspx';
        $banks = explode("-", $_POST['paytype']);
        $paychannel ="04";
        $bank = $banks[0];
         }else {
        $actionUrl = 'http://netpay.sdo.com/paygate/default.aspx';
        $bank = '';  
      }
        //echo $actionUrl;
        //exit;
        $data=$total_money.$orderid.$merid.$meruesr.$paychannel.$return_url.$notify_url.$backurl.$ordertime.$curtype.$notifytype.$signtype.$prono.$prodesc.$remark1.$remark2.$bank.$dfchannel.$producturl;

        $mac = md5($data.$security_code);
	return render('block_pay_sdopay', array(
                'actionUrl' => $actionUrl,
                'version' => $version,
                'amount' => $total_money,  
		'order_id' => $order_id,
                'orderid' => $orderid,
                'paychannel' => $paychannel,
		'return_url' => $return_url,
		'notifyurl' => $notify_url,
                'merid' => $merid,
		'ordertime' => $ordertime,
		'curtype' => $curtype,
		'notifytype' => $notifytype,
		'signtype' => $signtype,
                'prono' => $prono,
                'prodesc' => $prodesc,
                'remark1' => $remark1,
                'remark2' => $remark2,
                'bank' => $bank,
                'mac' => $mac,
              		));
}

////////////////////////////////////////////////////
/* payment: chinabank */
function pay_team_chinabank($total_money, $order) {
	global $INI; if($total_money<=0||!$order) return null;
	$team = Table::Fetch('team', $order['team_id']);
	$order_id = $order['id'];

	$v_mid = $INI['chinabank']['mid'];
	$v_url = $INI['system']['wwwprefix']. '/order/chinabank/return.php';
	$key   = $INI['chinabank']['sec'];
	$v_oid = $order['pay_id'];
	$v_amount = $total_money;
	$v_moneytype = $INI['system']['currencyname'];
	$text = $v_amount.$v_moneytype.$v_oid.$v_mid.$v_url.$key;
	$v_md5info = strtoupper(md5($text));

	return render('block_pay_chinabank', array(
				'order_id' => $order_id,
				'v_mid' => $v_mid,
				'v_url' => $v_url,
				'key' => $key,
				'v_oid' => $v_oid,
				'v_moneytype' => $v_moneytype,
				'v_md5info' => $v_md5info,
				));
}

function pay_charge_chinabank($total_money, $charge_id, $title) {
	global $INI; if($total_money<=0||!$title) return null;

	$order_id = 'charge';
	$v_mid = $INI['chinabank']['mid'];
	$v_url = $INI['system']['wwwprefix']. '/order/chinabank/return.php';
	$key   = $INI['chinabank']['sec'];
	$v_oid = $charge_id;
	$v_amount = $total_money;
	$v_moneytype = $INI['system']['currencyname'];
	$text = $v_amount.$v_moneytype.$v_oid.$v_mid.$v_url.$key;
	$v_md5info = strtoupper(md5($text));

	return render('block_pay_chinabank', array(
				'order_id' => $order_id,
				'v_mid' => $v_mid,
				'v_url' => $v_url,
				'key' => $key,
				'v_oid' => $v_oid,
				'v_moneytype' => $v_moneytype,
				'v_md5info' => $v_md5info,
				));
}

/* payment: bill */
function pay_team_bill($total_money, $order) {
	global $INI, $login_user; if($total_money<=0||!$order) return null;
	$team = Table::Fetch('team', $order['team_id']);

	$order_id = $order['id'];
	$merchantAcctId = $INI['bill']['mid'];	
	$key = $INI['bill']['sec']; 
	$inputCharset = "1";
	$pageUrl = $INI['system']['wwwprefix'] . '/order/bill/return.php';
	$bgUrl = $INI['system']['wwwprefix'] . '/order/bill/return.php';
	$version = "v2.0";
	$language = "1";
	$signType = "1";	
	$payerName = $login_user['username'];
	$payerContactType = "1";	
	$payerContact = $login_user['email'];	
	$orderId = $order['pay_id'];
	$orderAmount = intval($total_money * 100);
	$orderTime = date('YmdHis');
	$productName = mb_substr(strip_tags($team['title']),0,255,'UTF-8');
	$productNum="1";
	$productId="";
	$productDesc="";
	$ext1="";
	$ext2="";
	$payType="00";
	$bankId="";
	$redoFlag="0";
	$pid=""; 

	$sv = billAppendParam($sv,"inputCharset",$inputCharset);
	$sv = billAppendParam($sv,"pageUrl",$pageUrl);
	$sv = billAppendParam($sv,"bgUrl",$bgUrl);
	$sv = billAppendParam($sv,"version",$version);
	$sv = billAppendParam($sv,"language",$language);
	$sv = billAppendParam($sv,"signType",$signType);
	$sv = billAppendParam($sv,"merchantAcctId",$merchantAcctId);
	$sv = billAppendParam($sv,"payerName",$payerName);
	$sv = billAppendParam($sv,"payerContactType",$payerContactType);
	$sv = billAppendParam($sv,"payerContact",$payerContact);
	$sv = billAppendParam($sv,"orderId",$orderId);
	$sv = billAppendParam($sv,"orderAmount",$orderAmount);
	$sv = billAppendParam($sv,"orderTime",$orderTime);
	$sv = billAppendParam($sv,"productName",$productName);
	$sv = billAppendParam($sv,"productNum",$productNum);
	$sv = billAppendParam($sv,"productId",$productId);
	$sv = billAppendParam($sv,"productDesc",$productDesc);
	$sv = billAppendParam($sv,"ext1",$ext1);
	$sv = billAppendParam($sv,"ext2",$ext2);
	$sv = billAppendParam($sv,"payType",$payType);	
	$sv = billAppendParam($sv,"bankId",$bankId);
	$sv = billAppendParam($sv,"redoFlag",$redoFlag);
	$sv = billAppendParam($sv,"pid",$pid);
	$sv = billAppendParam($sv,"key",$key);
	$signMsg= strtoupper(md5($sv));

	return render('block_pay_bill', array(
				'order_id' => $order_id,
				'merchantAcctId' => $merchantAcctId,
				'key' => $key,
				'inputCharset' => $inputCharset,
				'pageUrl' => $pageUrl,
				'bgUrl' => $bgUrl,
				'version' => $version,
				'language' => $language,
				'signType' => $signType,
				'payerName' => $payerName,
				'payerContactType' => $payerContactType,
				'payerContact' => $payerContact,
				'orderId' => $orderId,
				'orderAmount' => $orderAmount,
				'orderTime' => $orderTime,
				'productName' => $productName,
				'productNum' => $productNum,
				'productId' => $productId,
				'productDesc' => $productDesc,
				'ext1' => $ext1,
				'ext2' => $ext2,
				'payType' => $payType,
				'bankId' => $bankId,
				'redoFlag' => $redoFlag,
				'pid' => $pid,
				'signMsg' => $signMsg,
				));
}

function pay_charge_bill($total_money, $charge_id, $title) {
	global $INI, $login_user; if($total_money<=0||!$title) return null;

	$order_id = 'charge';
	$merchantAcctId = $INI['bill']['mid'];	
	$key = $INI['bill']['sec']; 
	$inputCharset = "1";
	$pageUrl = $INI['system']['wwwprefix'] . '/order/bill/return.php';
	$bgUrl = $INI['system']['wwwprefix'] . '/order/bill/return.php';
	$version = "v2.0";
	$language = "1";
	$signType = "1";	
	$payerName = $login_user['username'];
	$payerContactType = "1";	
	$payerContact = $login_user['email'];	
	$orderId = $charge_id;
	$orderAmount = intval($total_money * 100);
	$orderTime = date('YmdHis');
	$productName = mb_substr(strip_tags($title),0,255,'UTF-8');
	$productNum="1";
	$productId="";
	$productDesc="";
	$ext1="";
	$ext2="";
	$payType="00";
	$bankId="";
	$redoFlag="0";
	$pid=""; 

	$sv = billAppendParam($sv,"inputCharset",$inputCharset);
	$sv = billAppendParam($sv,"pageUrl",$pageUrl);
	$sv = billAppendParam($sv,"bgUrl",$bgUrl);
	$sv = billAppendParam($sv,"version",$version);
	$sv = billAppendParam($sv,"language",$language);
	$sv = billAppendParam($sv,"signType",$signType);
	$sv = billAppendParam($sv,"merchantAcctId",$merchantAcctId);
	$sv = billAppendParam($sv,"payerName",$payerName);
	$sv = billAppendParam($sv,"payerContactType",$payerContactType);
	$sv = billAppendParam($sv,"payerContact",$payerContact);
	$sv = billAppendParam($sv,"orderId",$orderId);
	$sv = billAppendParam($sv,"orderAmount",$orderAmount);
	$sv = billAppendParam($sv,"orderTime",$orderTime);
	$sv = billAppendParam($sv,"productName",$productName);
	$sv = billAppendParam($sv,"productNum",$productNum);
	$sv = billAppendParam($sv,"productId",$productId);
	$sv = billAppendParam($sv,"productDesc",$productDesc);
	$sv = billAppendParam($sv,"ext1",$ext1);
	$sv = billAppendParam($sv,"ext2",$ext2);
	$sv = billAppendParam($sv,"payType",$payType);	
	$sv = billAppendParam($sv,"bankId",$bankId);
	$sv = billAppendParam($sv,"redoFlag",$redoFlag);
	$sv = billAppendParam($sv,"pid",$pid);
	$sv = billAppendParam($sv,"key",$key);
	$signMsg= strtoupper(md5($sv));

	return render('block_pay_bill', array(
				'order_id' => $order_id,
				'merchantAcctId' => $merchantAcctId,
				'key' => $key,
				'inputCharset' => $inputCharset,
				'pageUrl' => $pageUrl,
				'bgUrl' => $bgUrl,
				'version' => $version,
				'language' => $language,
				'signType' => $signType,
				'payerName' => $payerName,
				'payerContactType' => $payerContactType,
				'payerContact' => $payerContact,
				'orderId' => $orderId,
				'orderAmount' => $orderAmount,
				'orderTime' => $orderTime,
				'productName' => $productName,
				'productNum' => $productNum,
				'productId' => $productId,
				'productDesc' => $productDesc,
				'ext1' => $ext1,
				'ext2' => $ext2,
				'payType' => $payType,
				'bankId' => $bankId,
				'redoFlag' => $redoFlag,
				'pid' => $pid,
				'signMsg' => $signMsg,
				));
}

/* payment: paypal */
function pay_team_paypal($total_money, $order) {
	global $INI, $login_user; if($total_money<=0||!$order) return null;
	$team = Table::Fetch('team', $order['team_id']);
	
	$order_id = $order['id'];
	$cmd = '_xclick';
	$business = $INI['paypal']['mid'];
	$location = $INI['paypal']['loc'];
	$currency_code = $INI['system']['currencyname'];

	$item_number = $order['pay_id'];
	$item_name = $team['title'];
	$amount = $total_money;
	$quantity = 1;

	$post_url = "https://www.paypal.com/row/cgi-bin/webscr";
	$return_url = $INI['system']['wwwprefix'] . '/order/index.php';
	$notify_url = $INI['system']['wwwprefix'] . '/order/paypal/ipn.php';
	$cancel_url = $INI['system']['wwwprefix'] . "/order/index.php";

	return render('block_pay_paypal', array(
				'order_id' => $order_id,
				'cmd' => $cmd,
				'business' => $business,
				'location' => $location,
				'currency_code' => $currency_code,
				'item_number' => $item_number,
				'item_name' => $item_name,
				'amount' => $amount,
				'quantity' => $quantity,
				'post_url' => $post_url,
				'return_url' => $return_url,
				'notify_url' => $notify_url,
				'cancel_url' => $cancel_url,
				'login_user' => $login_user,
				));
}

function pay_charge_paypal($total_money, $charge_id, $title) {
	global $INI, $login_user; if($total_money<=0||!$title) return null;

	$order_id = 'charge';
	$cmd = '_xclick';
	$business = $INI['paypal']['mid'];
	$location = $INI['paypal']['loc'];
	$currency_code = $INI['system']['currencyname'];

	$item_number = $charge_id;
	$item_name = $title;
	$amount = $total_money;
	$quantity = 1;

	$post_url = "https://www.paypal.com/row/cgi-bin/webscr";
	$return_url = $INI['system']['wwwprefix'] . '/order/index.php';
	$notify_url = $INI['system']['wwwprefix'] . '/order/paypal/ipn.php';
	$cancel_url = $INI['system']['wwwprefix'] . "/order/index.php";

	return render('block_pay_paypal', array(
				'order_id' => $order_id,
				'cmd' => $cmd,
				'business' => $business,
				'location' => $location,
				'currency_code' => $currency_code,
				'item_number' => $item_number,
				'item_name' => $item_name,
				'amount' => $amount,
				'quantity' => $quantity,
				'post_url' => $post_url,
				'return_url' => $return_url,
				'notify_url' => $notify_url,
				'cancel_url' => $cancel_url,
				'login_user' => $login_user,
				));
}

/* payment: yeepay */
function pay_team_yeepay($total_money, $order) {
	global $INI, $login_user; if($total_money<=0||!$order) return null;
	//$team = Table::Fetch('team', $order['team_id']);
	require_once( WWW_ROOT . '/order/yeepay/yeepayCommon.php');

	$order_id = $order['id'];
	$pay_id = $order['pay_id'];
	$p0_Cmd = 'Buy';
	$p1_MerId = '10012407344';
	$p2_Order = $pay_id;
	$p3_Amt = $total_money;
	$p4_Cur = "CNY";
	$str = '先花花还款';
	$p5_Pid = '';
	$p6_Pcat = '';
	$p7_Pdesc = '';
	$p8_Url = $INI['system']['wwwprefix'] . '/order/yeepay/notify.php';
	$p9_SAF = '0';
	$pa_MP = '';
	$pd_FrpId = '';
	$pr_NeedResponse = '1';
	$merchantKey = '757Rvi3L1T3w41WWa20ah6sk5nz9kz2087M0fIp0W2hgTp4074jpRPF4V927';
	//echo $p2_Order."<br/>".$p3_Amt."<br/>".$p4_Cur."<br/>".$p5_Pid."<br/>".$p6_Pcat."<br/>".$p7_Pdesc."<br/>".$p8_Url."<br/>".$pa_MP."<br/>".$pd_FrpId."<br/>".$pr_NeedResponse;exit;
	//$hmac = getReqHmacString($p1_MerId,$p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse,$merchantKey);
	$hmac = getReqHmacString($p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse);
	//echo $hmac;exit;
	return render('block_pay_yeepay', array(
				'order_id' => $order_id,
				'p0_Cmd' => $p0_Cmd,
				'p1_MerId' => $p1_MerId,
				'p2_Order' => $p2_Order,
				'p3_Amt' => $p3_Amt,
				'p4_Cur' => $p4_Cur,
				'p5_Pid' => $p5_Pid,
				'p6_Pcat' => $p6_Pcat,
				'p7_Pdesc' => $p7_Pdesc,
				'p8_Url' => $p8_Url,
				'p9_SAF' => $p9_SAF,
				'pa_MP' => $pa_MP,
				'pd_FrpId' => $pd_FrpId,
				'pr_NeedResponse' => $pr_NeedResponse,
				'merchantKey' => $merchantKey,
				'hmac' => $hmac,
				));
}


function pay_team_xianhuahuayeepay($total_money, $order, $bank) {
	global $INI, $login_user; if($total_money<=0||!$order) return null;
	//$team = Table::Fetch('team', $order['team_id']);
	require_once( WWW_ROOT . '/order/yeepay/yeepayCommon.php');

	$order_id = $order['id'];
	$pay_id = $order['pay_id'];
	$p0_Cmd = 'Buy';
	$p1_MerId = '10012407344';
	$p2_Order = $pay_id;
	$p3_Amt = $total_money;
	$p4_Cur = "CNY";
	$str = '先花花还款';
	$p5_Pid = '';
	$p6_Pcat = '';
	$p7_Pdesc = '';
	$p8_Url = $INI['system']['wwwprefix'] . '/order/yeepay/callback.php';
	$p9_SAF = '0';
	$pa_MP = '';
	$pd_FrpId = $bank;
	$pr_NeedResponse = '1';
	$merchantKey = '757Rvi3L1T3w41WWa20ah6sk5nz9kz2087M0fIp0W2hgTp4074jpRPF4V927';
	//echo $p2_Order."<br/>".$p3_Amt."<br/>".$p4_Cur."<br/>".$p5_Pid."<br/>".$p6_Pcat."<br/>".$p7_Pdesc."<br/>".$p8_Url."<br/>".$pa_MP."<br/>".$pd_FrpId."<br/>".$pr_NeedResponse;exit;
	//$hmac = getReqHmacString($p1_MerId,$p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse,$merchantKey);
	$hmac = getReqHmacString($p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse);
	//echo $hmac;exit;
	return render('block_pay_yeepay', array(
				'order_id' => $order_id,
				'p0_Cmd' => $p0_Cmd,
				'p1_MerId' => $p1_MerId,
				'p2_Order' => $p2_Order,
				'p3_Amt' => $p3_Amt,
				'p4_Cur' => $p4_Cur,
				'p5_Pid' => $p5_Pid,
				'p6_Pcat' => $p6_Pcat,
				'p7_Pdesc' => $p7_Pdesc,
				'p8_Url' => $p8_Url,
				'p9_SAF' => $p9_SAF,
				'pa_MP' => $pa_MP,
				'pd_FrpId' => $pd_FrpId,
				'pr_NeedResponse' => $pr_NeedResponse,
				'merchantKey' => $merchantKey,
				'hmac' => $hmac,
				));
}

function pay_charge_yeepay($total_money, $charge_id, $title) {
	global $INI, $login_user; if($total_money<=0||!$title) return null;
	require_once( WWW_ROOT . '/order/yeepay/yeepayCommon.php');

	$order_id = 'charge';
	$p0_Cmd = 'Buy';
	$p1_MerId = $INI['yeepay']['mid'];
	$p2_Order = $charge_id;
	$p3_Amt = $total_money;
	$p4_Cur = "CNY";
	$p5_Pid = "ZuituGo-Charge({$total_money})";
	$p6_Pcat = '';
	$p5_Pdesc = "ZuituGo-Charge({$total_money})";
	$p8_Url = $INI['system']['wwwprefix'] . '/order/yeepay/callback.php';
	$p9_SAF = '0';
	$pa_MP = '';
	$pd_FrpId = strval($_REQUEST['pd_FrpId']);
	$pr_NeedResponse = '1';
	$merchantKey = $INI['yeepay']['sec'];

	$hmac = getReqHmacString($p1_MerId,$p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse,$merchantKey);

	return render('block_pay_yeepay', array(
				'order_id' => $order_id,
				'p0_Cmd' => $p0_Cmd,
				'p1_MerId' => $p1_MerId,
				'p2_Order' => $p2_Order,
				'p3_Amt' => $p3_Amt,
				'p4_Cur' => $p4_Cur,
				'p5_Pid' => $p5_Pid,
				'p6_Pcat' => $p6_Pcat,
				'p7_Pdesc' => $p7_Pdesc,
				'p8_Url' => $p8_Url,
				'p9_SAF' => $p9_SAF,
				'pa_MP' => $pa_MP,
				'pd_FrpId' => $pd_FrpId,
				'pr_NeedResponse' => $pr_NeedResponse,
				'merchantKey' => $merchantKey,
				'hmac' => $hmac,
				));
}

/****payment cmpay***/

function pay_team_cmpay($total_money, $order) {
	global $INI; if($total_money<=0||!$order) return null;

	$team = Table::Fetch('team', $order['team_id']);
        $order_id = $order['id'];
        $orderId = $order['pay_id'];
        $productDesc = $team['title'];
        $productName = $team['title'];
        $productId = date('Ymd');//产品编号
        $amount = $total_money;
        $currency = "CNY";
        $channelType = "TOKEN";
        $functiontype = "DODIRECTPAYMENT";
	return render('block_pay_cmpay', array(
                  'order_id' => $order_id,
                  'orderId' => $orderId,
                  'channelType' => $channelType,
                  'amount' => $amount,
                  'currency' => $currency,
                  'productName' => $productName,
                  'productDesc' => $productDesc,
                  'productId' => $productId,
				));
}


function pay_charge_cmpay($total_money, $charge_id, $title) {
	global $INI, $login_user; if($total_money<=0||!$title) return null;


        $order_id = 'charge';
        $orderId = $charge_id; 
        $productDesc = $title;
        $productName = $title;
        $productId = date('Ymd');//产品编号
        $amount = $total_money;
        $currency = "CNY";
        $channelType = "TOKEN";
        $functiontype = "DODIRECTPAYMENT";

        return render('block_pay_cmpay', array(
                  'order_id' => $order_id,
                  'orderId' => $orderId,
                  'channelType' => $channelType,
                  'amount' => $amount,
                  'currency' => $currency,
                  'productName' => $productName,
                  'productDesc' => $productDesc,
                  'productId' => $productId,
				));
}
/* pay util function */
function billAppendParam($s, $k, $v){
	$joinstring = $s ? '&' : null;
	return $v=='' ? $s : "{$s}{$joinstring}{$k}={$v}";
}
