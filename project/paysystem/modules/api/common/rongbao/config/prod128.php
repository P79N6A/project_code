<?php

return [
    //商户ID
    'merchant_id'        => '100000001301654',
    //商户邮箱
    'seller_email'       => 'yinlu@xianhuahua.com',
    // 商户私钥  密钥 hsm623
    'merchantPrivateKey' => dirname(__DIR__) .'/key/prod128/user-rsa.pem',
    // 融宝公钥
    'reapalPublicKey'    => dirname(__DIR__) .'/key/prod128/itrus001.pem',
    // APIKEy
    'apiKey'             => 'ffg9a9c9aga3ce2636ca7628e1egg050f7dc125ea6807abe01f07dga7046bc3g',
    // APIUrl
    'apiUrl'             => 'http://api.reapal.com',
    //交易币种
    'currency'           => '156',
    //    证件类型 身份证
    'cert_type'          => '01',
];
