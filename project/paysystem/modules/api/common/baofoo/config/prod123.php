<?php
//生产环境
$keyPath = Yii::$app->basePath.'/modules/api/common/baofoo/key/123/';
return [
    "version"      =>"4.0.0.0",  #版本
    "terminal_id"  =>'35678',   #终端号
    "txn_type"     =>'0431',   #交易类型
    //代扣
    "txn_sub_type" =>'13',  #支付交易子类
    "query_txn_sub_type" =>'31', #查询交易子类
    //认证支付
    "direct_binding_txn_sub_type" =>'01',#直接绑卡
    "prep_binding_txn_sub_type" =>'11',  #预绑卡类交易
    "confirm_binding_txn_sub_type" =>'12',  #确认绑卡类交易
    "remove_binding_txn_sub_type" =>'02',  #解除绑定关系类交易
    "query_binding_txn_sub_type" =>'03',  #查询绑定关系类交易
    "prep_pay_txn_sub_type" =>'14',  #认证支付类预支付交易（14不发短信，15发送短信）
    "confirm_pay_txn_sub_type" =>'16',  #认证支付类支付确认交易
    "query_trade_txn_sub_type" =>'31',  #交易状态查询类交易

    "member_id"    =>'1183342',  #宝付提供给商户的唯一编号
    "data_type"    =>'json',
    "biz_type"     =>'0000',#接入类型
    "pay_cm"       =>'2',#对四要素（身份证号、持卡人姓名、银行卡绑定手机、卡号）进行严格校验
    "id_card_type" =>'01',#身份证类型

    // 证书
    'private_key_password' => "yyy666",	//商户私钥证书密码
    'pfxfilename' => $keyPath."private_key.pfx",  //注意证书路径是否存在
    'cerfilename' => $keyPath."baofu_public.cer",//注意证书路径是否存在
    //余额查询
    'b_terminal_id' => '35676',//网银终端号
    'Key' => 'sx5eh35jfsgr8krj',//MD5Key

    // 支付
    "action_url" => 'https://public.baofoo.com/cutpayment/api/backTransRequest',
    //查询
    "query_url" =>'https://public.baofoo.com/cutpayment/api/backTransRequest',
    //余额查询
    'b_query_url' => 'https://public.baofoo.com/open-service/query/service.do',
];