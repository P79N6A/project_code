<?php

return [
    "oid_partner"=> "201612161001339313",   #商户号
    "version"=> "1.0",                  #版本
    "app_request"=> 3,              #请求应用标识 1-android 2-ios 3-wap
    "sign_type"=> 'MD5',          #签名方式
    "id_type"=> 0,              #证件类型 0 身份证
    "trade_qstring_equal"=> '=>',
    "trade_qstring_split"=> '&',
    "valid_order"=> 10080,  # 订单有效时间 默认7天 单位分钟

    // md5密钥
    'key' => 'c404d6afca19d83dffc1fc5535954372',

    //'base_url'   => 'https://yyy.xianhuahua.com',
    //'notify_url' => "https://yyy.xianhuahua.com/payapi/unionpay/notify",
    // 签约并授权
    //"url_signapply" => 'https://yintong.com.cn/llpayh5/signApply.htm',
    "url_signapply" => 'https://wap.lianlianpay.com/signApply.htm',
    // 授权
    "url_apply" => 'https://repaymentapi.lianlianpay.com/agreenoauthapply.htm',
    // 支付
    "url_pay" => 'https://repaymentapi.lianlianpay.com/bankcardrepayment.htm',
];