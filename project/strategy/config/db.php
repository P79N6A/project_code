<?php
return [
 	// 一亿元生产数据库
    'yyy' => [
        'class'       => 'yii\db\Connection',
        'dsn'         => 'mysql:host=rr-bp190xh1w7n22flti.mysql.rds.aliyuncs.com;dbname=xhh_yiyiyuan',
        'username'    => 'xhh_super_r',
        'password'    => 'Develop_only%Xhh',
        'charset'     => 'utf8',
        'tablePrefix' => 'yi_',
    ],
	//反欺诈
	'antifraud' => [
        'class' => 'yii\db\Connection',
        'dsn'        => 'mysql:host=rr-bp190xh1w7n22flti.mysql.rds.aliyuncs.com;dbname=xhh_antifraud',
        'username'    => 'xhh_super_r',
        'password'    => 'Develop_only%Xhh',
        'charset'     => 'utf8',
        'tablePrefix' => 'af_',
        ],
    // 决策引擎正式数据库
   	'strategy' =>[
      'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=rdsvfjabqniybn3.mysql.rds.aliyuncs.com;dbname=xhh_strategy',
        'username' => 'xhh_strategy',
        'password' => 'Xhh-sgy-115322st',
        'charset' => 'utf8',
        'tablePrefix' => 'st_'
      ],
    // 信审决策数据库
   	'cloud' =>[
      'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.139.102.5;dbname=xhh_cloud',
       'username' => 'xhh_cloud',
        'password' => 'Cloud_3221_xhh_33EBca',
        'charset' => 'utf8',
        'tablePrefix' => 'dc_',
      ],
    //7-14测试数据库
    'loan' =>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.253.102.118:3003;dbname=youxin',
        'username' => 'youxin_r',
        'password' => 'e*aA^0d)-33$D_4=ass_a!3',
        'charset' => 'utf8',
        'tablePrefix' => ''
    ],
    //开放平台
    'open' => [
        'class' => 'yii\db\Connection',
        'dsn'        => 'mysql:host=rr-bp190xh1w7n22flti.mysql.rds.aliyuncs.com;dbname=xhh_open',
        'username'    => 'xhh_super_r',
        'password'    => 'Develop_only%Xhh',
        'charset'     => 'utf8',
        'tablePrefix' => '',
    ],
    //米花花项目系统数据库
    'mhh' =>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=rr-bp1pk79y50uxbxlue.mysql.rds.aliyuncs.com:3306;dbname=mhh',
        'username' => 'xhh_peanut_read',
        'password' => 'P17888_read%XHHpt',
        'charset' => 'utf8',
        'tablePrefix' => '',
    ],
    //智融DB
    'credit' =>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=rm-bp1yp7s5224ql9n0s.mysql.rds.aliyuncs.com;dbname=youxinling',
        'username' => 'youxinling_r',
        'password' => 'rw1%^&#@$C19YJ+7j2m',
        'charset' => 'utf8',
        'tablePrefix' => ''
    ],
    // 新信审决策数据库
    'cloudnew' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=rds9wfbl3vk7m10p0q9v.mysql.rds.aliyuncs.com;dbname=xhh_cloud',
        'username' => 'xhh_super_r',
        'password' => 'Develop_only%Xhh',
        'charset' => 'utf8',
        'tablePrefix' => '',
    ],
    // 一个亿数据库
    'ygy' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=rr-bp190xh1w7n22flti.mysql.rds.aliyuncs.com;dbname=xhh_yigeyi',
        'username' => 'xhh_yigeyi_r',
        'password' => 'Ogwmf0GGI0pCq5#H',
        'charset' => 'utf8',
        'tablePrefix' => '',
    ],
    // TIDB数据库
    'tidb' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.253.125.50:4000;dbname=analysis_repertory',
        'username' => 'analysis_r',
        'password' => 'ZT7mDoS#hiIx',
        'charset' => 'utf8',
        'tablePrefix' => '',
    ],
];
