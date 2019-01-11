<?php
//测试环境
$keyPath = Yii::$app->basePath.'/modules/api/common/baofoo/key/prod114/';
return [
    "version"      =>"4.0.0",  #版本
    "terminal_id"  =>'35243',   #终端号
    "member_id"    =>'1177707',  #宝付提供给商户的唯一编号  花生米富2
    "data_type"    =>'json',

    //证书
    'private_key_password' => "hsm623",	//商户私钥证书密码
    'pfxfilename' => $keyPath."bfkey_xhh.pfx",  //注意证书路径是否存在
    'cerfilename' => $keyPath."baofoo_public_key.cer",//注意证书路径是否存在

    //交易url
    "trade_url" => 'https://public.baofoo.com/baofoo-fopay/pay/BF0040001.do',
    "query_url" => 'https://public.baofoo.com/baofoo-fopay/pay/BF0040002.do'
];