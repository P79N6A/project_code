<?php
Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/' . SYSTEM_DB . '.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
   	'timeZone'=>'Asia/Shanghai',
    'controllerNamespace' => 'app\commands',
    'modules' => [],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db['strategy'],
        'db_yyy' => $db['yyy'],
        'db_antifraud' => $db['antifraud'],
        'db_cloud' => $db['cloud'],
        'db_loan' => $db['loan'],
        'db_open' => $db['open'],
        'db_mhh' =>$db['mhh'],
        'db_credit' =>$db['credit'],
        'db_cloudnew' =>$db['cloudnew'],
        'db_ygy' =>$db['ygy'],
        'db_tidb' =>$db['tidb'],
    ],
    'params' => $params,
];
