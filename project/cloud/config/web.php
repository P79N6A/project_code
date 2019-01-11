<?php
$stuff = SYSTEM_PROD ? '' : '_test';
$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/' . SYSTEM_DB . '.php');
$ssdb = require __DIR__ . "/ssdb{$stuff}.php";

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone'=>'Asia/Shanghai',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'silkeroad',
        ],
    	'urlManager' => [
    			'enablePrettyUrl' => true,
    			'showScriptName' => false,
    			'rules' => [
    					'<controller:\w+>/<id:\d+>'=>'<controller>/view',
    					'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
    					'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
    				],
    	],
        'ssdb_detail' => $ssdb['detail'],
        'ssdb_address' => $ssdb['address'],
        'ssdb_app' => $ssdb['app'],
        'ssdb_msg' => $ssdb['msg'],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'session' => [
            'class' => 'vendor\yii\ssdb\SsdbSession',
        ],
    	
    /*	'session'=>[
    				'class'=>'yii\web\DbSession',
    				'db'=>'db',
    				'sessionTable'=>'mer_session'
    	],*/
    	
		// start 前端帐号
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'idParam' => '__user',
            'loginUrl'=>'/dev/default/login'
        ],
        //end
		
		// start 后台帐号
        'admin' => [
        	'class' => 'yii\web\User',
		    'identityClass' => 'app\models\Admin',
		    'enableAutoLogin' => false, 
		    'idParam' => '__admin',
		    'loginUrl'=>'/backend/default/login'
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
        'db_open' =>$db['xhh_open'],
        'db_analysis_repertory' =>$db['xhh_analysis_repertory'],
        'db_analysis_repertory2' =>$db['xhh_analysis_repertory2'],
        'db_cloudnew' =>$db['xhh_cloudnew'],
        'db_yiyiyuan' =>$db['xhh_yiyiyuan'],
        'db_anti' =>$db['xhh_anti'],
        'db_sysloan' =>$db['xhh_sysloan'],
        'db_tidb' =>$db['xhh_tidb'],
        'db_write_yyy' =>$db['write_yiyiyuan'],
        'db_yigeyi' =>$db['xhh_yigeyi'],
        'db_write_ygy' =>$db['write_yigeyi'],
		'db_huaka' =>$db['xhh_huaka'],
        'db_write_hk' =>$db['write_huaka'],
    ],
    'modules' => [
        'api' => ['class' => 'app\modules\api\ApiModule',],
        'backend' => ['class' => 'app\modules\backend\BackendModule',],
        'backstage' => ['class' => 'app\modules\backstage\BackstageModule',],
        'service' => ['class' => 'app\modules\service\ServiceModule',],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
      'class' => 'yii\debug\Module',
      'allowedIPs' => ['127.0.0.1'] // 按需调整这里
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
      'class' => 'yii\gii\Module',
      'allowedIPs' => ['127.0.0.1'] // 按需调整这里
    ];
}

return $config;
