<?php
//生产环境
return [
    // 操作url
    "action_url" => 'http://eros.yaoyuefu.com/api/pay/index',
    "action_url_new" => 'http://eros.yaoyuefu.com/api/recharge/index',
    // "action_url_new" => 'http://47.93.121.86:8012/api/recharge/index',
    //查询
    "send_url" =>'http://funds.yaoyuefu.com/api/sms/sendmsg',
    'callback_url' => 'http://pay.xianhuahua.com/cgback/backpay',
    'getback_url' => 'http://pay.xianhuahua.com/cgback/getback',
];