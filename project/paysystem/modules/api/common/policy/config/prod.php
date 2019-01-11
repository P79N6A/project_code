<?php
//生产环境
$keyPath = Yii::$app->basePath.'/modules/api/common/policy/key/prod/';
return [
    "campaignDefId"     =>"10002536977",  #营销活动id    
    'packageDefId'      =>"51321209",#产品组合id      
	'_env'              =>"prd", #指定环境 iTest:测试环境; uat:预发环境; prd:生产环境	
    '_appKey'           =>"ab3aa148cd2da098b130449f7f97c3fc",//开发者的appKey，由众安提供
    '_url'              =>"http://opengw.zhongan.com/Gateway.do",
    '_charset'          =>"UTF-8",
    '_signType'         =>"RSA",
    '_version'          =>"1.0.0",
    '_format'           =>"json",
    '_privateKey'       => $keyPath."private_key.pfx",  
    '_publicKey'        => $keyPath."public_key.cer",
    'checkServiceName'  =>'zhongan.xianhua.loan.check',
    'applyServiceName'  =>'zhongan.xianhua.loan.apply',
    'cancelServiceName' =>'zhongan.xianhua.loan.cancel',
    'signKey'           =>'2017xianhua',
    'secretKey'         =>'xianhua@20171122',
    'pay_url'           =>'https://cashier.zhongan.com/za-cashier-web/gateway.do',
    'merchant_code'     =>'1712010502',
    'app_key'           =>'GCc3qJxevFBQzBhuf9gV',
    //'notify_url'        =>'http://pay.xianhuahua.com/policy/paynotify',
    //'return_url'        =>'http://pay.xianhuahua.com/policy/payback',
	'notify_url'        =>'http://pay.yaoyuefu.com/policy/paynotify',
	'return_url'        =>'http://pay.yaoyuefu.com/policy/payback',
    'queryPayServiceName'      =>'com.zhongan.cashier.service.CashierOrderService.findMerchantOrderByOrderNo',
];