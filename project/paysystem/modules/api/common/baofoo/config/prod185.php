<?php
//生产环境
$keyPath = Yii::$app->basePath."/modules/api/common/baofoo/key/185/";
return [
    "version" => "4.0.0.0", // 接口版本
    "member_id" => "1228721", // 商户号
    "terminal_id" => "43649", //终端号
    "aes_key" => "cf807954e1120971", // AES对称秘钥,var_dump(substr(MD5('bfxyzf163'),8,16));

    // 交易类型
    "txn_type" => [
        "ready_sign" => "01", // 预签约
        "confirm_sign" => "02", // 确认签约
        "check_sign" => "03", // 检查是否签约
        "unbind_sign" => "04", // 解除签约
        "ready_pay" => "05", // 预支付
        "confirm_pay" => "06", // 确认支付
        "check_pay" => "07", // 检查支付结果
        "query_pay" => "08", // 直接支付[预支付+确认支付]
    ],
    // 银行卡类型
    "card_type" => [
        "debit_card" => "101", // 借记卡
        "credit_card" => "102", // 信用卡
    ],
    // 身份证件类型
    "id_card_type" => [
        "id_card" => "01", // 居民身份证
    ],
    // 接口地址
    "apiUrl" => "https://public.baofoo.com/cutpayment/protocol/backTransRequest",

    // 回调接口地址
    "return_url" => SYSTEM_PROD?"http://pay.xianhuahua.com/bfxy/backpay":"http://paytest.xianhuahua.com/bfxy/backpay",

    // 证书
    "private_key_password" => "228721",	//商户私钥证书密码
    "pfxfilename" => $keyPath."private_key.pfx",  //注意证书路径是否存在
    "cerfilename" => $keyPath."baofu_public.cer",//注意证书路径是否存在
];