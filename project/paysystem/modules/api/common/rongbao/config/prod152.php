<?php

return [
    //商户ID
    'merchant_id'        => '100000001301635',
    //商户邮箱
    'seller_email'       => 'pengyucheng@xianhuahua.com',
    // 商户私钥  密钥 tjyx23
    'merchantPrivateKey' => dirname(__DIR__) .'/key/prod152/user-rsa.pem',
    // 融宝公钥
    'reapalPublicKey'    => dirname(__DIR__) .'/key/prod152/itrus001.pem',
    // APIKEy
    'apiKey'             => '88d64a78288g08086ce7833a1ed4af9a42f6g6a35026437220488208a126e1fg',
    // APIUrl
    'apiUrl'             => 'http://api.reapal.com',
    //交易币种
    'currency'           => '156',
    //    证件类型 身份证
    'cert_type'          => '01',
];
