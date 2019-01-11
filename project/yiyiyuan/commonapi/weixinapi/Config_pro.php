<?php

return [
    //接口请求地址，固定不变，无需修改 扫描二维码支付
//    'scan_url' => 'https://pay.swiftpass.cn/pay/gateway',
//    //商户号，商户需改为自己的
//    'scan_key' => '9d101c97133837e13dde2d32a5054abb',
//    'scan_service'    => 'pay.weixin.native',
//    //商户号，商户需改为自己的
//    'scan_mchid'      => '7551000001',
//    //版本号默认为2.0
//    'scan_version'    => '2.0',
//    //异步通知返回地址
//    'scan_notify_url' => 'https://yyy.xianhuahua.com/payapi/yyyscan/notify',
//=============================微信公众号还款==============================================
    'gzh_service'           => 'pay.weixin.jspay',
    //接口请求地址，固定不变，无需修改
    'gzh_url'               => 'https://pay.swiftpass.cn/pay/gateway',
    //商户号，商户需改为自己的---微信公众号还款
    'gzh_mchid'             => '102510066666',
    //密钥，商户需改为自己的---微信公众号还款
    'gzh_key'               => 'b322768375c7e3beefffa3d2a4e47778',
    //版本号默认为2.0
    'gzh_version'           => '2.0',
    //公众号异步通知返回地址---微信公众号还款
    'gzh_notify_url'        => 'http://weixin.xianhuahua.com/dev/yyysdknotify/notify',
    //公众号callback_url---微信公众号还款
    'gzh_callback_url'      => 'http://weixin.xianhuahua.com/dev/repay/success',
    'gzh_url_return'        => 'https://pay.swiftpass.cn/pay/jspay',
//=============================app sdk还款==============================================    
    'sdk_service'           => 'unified.trade.pay',
    //接口请求地址，固定不变，无需修改
    'sdk_url'               => 'https://pay.swiftpass.cn/pay/gateway',
    
    
    //商户号，商户需改为自己的---app sdk还款
    'sdk_mchid'             => '103530004089',
    //商户号，商户需改为自己的---app sdk续期
    'renewal_mchid'         => '103530004089',
    //密钥，商户需改为自己的---app sdk还款
    'sdk_key'               => '6cf87cdc05b88ac879d63e298a388cfc',
    //密钥，商户需改为自己的---app sdk续期
    'renewal_key'           => '6cf87cdc05b88ac879d63e298a388cfc',
    
    
    'sdk_version'           => '2.0',
    //公众号异步通知返回地址---app sdk还款
    'sdk_notify_url'        => 'http://weixin.xianhuahua.com/dev/yyysdknotify/notify',
//=============================app sdk续期==============================================  
    'renewal_service'       => 'unified.trade.pay',
    //接口请求地址，固定不变，无需修改
    'renewal_url'           => 'https://pay.swiftpass.cn/pay/gateway',
    'renewal_version'       => '2.0',
    //公众号异步通知返回地址---app sdk续期
    'renewal_notify_url'    => 'http://weixin.xianhuahua.com/dev/yyysdknotify/renewalnotify',
//=============================微信公众号 续期==============================================     
    'wrenewal_service'      => 'pay.weixin.jspay',
    //接口请求地址，固定不变，无需修改
    'wrenewal_url'          => 'https://pay.swiftpass.cn/pay/gateway',
    //商户号，商户需改为自己的---微信公众号续期
    'wrenewal_mchid'        => '102510066666',
    //密钥，商户需改为自己的---微信公众号续期
    'wrenewal_key'          => 'b322768375c7e3beefffa3d2a4e47778',
    //版本号默认为2.0
    'wrenewal_version'      => '2.0',
    //续期异步通知返回地址---微信公众号续期
    'wrenewal_notify_url'   => 'http://weixin.xianhuahua.com/dev/yyysdknotify/renewalnotify',
    //续期callback_url---微信公众号续期
    'wrenewal_callback_url' => 'http://weixin.xianhuahua.com/dev/renewal/renewalsuccess',
    'wrenewal_url_return'   => 'https://pay.swiftpass.cn/pay/jspay',
];
