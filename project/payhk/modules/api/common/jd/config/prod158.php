<?php
//生产环境
return [
    #天津有信而立信息技术服务有限公司
    // 操作url
    "action_url" => 'https://wapi.jd.com/express.htm',
    //版本号
    'version'=>'1.0.0',
    //终端号
    'terminal'=>'00000001',
    //商户号
    'merchant'=>'110943970003',
    //md5 密钥
    "md5_key" =>'zb8xTJZdWApOWbCedWS0sqOJioi0sslI',
    //3des 密钥
    '3des_key' => 'GfuRFV2htt8EKVKtug3QAW3IC1H0ose1',

    //异步通知地址
    'backPay_url' =>'http://pay.xianhuahua.com/jd/backpay',
];