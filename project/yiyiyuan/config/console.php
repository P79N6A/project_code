<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');
$stuff = '_' . SYSTEM_ENV;
$params = require __DIR__ . "/params{$stuff}.php";
$redis = require __DIR__ . "/redis{$stuff}.php";

$db = require __DIR__ . '/' . SYSTEM_DB . '.php';

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'timeZone' => 'Asia/Shanghai',
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [

        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false, //这句一定有，false发送邮件，true只是生成邮件在runtime文件夹下，不发邮件
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.exmail.qq.com', //每种邮箱的host配置不一样
                'username' => 'noreply@xianhuahua.com',
                'password' => '@#33qazXSW@',
                'port' => '465',
                'encryption' => 'ssl',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => ['noreply@xianhuahua.com' => '先花花开放平台'],
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'redis' => $redis,
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db['db'],
        'dbxs' => $db['dbxs'],
        'dbown' => $db['dbown'],
        'dbxsnew' => $db['dbxsnew'],
        'dbhaotian' => $db['dbhaotian'],
        'dbanalysis' => $db['dbanalysis'],
    ],
    'params' => $params,
];
