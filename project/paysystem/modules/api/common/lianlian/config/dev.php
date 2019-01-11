<?php

return include __dir__ . '/prod104.php';

// return [
//     "version"=> "1.1",                  #版本
//     "oid_partner"=> "201704171001649504",   #商户号
//     "app_request"=> 3,              #请求应用标识 1-android 2-ios 3-wap
//     "sign_type"=> 'MD5',          #签名方式
//     "busi_partner"=>101001,  #虚拟商品销售：101001 实物商品销售：109001
//     "id_type"=> 0,              #证件类型 0 身份证
//     "trade_qstring_equal"=> '=>',
//     "trade_qstring_split"=> '&',
//     "valid_order"=> 10080,  # 订单有效时间 默认7天 单位分钟
//     // md5密钥
//     'key' => 'c404d6afca19d83dffc1fc5535954372',
//     // 支付
//     "url_pay" => 'https://wap.lianlianpay.com/authpay.htm',
//     //支付查询
//     "query_pay"=>'https://queryapi.lianlianpay.com/orderquery.htm'
// ];