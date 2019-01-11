<?php
//测试配置文件
$keyPath = Yii::$app->basePath.'/modules/api/common/slbank/key/dev/';
return [
    'gateway' => 'https://bds-openapi.shulidata.com/gateway.do',    // 网关地址
    'getAuthApi' => 'slops.authcoll.channel.page',                  // 生成H5链接接口
    'getResultApi' => 'slops.authcoll.public.query',                // 查询接口

    'orgCode' => 'sl2017121414114666',                              // 商户编号
    'prodCode' => 'BANK',                                           // 业务编码
    'bizType' => 'BIZ_TYPE',                                        // 业务场景
    'charset' => 'UTF-8',                                           // 数据编码

    'encType' => 'AES',                                             // 业务数据加密算法
    'encKey' => 'slsdFdrNt47kQqjQb1CHYQ==',                         // 数立定义的AES
    'signType' => 'RSA',                                            // 签名算法
    'privateKeyFilePath' => $keyPath.'rsa_private_key.pem',         // 商户私钥路径
    'slopsRsaPubKeyFilePath' => $keyPath.'slops_pub_key.pem',       // 数立公钥路径

    'version' => '1.0',                                             // 接口版本
    'sdkVersion' => '1.0',                                          // SDK版本
];