<?php

return [
    //商户ID
    'merchant_id'        => '100000001303510',
    //商户邮箱
    'seller_email'       => 'wangranran@ihsmf.com',
    // 商户私钥  密钥 px1234
    'merchantPrivateKey' => dirname(__DIR__) .'/key/prod167/user-rsa.pem',
    // 融宝公钥
    'reapalPublicKey'    => dirname(__DIR__) .'/key/prod167/itrus001.pem',
    // APIKEy
    'apiKey'             => '40987c69111fg270e8f4fbf492ge61fa8ad77gffac070b2770528baee06gdd4b',
    // APIUrl
    'apiUrl'             => 'http://api.reapal.com',
    //交易币种
    'currency'           => '156',
    //    证件类型 身份证
    'cert_type'          => '01',
];
