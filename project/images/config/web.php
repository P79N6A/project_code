<?php
$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/' . SYSTEM_DB . '.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone'=>'Asia/Shanghai',
    'defaultRoute' => 'index',
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
    	'cache' => [
    		'class' => 'yii\caching\FileCache',
    	],
    	
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'idParam' => '__user',
            'loginUrl'=>'/dev/default/login'
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
        'db' => $db,
    ],
    'modules' => [
        'backend' => ['class' => 'app\modules\backend\BackendModule',],
        'dev' => ['class' => 'app\modules\dev\DevModule',],
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
