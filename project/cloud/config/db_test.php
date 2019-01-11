<?php
return [
    // 信审决策测试数据库
   'db' =>[
      'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_cloudnew',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'dc_'
      ],
    //一亿元
    'xhh_yiyiyuan' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_test',
      'username' => 'root',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => 'xhh_',
    ],
    //通讯录
    'own_yiyiyuan' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_test',
      'username' => 'root',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => '',
    ],
    //mycat
    'xhh_sparrow' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=sparrow',
      'username' => 'root',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => '',
    ],
    
    //mycat
    'xhh_analysis_repertory' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=analysis_repertory',
      'username' => 'root',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => '',
    ],
        //mycat
    'xhh_analysis_repertory2' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=analysis_repertory',
      'username' => 'root',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => '',
    ],

    'xhh_cloudnew' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_cloudnew',
        'username' => 'root',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => '',
    ],

    //
    'xhh_open' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_open',
      'username' => 'root',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => 'xhh_',
    ],
    'xhh_anti'=> [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_antifraud',
      'username' => 'root',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => 'xhh_',
    ],
    //贷后数据库
    'xhh_sysloan' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_sysloan',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'xhh_',
    ],
    //tidb
    'xhh_tidb' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=analysis_repertory_tidb',
      'username' => 'root',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => '',
    ],
    //write xhh_yiyiyuan
    'write_yiyiyuan' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_test',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => '',
    ],

    //read yigeyi
    'xhh_yigeyi' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=loan_shop_ygy',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => '',
    ],
    //write yigeyi
    'write_yigeyi' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=loan_shop_ygy',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => '',
    ],

	//read huaka
    'xhh_huaka' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=loan_shop',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => '',
    ],
    //write huaka
    'write_huaka' => [
      'class' => 'yii\db\Connection',
      'dsn' => 'mysql:host=182.92.80.211;dbname=loan_shop',
      'username' => 'xhhadmin',
      'password' => 'Xhuahua#Db!332',
      'charset' => 'utf8',
      'tablePrefix' => '',
    ],
];
