<?php
//测试环境
$keyPath = Yii::$app->basePath.'/modules/api/common/changjie/key/prod118/';
return [
    "version"      =>"01",  #版本
    "merchant_id"    =>'cp2017071162656',  #商户的唯一编号
    "corp_acct_no" =>'110060776018170108340', #企业账号
    "business_code" => '09900', #业务代码
    "product_code" => '60020002', #产品编码

    //证书
    'private_key_pwd' => "UppiV71VhNf6",	//商户私钥证书密码
    'private_key_path' => $keyPath."cj_pri.pfx",  //注意证书路径是否存在
    'public_key_path' => $keyPath."cj_pub.cer",//注意证书路径是否存在

    //请求接口url
    "trade_url" => 'https://cop-gateway.chanpay.com/crps-gateway/gw01/process01',
];