<?php
Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$stuff = SYSTEM_PROD ? '' : '_test';
$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/' . SYSTEM_DB . '.php');
$ssdb = require __DIR__ . "/ssdb{$stuff}.php";

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
   	'timeZone'=>'Asia/Shanghai',
    'controllerNamespace' => 'app\commands',
    'modules' => [],
    'components' => [
        'ssdb_detail' => $ssdb['detail'],
        'ssdb_address' => $ssdb['address'],
        'ssdb_app' => $ssdb['app'],
        'ssdb_msg' => $ssdb['msg'],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'session' => [
            'class' => 'yii\ssdb\SsdbSession',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db['db'],
        'db_yiyiyuan' =>$db['xhh_yiyiyuan'],
        'db_sparrow' =>$db['xhh_sparrow'],
        'db_cloudnew' =>$db['xhh_cloudnew'],
        'db_analysis_repertory' =>$db['xhh_analysis_repertory'],
        'db_analysis_repertory2' =>$db['xhh_analysis_repertory2'],
        'db_open' =>$db['xhh_open'],
        'db_own_yiyiyuan' =>$db['own_yiyiyuan'],
        'db_anti' =>$db['xhh_anti'],
        'db_sysloan' =>$db['xhh_sysloan'],
        'db_tidb' =>$db['xhh_tidb'],
        'db_write_yyy' =>$db['write_yiyiyuan'],
        'db_yigeyi' =>$db['xhh_yigeyi'],
        'db_write_ygy' =>$db['write_yigeyi'],
		'db_huaka' =>$db['xhh_huaka'],
        'db_write_hk' =>$db['write_huaka'],
    ],
    'params' => $params,
];
