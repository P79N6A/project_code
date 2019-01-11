<?php
//测试环境
$keyPath = Yii::$app->basePath.'/modules/api/common/changjie/key/prod127/';
return [
    "version"      =>"01",  #版本
    "merchant_id"    =>'cp2017071136532',  #商户的唯一编号
    "corp_acct_no" =>'120066043018000002354', #企业账号
    "business_code" => '09900', #业务代码
    "product_code" => '60020002', #产品编码

    //证书
    'private_key_pwd' => "bFQ6gstKR94K",	//商户私钥证书密码
    'private_key_path' => $keyPath."cj_pri.pfx",  //注意证书路径是否存在
    'public_key_path' => $keyPath."cj_pub.cer",//注意证书路径是否存在

    //请求接口url
    "trade_url" => 'https://cop-gateway.chanpay.com/crps-gateway/gw01/process01',
];