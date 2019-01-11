<?php
/**
 * 测试环境
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/6
 * Time: 10:10
 */
return [
    'merid'                 => '101100117', //商户号
    'noncestr'              => md5('zfb' . date('YmdHis') . rand(1, 100)), //长度不大于 32 位
    'key'                   => '321ef04116cf4140a713bbbc19c72883', //商户密钥

    'request_url'           => 'https://pay.ebjfinance.com/alijspay.php', //支付请求页面
    'query_url'             => 'https://pay.ebjfinance.com/alipay/alipayquery.php', //支付结果查询
];