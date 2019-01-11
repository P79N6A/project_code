<?php
//测试环境
return [
    // 操作url
    #"action_url" => 'https://wapi.jd.com/express.htm',
    "action_url" => 'https://quick.chinabank.com.cn/express.htm',

    //版本号
    'version'=>'1.0.0',
    //终端号
    'terminal'=>'00000001',
    //商户号
    'merchant'=>'110946701003',
    //md5 密钥
    "md5_key" =>'qoawugdhtkxPGLaMQ9XtvtidRcCggRma',
    //3des 密钥
    '3des_key' => 'Q7qtDdDQHw6tT4adpMFhDnbckrMLdgEc',

    //异步通知地址
    'backPay_url' =>  'http://paytest.xianhuahua.com/jd/backpay',
];