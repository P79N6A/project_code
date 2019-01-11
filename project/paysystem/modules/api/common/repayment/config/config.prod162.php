<?php
// 正式账号

return [
    "Version"       =>"1.0",  #版本
    'PayVersion'    => '1', //快捷版本0标准版 1升级版
    "PartnerId"     =>"200001540109",//商户编号
    "query_url"     =>'https://pay.chanpay.com/mag-unify/gateway/receiveOrder.do?',//查询第三方接口
    'NotifyUrl'     =>'http://pay.xianhuahua.com/cjback/notify',//异步回调地址   生产地址
   //'NotifyUrl'     =>'http://paytest.xianhuahua.com/cjback/notify',//异步回调地址  测试地址
    'url'           =>'https://pay.chanpay.com/mag-unify/gateway/receiveOrder.do?',//请求第三方接口
    "InputCharset"  =>"UTF-8",//编码类型
    "SignType"      =>"RSA",//加密方式
    "server_key"    =>"MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDPq3oXX5aFeBQGf3Ag/86zNu0VICXmkof85r+DDL46w3vHcTnkEWVbp9DaDurcF7DMctzJngO0u9OG1cb4mn+Pn/uNC1fp7S4JH4xtwST6jFgHtXcTG9uewWFYWKw/8b3zf4fXyRuI/2ekeLSstftqnMQdenVP7XCxMuEnnmM1RwIDAQAB",//服务器公钥
    "private_key"   =>"-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDtYQBD30y/kk9kSpPuV3TFGNaZQDWqav2qw3anHEa+GAHWB333
eEL33MOe+4GxjmfIOuCfTKpzEhpvM0GbTKW9EPQWxdz44JUtvRrxKzEInOaqFPMT
xVU+/4Zzo8TiOrgCtwbbqgqlDHqwnznu+uLvkULGk2p9b2mqnSxNcMeC7QIDAQAB
AoGACnQJFPQSZyVERcBa/1XumHddi+Yd9uE7RnfRE87U9q4L9qbhzzIGkO+x1aBx
t6XzwAeHuLwhjWuwqlDxhKs9aR5KTVwtDUUFz+jD2ocUpkSGToJYx6vOlhWRTDD9
+cfeWyNzDkU018cRmeCr+gPwUBdaa6U2W+2Fn541SQ4CaSkCQQD/iNCIBpFuL+jx
JBx7Bp/Jn42hnhSZXt0ov2qhaF+Hvvgn+KqMpChE28b6F4V7EdjQc6MfntySRqh2
unMp3KZfAkEA7c+36vOMV1VlkOiGA3FuOIbi6YyGIJXhvJNuSJml1vl78JzaVsgm
7AtnDGYe/s/YKF7AIuWbMoKHwOMavR5iMwJAPyFO23w766v8ca7JNn+xdD9t3zLN
xgJQwyNfNZcymrfMWRuvDuXzaOefJeQvvvLuzLj04Pf/aEf6kLKJhxsTIwJAexz2
EeRT20KSehpmhpHKhekOv+nH5kaxvnZ0uZERkeFGkKIjRpoHzFt61ahok3H2ba/f
uDE1z0hUDz+DMDTykQJBAM06OLPrrlF0wZ5joFhyEECjieRkcnmvEDXua46jnOeY
sPXWMEfIAban6TyZAdBn03K2OIGIqxeEwiVasFkdNVI=
-----END RSA PRIVATE KEY-----",
//私钥
    "public_key"    =>"-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDtYQBD30y/kk9kSpPuV3TFGNaZ
QDWqav2qw3anHEa+GAHWB333eEL33MOe+4GxjmfIOuCfTKpzEhpvM0GbTKW9EPQW
xdz44JUtvRrxKzEInOaqFPMTxVU+/4Zzo8TiOrgCtwbbqgqlDHqwnznu+uLvkULG
k2p9b2mqnSxNcMeC7QIDAQAB
-----END PUBLIC KEY-----",
//公钥
    "action_url" => 'https://pay.chanpay.com/mag-unify/gateway/receiveOrder.do',
];