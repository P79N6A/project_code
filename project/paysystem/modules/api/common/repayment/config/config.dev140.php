<?php
// 测试账号
return [
    //支付宝、微信 测试参数 地址
    'pay_merid'         => 'yft2017111700002', //'100001',  //商户代码
    'pay_key'           => 'bKo3AsKX9fw9cSpYR7o6QfusqwtwhsZ1', //'wuhLhyqW4kB4Q4yOrwH80HuVnXNSehOr', //秘钥
    'pay_url_h5'        => 'http://jh.yizhibank.com/api/createOrder?',
    'pay_url_public'    => 'http://jh.yizhibank.com/api/createPcOrder',
    //快捷支付 测试参数 地址
    'fast_merid'        => 'yft2017111700002', //测试商户号
    'fast_key'          => 'bKo3AsKX9fw9cSpYR7o6QfusqwtwhsZ1', // 秘钥
    'fast_pay_url'      => 'http://jh.yizhibank.com/api/createQuickOrder', //
    'wx_url'            => 'http://jh.yizhibank.com/api/createWeixinOrder', //
    //随机参数-长度不大于 32 位
    'noncestr'          => '12345678910abcdef',
    //图片地址前缀
    'img_url'           => 'http://mobile.qq.com/qrcode?url=',
    'query_url'         => 'http://jh.yizhibank.com/api/queryOrder',
    //支付平台
    'notifyUrl'         => 'http://paytest.xianhuahua.com/repayback/notify',

    //支付宝唤起地址
    'iosAlipaysUrl'     => 'alipay://platformapi/startApp?appId=10000011&url=',
    'AndroidAlipaysUrl' => 'alipays://platformapi/startApp?appId=10000011&url=',
];