<?php
//测试环境
return [
    // 操作url
    // "action_url" => 'http://testeros.yaoyuefu.com/api/pay/index',
    "action_url" => 'http://47.93.121.86:8012/api/pay/index',
    // "action_url_new" => 'http://testeros.yaoyuefu.com/api/recharge/index',
    "action_url_new" => 'http://47.93.121.86:8012/api/recharge/index',
    //发送短信
    "send_url" =>'http://testpay.yaoyuefu.com/api/sms/sendmsg',
    'callback_url' => 'http://paytest.xianhuahua.com/cgback/backpay',
    'getback_url' => 'http://paytest.xianhuahua.com/cgback/getback',
];