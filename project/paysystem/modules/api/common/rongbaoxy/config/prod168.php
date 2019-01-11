<?php

#融宝协议支付(天津有信)-1635

$keyPath = Yii::$app->basePath.'/modules/api/common/rongbaoxy/key/168/';
return [
    //商户ID
    'merchant_id'        => '100000001303510',
    //商户邮箱
    'seller_email'       => 'wangranran@ihsmf.com',
    // 商户私钥
    'merchantPrivateKey' => $keyPath . 'user-rsa.pem',
    // 融宝公钥
    'reapalPublicKey'    => $keyPath . 'itrus001.pem',
    // APIKEy
    'apiKey'             => '40987c69111fg270e8f4fbf492ge61fa8ad77gffac070b2770528baee06gdd4b',
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
