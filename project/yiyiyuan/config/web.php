<?php

$stuff = '_' . SYSTEM_ENV;
$params = require __DIR__ . "/params{$stuff}.php";
$redis = require __DIR__ . "/redis{$stuff}.php";

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
                '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>'
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
        'redis' => $redis,
        'session' => [
            'class' => 'yii\web\DbSession',
            'db' => 'db',
            'sessionTable' => 'session',
        ],
        'cache' => [
            //'class' => 'yii\caching\FileCache',
            'class' => 'yii\redis\Cache',
        ],
        'user' => [
            'identityClass' => 'app\models\news\User',
            'enableAutoLogin' => true,
        ],
        'backoperate' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\news\Manager',
            'enableAutoLogin' => false,
            'idParam' => '__admin',
            'loginUrl' => '/backoperate/login'
        ],
        // H5用户登录
        'userWap' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\news\User',
            'enableAutoLogin' => false,
            'loginUrl' => '/wap/login/login'
        ],
        // start web端用户登录
        'newDev' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\news\User',
            'enableAutoLogin' => false,
            'loginUrl' => '/borrow/reg/login'
        ],
        // start web端用户登录
        'seven' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\day\User_guide',
            'enableAutoLogin' => false,
            'loginUrl' => '/day/reg'
        ],
        'renew' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\news\User',
            'enableAutoLogin' => false,
            'idParam' => '__renew',
            'loginUrl' => '/renew/login'
        ],
        'mall' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\news\User',
            'enableAutoLogin' => false,
            'loginUrl' => '/new/reg'
        ],
        'userWeb' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
            'idParam' => '__userWeb',
            'loginUrl' => '/default/login'
        ],
        'backstage' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\news\Manager',
            'enableAutoLogin' => false,
            'idParam' => '__admin',
            'loginUrl' => '/backstage/login'
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
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
        'db' => $db['db'],
        'dbxs' => $db['dbxs'],
        'dbown' => $db['dbown'],
        'dbxsnew' => $db['dbxsnew'],
        'dbanalysis' => $db['dbanalysis'],
    ],
    'modules' => [

        'dev' => [
            'class' => 'app\modules\dev\DevModule',
        ],
        'backend' => [
            'class' => 'app\modules\backend\BackendModule',
        ],
        'backoperate' => [
            'class' => 'app\modules\backoperate\BackoperateModule',
        ],
        'app' => [
            'class' => 'app\modules\app\AppModule',
        ],
        'background' => [
            'class' => 'app\modules\background\BackgroundModule',
        ],
        'api' => [
            'class' => 'app\modules\api\ApiModule',
        ],
        'backstage' => [
            'class' => 'app\modules\backstage\BackstageModule',
        ],
        'payapi' => [
            'class' => 'app\modules\payapi\PayapiModule',
        ],
        'wap' => [
            'class' => 'app\modules\wap\WapModule',
        ],
        'new' => [
            'class' => 'app\modules\newdev\NewdevModule',
        ],
        'borrow' => [
            'class' => 'app\modules\borrow\BorrowModule',
        ],
        'sysloan' => [
            'class' => 'app\modules\sysloan\SysloanModule',
        ],
        'seven' => [
            'class' => 'app\modules\seven\SevenModule',
        ],
        'backservice' => [
            'class' => 'app\modules\backservice\BackserviceModule',
        ],
        'guide' => [
            'class' => 'app\modules\guide\GuideModule',
        ],
        'channelapi' => [
            'class' => 'app\modules\channelapi\ChannelApiModule',
        ],
        'jiedianqian' => [
            'class' => 'app\modules\jiedianqian\JiedianqianModule',
        ],
        'foreign' => [
            'class' => 'app\modules\foreign\ForeignModule',
        ],
        'mall' => [
            'class' => 'app\modules\mall\MallModule',
        ],
        'mihuahua' => [
            'class' => 'app\modules\mihuahua\MihuahuaModule',
        ],
        'renew' => [
            'class' => 'app\modules\renew\RenewModule',
        ],
        'day' => [
            'class' => 'app\modules\sevenday\SevendayModule',
        ],
		'sysloanguide' => [
            'class' => 'app\modules\sysloanguide\SysloanguideModule',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
