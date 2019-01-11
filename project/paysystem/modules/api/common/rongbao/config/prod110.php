<?php

return [
    //商户ID
    'merchant_id'        => '100000001300980',
    //商户邮箱
    'seller_email'       => 'lianghongqun@xianhuahua.com',
    // 商户私钥
    'merchantPrivateKey' => dirname(__DIR__) .'/key/prod110/user-rsa.pem',
    // 融宝公钥
    'reapalPublicKey'    => dirname(__DIR__) .'/key/prod110/itrus001.pem',
    // APIKEy
    'apiKey'             => '0e643d6865a27957a9e875dd52e5ffb78ca043394a729cg77g47ebed53e613e3',
    // APIUrl
    'apiUrl'             => 'http://api.reapal.com',
    //交易币种
    'currency'           => '156',
    //    证件类型 身份证
    'cert_type'          => '01',
];
