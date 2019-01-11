<?php
return [

    'antifraud' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_antifraud',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'af_',
        ],
        
    // 一亿元测试数据库
    'yyy' =>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_test',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'yi_'
        ],
    // 决策引擎测试数据库
    'strategy' =>[
      'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_strategy',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'st_'
      ],
      // 信审决策测试数据库
   'cloud' =>[
      'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_cloudnew',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'dc_'
      ],
    //7-14测试数据库
    'loan' =>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_loan',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => ''
    ],
    'open' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_open',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => ''
    ],
    //prome测试数据源
    'test' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_antifraud_history',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'af_',
    ],
    //米花花项目系统数据库
    'mhh' =>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=mhh_test',
        //'dsn' => 'mysql:host=47.93.121.86;dbname=mhh_test',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => ''
    ],
    //智融DB
    'credit' =>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=yx_test',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => ''
    ],
    // 新信审决策数据库
    'cloudnew' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_cloudnew',
        // 'username' => 'root',
        // 'password' => '123456',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => '',
    ],
    // 一个亿数据库
    'ygy' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=ygy_test',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => '',
    ],
    // TIDB数据库
    'tidb' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=analysis_repertory_tidb',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => '',
    ],
];
