<?php
//测试环境
$keyPath = Yii::$app->basePath.'/modules/api/common/policy/key/dev/';
return [
    "campaignDefId"     =>"10002536977",  #营销活动id    
    'packageDefId'      =>"51321209",#产品组合id      
	'_env'              =>"iTest", #指定环境 iTest:测试环境; uat:预发环境; prd:生产环境	
    '_appKey'           =>"8dffac4d1b5c5e3bcaf046ce531aeb81",//开发者的appKey，由众安提供
    '_url'              =>"http://opengw.daily.zhongan.com/Gateway.do",
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
    'pay_url'           =>'http://cashier.itest.zhongan.com/za-cashier-web/gateway.do',
    'merchant_code'     =>'1512000401',
    'app_key'           =>'eNw4RpAuPVTq67IzGKzr',
    'notify_url'        =>'http://paytest.xianhuahua.com/policy/paynotify',
    'return_url'        =>'http://paytest.xianhuahua.com/policy/payback',
    'queryPayServiceName'      =>'com.zhongan.cashier.service.CashierOrderService.findMerchantOrderByOrderNo',
];