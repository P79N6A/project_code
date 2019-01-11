<?php

$key_dir = \Yii::$app->basePath . '/modules/api/common/rongbao/key/prod112/';
//生产环境配置文件
return [
// 商户ID，由纯数字组成的字符串
    'merchant_id' => "100000001301536",
//md5key
    'key' => "17dg7d0b3fc0daageg1fffgc75a20gg34aa2fda358bbf03dfc52a9eb64c399ff",
//签约融宝支付账号或卖家收款融宝支付帐户
    'seller_email' => "guorong@xianhuahua.com",
    'batch_version' => "1.0.0",
    //回调地址
    'notify_url' => 'http://open.xianhuahua.com/rongbaoback/rongbao/prod112',
    //融宝接口地址
    'dsfUrl' => 'https://agentpay.reapal.com/',
//商户私钥
    'privateKey' => $key_dir . 'user-rsa.pem',
//融宝公钥
    'pubKeyUrl' => $key_dir . 'itrus001.pem',   
//    'rongpay_api' => "http://testapi.reapal.com/agentcoll",
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
    'charset' => "utf-8", // 字符编码格式 目前支持  utf-8
    'sign_type' => "MD5", // 签名方式 不需修改
    'transport' => "http", //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    'version' => '1.0',
];
