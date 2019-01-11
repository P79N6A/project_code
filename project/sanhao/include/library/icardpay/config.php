<?php
return array(
  '1' => array(
    'merchantId' => '00000000315533',      //商户号
    'signType' =>'MD5',                   //加密类型(MD5,CFCA或ZJCA)
    'merchantKey' => '2hcty38v53yf',       //MD5的密钥(加密方式为MD5时必输)
    'keyFile' => '/cer/00001616.pfx',       //商户证书私钥文件(加密方式为CFCA或JZCA时必输)
    'password' => 'hkrt123',               //商户证书私钥密码(加密方式为CFCA或JZCA时必输)
    'loginapp' => 'jxtuan',                 //登录应用的名称
    'payNo'    => '0000000031553301',       //商户支付账号
    'freeloginapp' => 'icardpay',          //免登录应用名称
    'appip'       => 'https://user.icardpay.com',  //接口所在服务器的IP地址
    'bindappip'   => 'https://user.icardpay.com',   //绑定接口所在服务器的IP地址
    'otherappip'  => 'https://59.151.121.116',  //发送支付请求服务器的IP地址
    'notifyURL'   => 'http://www.jxtuan.com/account/return.php',                        //用户绑定接口绑定结果通知URL
    'returnURL'   => 'http://www.jxtuan.com/account/bindingsuccess.php'                         //用户绑定接口绑定结束跳转URL
  ),
  '2'=>array(
	'merchantId' => '00000000385769',      //商户号
	'signType' =>'MD5',                   //加密类型(MD5,CFCA或ZJCA)
	'merchantKey' => '9qj8c2w4pb79',       //MD5的密钥(加密方式为MD5时必输)
	'keyFile' => '/cer/00001616.pfx',       //商户证书私钥文件(加密方式为CFCA或JZCA时必输)
	'password' => 'hkrt123',               //商户证书私钥密码(加密方式为CFCA或JZCA时必输)
	'payNo'    => '0000000038576901',       //商户支付账号
	'appip'    => 'https://user.icardpay.com',  //接口所在服务器的IP地址
	)
);
?>
