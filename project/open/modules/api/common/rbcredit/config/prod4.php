<?php
//生产环境
$keyPath = Yii::$app->basePath.'/modules/api/common/rbcredit/key/prod4/';
return [
    'version'            => '1.0',#版本号
    'sign_type'          => 'MD5',#签名类型
    "merchant_id"        =>'100000001300836',   #终端号
    "seller_email"       =>'jinxu@xianhuahua.comm',  #商户邮箱
    //证书
    'merchantPrivateKey' => $keyPath."user-rsa.pem",  //商户私钥
    'reapalPublicKey'    => $keyPath."itrus001.pem",//融宝公钥
    //APIKEy
    'apiKey' => "268504d2ab50g6423c372938495fgfc85d799b4e590gfg1ad111a8b475b889c2",	
    //commitUrl
    "commitUrl" => 'https://agentpay.reapal.com/creditPay/singlePay',
    //queryUrl
    "queryUrl" => 'https://agentpay.reapal.com/creditPay/singleQuery',
];
