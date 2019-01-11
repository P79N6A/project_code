<?php

return [
    'trideskey' => '579BEFGINPQUVZehilprstxy',
    'from' => [
        'STRATEGY_ANTIFRAUD' => 3,
        'STRATEGY_CREDIT' => 18,
        'STRATEGY_ALLIN' => 68,
        'STRATEGY_CREDIT_ORIGIN' => 16,
        'STRATEGY_ALLIN_ORIGIN' => 66,
    ],
    'aid' => [ 
        'SOURCE_YYY' => 1, #一亿元
        'SOURCE_CREDIT' => 10,  #智融
    ],
    'xgboost' => [
        'url' => '10.253.101.53:8081/api/xgboost',#Xgboost模型url地址
        'auth_key' => 'spLu1bSt3jXPY8ximZUf9k7F',#Xgboost模型授权码
    ],
    'reloanxg' => [
        'url' => '10.253.101.53:8081/api/reloanxg',#reloanxg模型url地址
        'auth_key' => 'spLu1bSt3jXPY8ximZUf9k7F',#reloanxg模型授权码
    ],
    'ganoderma' => [
        'url' => 'fulin/request',# ganoderma接口url地址
    ],
    'xhApiDomain'=> 'http://openapi.xianhuahua.com/api/', # 开放平台API地址URL
    'operator' => [
        'url' => '10.253.101.53:8081/api/operator',
        'auth_key' => 'spLu1bSt3jXPY8ximZUf9k7F',#operator授权码
    ],
    'request' => [
        'url' => 'antifraud/request',# 腾讯接口url地址
    ],
];
