<?php
//正式环境 萍乡海桐
return [
    "Version"       => "1.0",  #版本
    "PartnerId"     => "200001800004",//商户编号
    'PayVersion'    => '0', //快捷版本0标准版 1升级版
    "InputCharset"  => "UTF-8",//编码类型
    "SignType"      => "RSA",//加密方式
	
    "server_key"    => "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDPq3oXX5aFeBQGf3Ag/86zNu0VICXmkof85r+DDL46w3vHcTnkEWVbp9DaDurcF7DMctzJngO0u9OG1cb4mn+Pn/uNC1fp7S4JH4xtwST6jFgHtXcTG9uewWFYWKw/8b3zf4fXyRuI/2ekeLSstftqnMQdenVP7XCxMuEnnmM1RwIDAQAB",//服务器公钥
    "private_key"   => "-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQDQ7EnOOqzzGzfwLzzMEQ8ZfzwPBel5x4wb0T//T+uUWyWwyI6f
CkRgS2iK1s4V4+L9PmDormLsyyQZqTi7wsInn8F6xL4m2QHjSXDmuHlvvF0pXzJ6
jANzQRnxeV40hN7bNEiW1Nbj0f+6gcuzO/5vAuaOgdf0tPb741CiHLgshwIDAQAB
AoGBAJVn1OQRW+tClL9D2LOKo4S1U6rerHe1N0IRYzXe3naqtAp3cl7TxdAjPTDE
qn5HCOXXfRAI3Z+/KiLbvEGx5ouKp87aGrSlJfFATl3SLEMmBjKhnOnYu79xxKvF
kNrVzIy08tidHBX0Dy7zJFOhsEbe+BGvng+m3fDPtmnMAtpBAkEA7r50rF25fjRx
E/FumOjLnL0sDmWWaga1dhQIgsGHdtAOqhwFd9unZ1Y239QCnKtda8uYrmjgRmdw
sTsEwGdvpwJBAOAGDBDihfftRccNkcKeXxnM3/T81Lhdm7So8dwtO4/Va6tQ6+2F
IWvc9S+aQlVrqCJo6J6vqgb/lmUqk3of+CECQAH8QXoheWZsRzh0PIg2/2J5TEF+
ZMbS8XVe60czFs5NIqIVg5IgmH7Kf8BSwgbNggRgeA+TPbI4L/65T+vNsGcCQQDe
FeCcVJTPJlAId3FK6MoKv8o+Cu+vHk2gGeQ7jDQB8Wfd3EknnNV3IwOGf3zHRgTW
p/4EYs1CRtsHy9+MMGlBAkAGj7HOpM8vrf/oDmWEE/Vvp922ivAWa5k8qVFi0Pv6
wC/l0x9s0y+xfodC5ytVT/bsXq1vaHGMYyl8WwbS5yPs
-----END RSA PRIVATE KEY-----",//私钥
    "public_key"    => "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDQ7EnOOqzzGzfwLzzMEQ8ZfzwP
Bel5x4wb0T//T+uUWyWwyI6fCkRgS2iK1s4V4+L9PmDormLsyyQZqTi7wsInn8F6
xL4m2QHjSXDmuHlvvF0pXzJ6jANzQRnxeV40hN7bNEiW1Nbj0f+6gcuzO/5vAuaO
gdf0tPb741CiHLgshwIDAQAB
-----END PUBLIC KEY-----",//公钥
	
    "action_url" => 'https://pay.chanpay.com/mag-unify/gateway/receiveOrder.do',
	"service_url" => [
		'quick_payment' => 'nmg_zft_api_quick_payment',
		'sms_resend' => 'nmg_api_quick_payment_resend',
		'sms_confirm' => 'nmg_api_quick_payment_smsconfirm',
		'trade_query' => 'nmg_api_query_trade'
	]
];