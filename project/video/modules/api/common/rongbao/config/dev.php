<?php
$key_dir = \Yii::$app->basePath . '/modules/api/common/rongbao/key/dev/';

return [
// 商户ID，由纯数字组成的字符串
    'merchant_id' => "100000000000147",
//md5key
    'key' => "g0be2385657fa355af68b74e9913a1320af82gb7ae5f580g79bffd04a402ba8f",
//签约融宝支付账号或卖家收款融宝支付帐户
    'seller_email' => "yujingxin8180149@126.com",
    'batch_version' => "1.0.0",
//通知地址，由商户提供
    'notify_url' => "http://182.92.80.211:8091/rongbaoback/rongbao/dev",
//查询通知地址，由商户提供
    'batchpay_notify_url' => "http://yyy.xianhuahua.com/dev/aa/rbbackurl",
//返回地址，由商户提供
    'return_url' => "http://yyy.xianhuahua.com/dev/aa/huixian",
// APIUrl测试
    'dsfUrl' => 'http://testagentpay.reapal.com/agentpay/',
//商户私钥
    'privateKey' => $key_dir . 'user-rsa.pem',
//融宝公钥
    'pubKeyUrl' => $key_dir . 'itrus001.pem',
    'rongpay_api' => "http://testapi.reapal.com/agentcoll",
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
    'charset' => "utf-8", // 字符编码格式 目前支持  utf-8
    'sign_type' => "MD5", // 签名方式 不需修改
    'transport' => "http", //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    'version' => '1.0',
];
