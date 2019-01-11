<?php

return [
    "oid_partner"=> "201704171001649504",   #商户号
    "api_version"=> "1.0",                  #版本
    "app_request"=> 3,              #请求应用标识 1-android 2-ios 3-wap
    "sign_type"=> 'RSA',          #签名方式
    "id_type"=> 0,              #证件类型 0 身份证
    "input_charset"=>strtolower('utf-8'),
    // md5密钥
    'key' => 'c404d6afca19d83dffc1fc5535954372',
    //秘钥格式注意不能修改（左对齐，右边有回车符）  商户私钥，通过openssl工具生成,私钥需要商户自己生成替换，对应的公钥通过商户站上传
    'RSA_PRIVATE_KEY' =>'-----BEGIN RSA PRIVATE KEY-----
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
-----END RSA PRIVATE KEY-----',
    'RSA_PUBLICK_KEY' =>'-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDtYQBD30y/kk9kSpPuV3TFGNaZ
QDWqav2qw3anHEa+GAHWB333eEL33MOe+4GxjmfIOuCfTKpzEhpvM0GbTKW9EPQW
xdz44JUtvRrxKzEInOaqFPMTxVU+/4Zzo8TiOrgCtwbbqgqlDHqwnznu+uLvkULG
k2p9b2mqnSxNcMeC7QIDAQAB
-----END PUBLIC KEY-----',

//连连银通公钥
    'LIANLIAN_PUBLICK_KEY' =>'-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCSS/DiwdCf/aZsxxcacDnooGph3d2JOj5GXWi+
q3gznZauZjkNP8SKl3J2liP0O6rU/Y/29+IUe+GTMhMOFJuZm1htAtKiu5ekW0GlBMWxf4FPkYlQ
kPE0FtaoMP3gYfh+OwI+fIRrpW3ySn3mScnc6Z700nU/VYrRkfcSCbSnRwIDAQAB
-----END PUBLIC KEY-----',
    'pay_url' => 'https://instantpay.lianlianpay.com/paymentapi/payment.htm',
    'query_url' => 'https://instantpay.lianlianpay.com/paymentapi/queryPayment.htm',
    'confirm_url' => 'https://instantpay.lianlianpay.com/paymentapi/confirmPayment.htm',
    'notify_url'=>'http://open.xianhuahua.com/lianback/liannotify/prod4',
];