<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/' . SYSTEM_DB . '.php';
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/Shanghai',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'silkeroad',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                '<controller:cjpay>/<action:backpay>/<cfg:\w+>' => '<controller>/<action>',
                '<controller:cjxy>/<action:backpay>/<cfg:\w+>' => '<controller>/<action>',
                '<controller:rbremitback>/<action:rbremit>/<env:\w+>' => '<controller>/<action>',
            ],
        ],
        'memcache' => [
            'class' => 'yii\caching\MemCache',
            'servers' => [
                [
                    'host' => '192.168.1.12',
                    'port' => 15000,
                ],
            ],
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
        /*'session'=>[
        'class'=>'yii\web\DbSession',
        'db'=>'db',
        'sessionTable'=>'session'
        ],*/
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
//        'user' => [
//            'identityClass' => 'app\models\User',
//            'enableAutoLogin' => true,
//        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'idParam' => '__user',
            'loginUrl' => '/dev/default/login',
        ],
        

        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false, //这句一定有，false发送邮件，true只是生成邮件在runtime文件夹下，不发邮件
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.qq.com', //每种邮箱的host配置不一样
                'username' => 'noreply@xianhuahua.com',
                'password' => '@#33qazXSW@',
                'port' => '25',
                'encryption' => 'tls',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => ['noreply@xianhuahua.com' => '先花花开放平台'],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db['pay_system'],

    ],
    'modules' => [
        /*'backend' => [
        'class' => 'app\modules\backend\BackendModule',
        ],*/
        'api' => [
            'class' => 'app\modules\api\ApiModule',
        ],
        'bankauth' => [
            'class' => 'app\modules\bankauth\BankauthModule',
        ],
        
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1'], // 按需调整这里
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1'], // 按需调整这里
    ];
}

return $config;
