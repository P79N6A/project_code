<?php

return [
    //商户ID
    'merchant_id'        => '100000001300836',
    //商户邮箱
    'seller_email'       => 'jinxu@xianhuahua.com',
    // 商户私钥
    'merchantPrivateKey' => dirname(__DIR__) .'/key/prod105/user-rsa.pem',
    // 融宝公钥
    'reapalPublicKey'    => dirname(__DIR__) .'/key/prod105/itrus001.pem',
    // APIKEy
    'apiKey'             => '268504d2ab50g6423c372938495fgfc85d799b4e590gfg1ad111a8b475b889c2',
    // APIUrl
    'apiUrl'             => 'http://api.reapal.com',
    //交易币种
    'currency'           => '156',
    //    证件类型 身份证
    'cert_type'          => '01',
];
