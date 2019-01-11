<?php
$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/' . SYSTEM_DB . '.php');

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
    	'cache' => [
    		'class' => 'yii\caching\FileCache',
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
        //end
    	'session'=>[
    		'class'=>'yii\web\DbSession',
    		'db'=>'db',
    		'sessionTable'=>'session'
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
        'db' => $db['strategy'],
        'db_yyy' => $db['yyy'],
    	'db_antifraud' => $db['antifraud'],
    	'db_cloud' => $db['cloud'],
        'db_loan' => $db['loan'],
        'db_open' =>$db['open'],
        'db_test' =>$db['test'],
        'db_mhh' =>$db['mhh'],
        'db_credit' =>$db['credit'],
        'db_cloudnew' =>$db['cloudnew'],
        'db_ygy' =>$db['ygy'],
        'db_tidb' =>$db['tidb'],
    ],
    'modules' => [
        'api' => ['class' => 'app\modules\api\ApiModule',],
        'sfapi' => ['class' => 'app\modules\sfapi\SfapiModule',],
        'sysapi' => ['class' => 'app\modules\sysapi\SysapiModule',],
        'promeapi' => ['class' => 'app\modules\promeapi\PromeapiModule',],
        'testapi' => ['class' => 'app\modules\testapi\TestapiModule',],
        'peanut' => ['class' => 'app\modules\peanut\PeanutModule',],
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
