<?php
/**
 * 正式
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/6
 * Time: 10:10
 */
return [
    'merid'                 => '101118104', //商户号
    'noncestr'              => md5('zfb' . date('YmdHis') . rand(1, 100)), //长度不大于 32 位
    'key'                   => '6db5fdbeb9404ed9b813b09e92154764', //商户密钥

    'request_url'           => 'https://pay.ebjfinance.com/wechatjspay.php', //支付请求页面
    'query_url'             => 'https://pay.ebjfinance.com/weixin/wechatpayquery.php', //支付结果查询
];