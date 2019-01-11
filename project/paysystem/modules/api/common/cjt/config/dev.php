<?php
//测试环境
$keyPath = Yii::$app->basePath.'/modules/api/common/cjt/key/dev/';
return [
    "version"       =>"01",  #版本
    "merchant_id"   =>"cp2016051890757",//商户编号
    "corp_acct_no"  =>"cp2016051890757",//企业账号
    "product_code"  =>"70020001",//代收产品编码
    "business_code" =>"01400",//代收业务编码
    //证书
    'private_key_password' => "123456",	//商户私钥证书密码
    'private_key_path' => $keyPath."private_key.pfx",  //商户私钥
    'public_key_path' => $keyPath."public_key.cer",  //畅捷通公钥
    // 操作url
    "action_url" => 'https://123.103.9.189:9204/crps-gateway/gw01/process01',
];