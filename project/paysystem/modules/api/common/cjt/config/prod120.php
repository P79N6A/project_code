<?php
//生产环境 畅捷代扣(一亿元北京有信)
$keyPath = Yii::$app->basePath.'/modules/api/common/cjt/key/prod120/';
return [
    "version"       =>"01",  #版本
    "merchant_id"   =>"cp2017071926939",//商户编号
    "corp_acct_no"  =>"110060776018800039239",//企业账号
    "product_code"  =>"80010001",//代收产品编码
    "business_code" =>"01400",//代收业务编码
    //证书
    'private_key_password' => "DSRBldFMnVuF",	//商户私钥证书密码
    'private_key_path' => $keyPath."private_key.pfx",  //商户私钥
    'public_key_path' => $keyPath."public_key.cer",  //畅捷通公钥
   
    // 操作url
    "action_url" => 'https://cop-gateway.chanpay.com/crps-gateway/gw01/process01',
];