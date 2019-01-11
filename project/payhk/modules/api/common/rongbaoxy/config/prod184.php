<?php

#融宝协议支付(天津有信)-1635

$keyPath = Yii::$app->basePath.'/modules/api/common/rongbaoxy/key/184/';
return [
    //商户ID
    'merchant_id'        => '100000001304472',
    //商户邮箱
    'seller_email'       => 'liudi@ihsmf.com',
    // 商户私钥
    'merchantPrivateKey' => $keyPath . 'user-rsa.pem',
    // 融宝公钥
    'reapalPublicKey'    => $keyPath . 'itrus001.pem',
    // APIKEy
    'apiKey'             => '0fc5134f2g38c2g1a8afbe96daa866e621774987767a69afc8095061552028bd',
    // APIUrl
   'apiUrl'             => 'http://api.reapal.com',
    #'apiUrl'             => 'http://testapi.reapal.com',
    //交易币种
    'currency'           => '156',
    //    证件类型 身份证
    'cert_type'          => '01',
    //    版本号
    'version'          => '1.0.0',
	//    签名类型
	'sign_type'          => 'RSA',
];
