<?php
//生产环境
$keyPath = Yii::$app->basePath.'/modules/api/common/rbcredit/key/dev/';
return [
    'version'            => '1.0',#版本号
    'sign_type'          => 'MD5',#签名类型
    "merchant_id"        =>'100000000000147',   #终端号
    "seller_email"       =>'850138237@qq.com',  #商户邮箱
    //证书
    'merchantPrivateKey' => $keyPath."itrus001_pri.pem",  //商户私钥
    'reapalPublicKey'    => $keyPath."itrus001.pem",//融宝公钥
    //APIKEy
    'apiKey' => "g0be2385657fa355af68b74e9913a1320af82gb7ae5f580g79bffd04a402ba8f",	
    //commitUrl
    "commitUrl" => 'http://testagentpay.reapal.com/agentpay/creditPay/singlePay',
    //queryUrl
    "queryUrl" => 'http://testagentpay.reapal.com/agentpay/creditPay/singleQuery ',
];