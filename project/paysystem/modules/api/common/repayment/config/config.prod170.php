<?php
// 正式账号
return [
    //支付宝、微信 正式参数 地址
    'pay_merid'         => 'yft2018061400010', //'100001',  //商户代码
    'pay_key'           => 'XnGtWQfQGQhD2mKZnpEskCoTZbkU5HF6',  //秘钥
    'pay_url_h5'        => 'https://alipay.3c-buy.com/api/createOrder?',
    'pay_url_public'    => 'https://alipay.3c-buy.com/api/createPcOrder?',
    //快捷支付 正式参数 地址
    'fast_merid'        => 'yft2017120400002', //正式商户号
    'fast_key'          => 'SSCuvu5uvAKf8FA3scFgcNNll2BOyiJU', // 秘钥
    'fast_pay_url'      => 'https://alipay.3c-buy.com/api/createQuickOrder', //
    'wx_url'            => 'https://alipay.3c-buy.com/api/createQuickOrder', //
    //随机参数-长度不大于 32 位
    'noncestr'          => '12345678910abcdef',
    //图片地址前缀
    'img_url'           => 'http://mobile.qq.com/qrcode?url=',
    // 'query_url'         => 'http://jh.yizhibank.com/api/queryOrder',
    'query_url'         => 'http://jh.chinambpc.com/api/queryOrder',
    //支付平台
    //'notifyUrl'         => 'http://paytest.xianhuahua.com/repayback/notify',//测试地址
    'notifyUrl'         => 'http://pay.xianhuahua.com/repayback/notify',//生产地址

    //支付宝唤起地址
    'iosAlipaysUrl'     => 'alipay://platformapi/startApp?appId=10000011&url=',
    'AndroidAlipaysUrl' => 'alipays://platformapi/startApp?appId=10000011&url=',
];