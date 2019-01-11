<?php
// 生产环境配置
return [
    'pay_system'=>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=rdsvfjabqniybn3.mysql.rds.aliyuncs.com;dbname=pay_system',
        'username' => 'pay_system',
        'password' => 'Ps_11@3567Xhh',
        'charset' => 'utf8',
        'tablePrefix' => 'pay_',
    ],
    // 开发平台生产数据库
	'xhh_open' =>[
		'class' => 'yii\db\Connection',
		'dsn' => 'mysql:host=rdsvfjabqniybn3.mysql.rds.aliyuncs.com;dbname=xhh_open',
		'username' => 'xhh_open',
		'password' => 'OpenApi_php@XHH',
		'charset' => 'utf8',
		'tablePrefix' => 'xhh_',
    ],

];