<?php

$keyPath = Yii::$app->basePath.'/modules/api/common/rongbao/key/dev/';
return [
    //商户ID
    'merchant_id'        => '100000001300836',
    //商户邮箱
    'seller_email'       => 'jinxu@xianhuahua.com',
    // 商户私钥
    
//    'merchantPrivateKey' => SYSTEM_PROD ? Yii::$app->basePath.'/common/rongbaopay/key/user-rsa.pem':'D:\\reapaldemo\\user-rsa.pem',
    'merchantPrivateKey' => $keyPath . 'user-rsa.pem',
    // 融宝公钥
    
//    'reapalPublicKey'    => SYSTEM_PROD ? Yii::$app->basePath.'/common/rongbaopay/key/itrus001.pem':'D:\\reapaldemo\\itrus001.pem',
    'reapalPublicKey'    => $keyPath . 'itrus001.pem',
    // APIKEy
    'apiKey'             => '268504d2ab50g6423c372938495fgfc85d799b4e590gfg1ad111a8b475b889c2',
    // APIUrl
    'apiUrl'             => 'http://api.reapal.com',
    //交易币种
    'currency'           => '156',
    //    证件类型 身份证
    'cert_type'          => '01',
];
