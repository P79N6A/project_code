<?php

return [
    "Version"       =>"1.0",  #版本
    'PayVersion'    => '1', //快捷版本0标准版 1升级版
    "PartnerId"     =>"200001840004",//商户编号
    "InputCharset"  =>"UTF-8",//编码类型
    "SignType"      =>"RSA",//加密方式
    "server_key"    =>"MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDPq3oXX5aFeBQGf3Ag/86zNu0VICXmkof85r+DDL46w3vHcTnkEWVbp9DaDurcF7DMctzJngO0u9OG1cb4mn+Pn/uNC1fp7S4JH4xtwST6jFgHtXcTG9uewWFYWKw/8b3zf4fXyRuI/2ekeLSstftqnMQdenVP7XCxMuEnnmM1RwIDAQAB",//服务器公钥
    "private_key"   =>"-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAM1ybmOLXEfpgBo5
EhHatjlN6mbB8LDWfQ3rR8+wfRG4Kmq1DyEhytCYAcuBIu0boKzjhx3HTW5AMnRn
BoafKx/J0lGhBv5LardqsU5heCxpGUN2utv9L5T4sVOQ8L2mbho/PvUY8ol83DN5
rAwjwuZOXgZ16eORR4eW0i3IHrNrAgMBAAECgYBLUgeskRws9StU8dVxHEkwayNj
tviiLJC+eKLkPuUriORsKKM6V2Q+42vNCzQdz8IxgF06CqaVpA2bZWYcFuC853AO
jA71P5ZEKNOxKsBRpccAXF1Hzr0/WsbUTh962l93xTFWg8AjGF/tZ6ye47EPrHL5
R0Q8DOuyw4D6x65v0QJBAPAeUnWtZzsAbo7JPjkYcMT6vj0wxixeZu82P3zSAEzE
bhgcTwTykFXrt71CqqF+DbszbUBXYa96lVDAfmP/68MCQQDbCQ2gYuCxW/pi5Gib
rzO1KOhBkPHrfDUvohsMGXHMqn3Jjdv38yhFtbmiHZlLHu7I0QROu7HpY653FVYu
3ac5AkBfcn4uvt4RCwvngEFWqstw0Yc7hZ7Q1jmuju7PrB5oZZCpzt7uRYlwTgG8
nrp69UN6DWg5MkLnYR/neI0FLR7HAkEA0rMzw3w6TwJ+qxCzPEfeQr92JFRNE0zp
UMfsosf7O3kqBXAMEMl8jQpR5wv4AVZhNxYxwZc2fp9gHbeNrwmTqQJBAM5xJC1U
2wefPpjxPL8Fm5ZBc/cjtrEC5lVCxhZrxNRG5v9ycKYxKT9xDviMg0AJPl92ff5w
bzQRdIPN/3Zkhvk=
-----END PRIVATE KEY-----",//私钥
    "public_key"    =>"-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDNcm5ji1xH6YAaORIR2rY5Tepm
wfCw1n0N60fPsH0RuCpqtQ8hIcrQmAHLgSLtG6Cs44cdx01uQDJ0ZwaGnysfydJR
oQb+S2q3arFOYXgsaRlDdrrb/S+U+LFTkPC9pm4aPz71GPKJfNwzeawMI8LmTl4G
denjkUeHltItyB6zawIDAQAB
-----END PUBLIC KEY-----",//公钥
    "action_url" => 'https://pay.chanpay.com/mag-unify/gateway/receiveOrder.do',
];




