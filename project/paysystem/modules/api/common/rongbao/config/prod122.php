<?php

return [
    //商户ID
    'merchant_id'        => '100000001301640',
    //'merchant_id'        => '100000000000148',//测试
    //商户邮箱
    'seller_email'       => 'luyutong@xianhuahua.com',

    'sign_type'          => 'MD5',
    // 商户私钥  密钥 xhh727
    'merchantPrivateKey' => dirname(__DIR__) .'/key/prod122/user-rsa.pem',
    // 融宝公钥
    'reapalPublicKey'    => dirname(__DIR__) .'/key/prod122/itrus001.pem',
    // APIKEy
    'apiKey'             => '43dc9ef70fd1eb13db3d1a37960c60bb42300ebe70de520717233c0d7g3b669d',
    // APIUrl
    'rongpay_api'             => 'http://api.reapal.com/agentcoll',
];
