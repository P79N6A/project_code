<?php

return [
    //商户ID
    'merchant_id'        => '100000001303951',
    //商户邮箱
    'seller_email'       => 'zhangyafei@xianhuahua.com',
    // 商户私钥  密钥 px1234
    'merchantPrivateKey' => dirname(__DIR__) .'/key/prod176/user-rsa.pem',
    // 融宝公钥
    'reapalPublicKey'    => dirname(__DIR__) .'/key/prod176/itrus001.pem',
    // APIKEy
    'apiKey'             => '82g477a8619f0c44cc10g2g3a9a8c457046f534b4g76bef4eff341a5cagd1efc',
    // APIUrl
    'apiUrl'             => 'http://api.reapal.com',
    //交易币种
    'currency'           => '156',
    //    证件类型 身份证
    'cert_type'          => '01',
];
