<?php
//生产环境
$keyPath = Yii::$app->basePath.'/modules/api/common/bbf/key/prod1/';
return [
    "version"      =>"1.0",  #版本
    "payTransCode"  =>'singlePayment',   #交易代码
    "queryTransCode"  =>'orderQuery',   #交易代码
    "signType"    =>'RSA',  #签名方式
    "merchantId"    =>'800010000050062', #邦付宝分配的商户号

    //证书
    'passWord'=>'1234qwer',
    'pfxfilename' => $keyPath."800010000050062.p12",  //
    'cerfilename' => $keyPath."8f8server.cer",//

    //交易url
    "url" => 'https://pay.8f8.com/capgate/capTransaction',
];