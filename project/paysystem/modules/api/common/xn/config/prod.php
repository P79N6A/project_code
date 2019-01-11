<?php 
//小诺配置 正式
return [
    "version"      =>"01",  #版本
    'key' => '3aNwrc9YAJhKgbLu5RCPg9YUSA8SBRT6',
    '_v' => '3jJoell2', //3des加密向量
    'appid' => 'WHzMGTz09htoAHjm',
    'agreementUrl' => "https://ybr.nyonline.cn/nyopen/queryNYContractUrl.c",  //小诺协议接口
    'bankurl'  => "https://ybr.nyonline.cn/nyopen/applyLoan.c",  //上标接口
    'billurl' => "https://ybr.nyonline.cn/nyopen/queryBillsByBidno.c", //借款人账单查询接口
    'repaymenturl' => "https://ybr.nyonline.cn/nyopen/repaymentBid.c", //还款通知
];