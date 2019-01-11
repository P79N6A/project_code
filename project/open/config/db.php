<?php
// 生产环境配置
return [
    // 开发平台生产数据库
	'xhh_open' =>[
		'class' => 'yii\db\Connection',
		'dsn' => 'mysql:host=rdsvfjabqniybn3.mysql.rds.aliyuncs.com;dbname=xhh_open',
		'username' => 'xhh_open',
		'password' => 'OpenApi_php@XHH',
		'charset' => 'utf8',
		'tablePrefix' => 'xhh_',
    ],
    //一亿元
    'xhh_yyy' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=rr-bp190xh1w7n22flti.mysql.rds.aliyuncs.com:3306;dbname=xhh_yiyiyuan',
        'username' => 'xhh_super_r',
        'password' => 'Develop_only%Xhh',
        'charset' => 'utf8',
        'tablePrefix' => 'yi_',
    ],
];
