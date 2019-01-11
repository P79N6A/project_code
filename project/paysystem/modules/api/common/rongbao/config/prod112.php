<?php

return [
    //商户ID
    'merchant_id'        => '100000001301536',
    //商户邮箱
    'seller_email'       => 'guorong@xianhuahua.com',
    // 商户私钥  密钥 hsm623
    'merchantPrivateKey' => dirname(__DIR__) .'/key/prod112/user-rsa.pem',
    // 融宝公钥
    'reapalPublicKey'    => dirname(__DIR__) .'/key/prod112/itrus001.pem',
    // APIKEy
    'apiKey'             => '17dg7d0b3fc0daageg1fffgc75a20gg34aa2fda358bbf03dfc52a9eb64c399ff',
    // APIUrl
    'apiUrl'             => 'http://api.reapal.com',
    //交易币种
    'currency'           => '156',
    //    证件类型 身份证
    'cert_type'          => '01',
];
