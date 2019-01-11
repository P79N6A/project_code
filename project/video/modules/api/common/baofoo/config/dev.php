<?php
//测试环境
$keyPath = Yii::$app->basePath.'/modules/api/common/baofoo/key/dev/';
return [
    "version"      =>"4.0.0",  #版本
    "terminal_id"  =>'100000859',   #终端号
    "member_id"    =>'100000178',  #宝付提供给商户的唯一编号
    "data_type"    =>'json',

    //证书
    'private_key_password' => "123456",	//商户私钥证书密码
    'pfxfilename' => $keyPath."m_pri.pfx",  //注意证书路径是否存在
    'cerfilename' => $keyPath."baofoo_pub.cer",//注意证书路径是否存在

    //交易url
    "trade_url" => 'http://paytest.baofoo.com/baofoo-fopay/pay/BF0040001.do',
    "query_url" => 'http://paytest.baofoo.com/baofoo-fopay/pay/BF0040002.do'
];