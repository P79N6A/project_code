<?php
//生产环境
$keyPath = Yii::$app->basePath.'/modules/api/common/baofoo/key/114/';
return [
    "version"      =>"4.0.0.0",  #版本
    "terminal_id"  =>'35242',   #终端号
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

    "member_id"    =>'1177707',  #宝付提供给商户的唯一编号
    "data_type"    =>'json',
    "biz_type"     =>'0000',#接入类型
    "pay_cm"       =>'2',#对四要素（身份证号、持卡人姓名、银行卡绑定手机、卡号）进行严格校验
    "id_card_type" =>'01',#身份证类型

    //余额查询
    'b_terminal_id' => '35241',//网银终端号
    'Key' => 'p79xu9gc4vyxwfsz',//MD5Key

    //众安-宝付账户
    'to_acc_name' => '众安在线财产保险股份有限公司3',//收款人宝付会员名
    'to_acc_no' => 'tangjialin@zhongan.com',//收款人宝付会员帐号
    'to_member_id' => '1182750',//收款方会员号(商编号)
    "terminal_id_df"  =>'35243',   #代付终端号
    'version_df' => '4.0.0', #版本
    //转账账单拉取
    'tran_bill_version' =>'4.0.0.2',
    'file_type' => 'fo',# 出单
    'client_ip' => '182.92.80.211',#ip地址需报备

    // 证书
    'private_key_password' => "hsm623",	//商户私钥证书密码
    'pfxfilename' => $keyPath."private_key.pfx",  //注意证书路径是否存在
    'cerfilename' => $keyPath."baofu_public.cer",//注意证书路径是否存在

    // 支付
    "action_url" => 'https://public.baofoo.com/cutpayment/api/backTransRequest',
    //查询
    "query_url" =>'https://public.baofoo.com/cutpayment/api/backTransRequest',
    //余额查询
    'b_query_url' => 'https://public.baofoo.com/open-service/query/service.do',
    //宝付转账
    'pay_url' => 'https://public.baofoo.com/baofoo-fopay/pay/BF0040007.do',
    //宝付转账查询
    'tran_query_url'    =>'https://public.baofoo.com/baofoo-fopay/pay/BF0040010.do',
    'tran_bill_url'     =>'https://public.baofoo.com/boas/api/fileLoadNewRequest',
];