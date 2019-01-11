<?php

return [
    //接口请求地址，固定不变，无需修改 扫描二维码支付
//    'scan_url' => 'https://pay.swiftpass.cn/pay/gateway',
//    //测试商户号，商户需改为自己的
//    'scan_key' => '9d101c97133837e13dde2d32a5054abb',
//    'scan_service' => 'pay.weixin.native',
//    //测试商户号，商户需改为自己的
//    'scan_mchid' => '7551000001',
//    //版本号默认为2.0
//    'scan_version' => '2.0',
//    //异步通知返回地址
//    'scan_notify_url' => 'https://yyy.xianhuahua.com/payapi/yyyscan/notify',
    'gzh_service' => 'pay.weixin.jspay',
    //接口请求地址，固定不变，无需修改
    'gzh_url' => 'https://pay.swiftpass.cn/pay/gateway',
    //测试商户号，商户需改为自己的
//    'gzh_mchid'        => '7551000001',
    'gzh_mchid' => '102580071501',
    //测试密钥，商户需改为自己的
//    'gzh_key'          => '9d101c97133837e13dde2d32a5054abb',
    'gzh_key' => '203b6fc793cd586b1199f84c8b23e1c2',
    //版本号默认为2.0
    'gzh_version' => '2.0',
    //公众号异步通知返回地址
    'gzh_notify_url' => 'https://yyy.xianhuahua.com/dev/yyysdknotify/notify',
    //公众号callback_url
    'gzh_callback_url' => 'https://yyy.xianhuahua.com/dev/repay/success',
    'gzh_url_return' => 'https://pay.swiftpass.cn/pay/jspay',
    'sdk_service' => 'unified.trade.pay',
    'sdk_url' => 'https://pay.swiftpass.cn/pay/gateway',
//    'sdk_mchid'      => '755437000006',
//    'sdk_key'        => '7daa4babae15ae17eee90c9e',

    'sdk_version'      => '1.0',
    'sdk_notify_url'   => 'https://yyy.xianhuahua.com/dev/yyysdknotify/notify',
    
    
    'sdk_mchid'     => '103570009591',
    'renewal_mchid' => '103570009591',
    'sdk_key'       => '21df235bf66e66d802d2e44f74875e26',
    'renewal_key'   => '21df235bf66e66d802d2e44f74875e26',
    
    
	'renewal_notify_url' => 'https://yyy.xianhuahua.com/dev/yyysdknotify/renewalnotify',
    'rengzh_notify_url' => 'https://yyy.xianhuahua.com/dev/yyysdknotify/renewalnotify',
    'renewal_url' => 'https://pay.swiftpass.cn/pay/gateway',
    'renewal_key' => '541a9b6a7ca94d242485c0b2b41c8a61',
    'renewal_service' => 'unified.trade.pay',
    'renewal_mchid' => '102590210093',

    'renewal_version' => '2.0',
    
//    'wrenewal_notify_url' => 'https://weixin.xianhuahua.com/dev/yyysdknotify/renewalnotify',
//    'wrenewal_url' => 'https://pay.swiftpass.cn/pay/gateway',
//    'wrenewal_key' => 'b322768375c7e3beefffa3d2a4e47778',
//    'wrenewal_service' => 'unified.trade.pay',
//    'wrenewal_mchid' => '102510066666',
//    'wrenewal_version' => '2.0',
//    'wrenewal_url_return' => 'https://pay.swiftpass.cn/pay/jspay',
    
    'wrenewal_service' => 'pay.weixin.jspay',
    //接口请求地址，固定不变，无需修改
    'wrenewal_url' => 'https://pay.swiftpass.cn/pay/gateway',
    //测试商户号，商户需改为自己的
    'wrenewal_mchid' => '102580071501',
    //测试密钥，商户需改为自己的
    'wrenewal_key' => '203b6fc793cd586b1199f84c8b23e1c2',
    //版本号默认为2.0
    'wrenewal_version' => '2.0',
    //续期异步通知返回地址
    'wrenewal_notify_url' => 'https://yyy.xianhuahua.com/dev/yyysdknotify/renewalnotify',
    //续期callback_url
    'wrenewal_callback_url' => 'https://yyy.xianhuahua.com/dev/renewal/renewalsuccess',
    'wrenewal_url_return' => 'https://pay.swiftpass.cn/pay/jspay',
    
    //果仁宝支付 key
//    'grb_key' => '123456',
    'grb_key' => 'm6G8wP',
    //果仁宝支付商户号
//    'grb_merid' => '222114271207668393',
    'grb_merid' => '102114271208152863',//一亿元线上
//    'grb_merid' => '102114271208152865',//小小黛朵线上商户号
//    'grb_merid' => 'wx476bb3649401c450',
    //果仁宝支付请求地址
//    'grb_url' => 'http://gplus.treespaper.com/gplus-api/rest/unifiedOrder',
    'grb_url' => 'http://gtrade.guorenbao.com/gplus-api/rest/unifiedOrder',
    //果仁宝支付结果查询 url
    'grb_res_url' => 'http://gplus.treespaper.com/gplus-api/rest/payQuery',
    //果仁宝callback_url
    'grb_callback_url' => 'https://yyy.xianhuahua.com/dev/repay/success',
    //果仁宝异步通知返回地址
    'grb_notify_url' => 'https://yyy.xianhuahua.com/dev/weixinpay/weixinpaynotify',
];
