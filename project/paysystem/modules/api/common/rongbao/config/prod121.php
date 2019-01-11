<?php

return [
    //商户ID
    'merchant_id'        => '100000001301654',
    // 'merchant_id'        => '100000000000148',//测试
    //商户邮箱
    'seller_email'       => 'yinlu@xianhuahua.com',

    'sign_type'          => 'MD5',
    // 商户私钥  密钥xhh727
    'merchantPrivateKey' => dirname(__DIR__) .'/key/prod121/user-rsa.pem',
    // 融宝公钥
    'reapalPublicKey'    => dirname(__DIR__) .'/key/prod121/itrus001.pem',
    // APIKEy
    'apiKey'             => 'ffg9a9c9aga3ce2636ca7628e1egg050f7dc125ea6807abe01f07dga7046bc3g',
    // APIUrl
    'rongpay_api'        => 'http://api.reapal.com/agentcoll',
    
    'charset'            => 'utf-8',
];
