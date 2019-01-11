<?php
// 测试环境配置
return [
    //支付路由
    'pay_system' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=payhk',
        'username' => 'root',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'pay_',
    ],
//    //开发平台
//    'xhh_open' =>[
//        'class' => 'yii\db\Connection',
//        'dsn' => 'mysql:host=182.92.80.211;dbname=payhk',
//        'username' => 'root',
//        'username' => 'xhhadmin',
//        'password' => 'Xhuahua#Db!332',
//        'charset' => 'utf8',
//        'tablePrefix' => 'xhh_',
//    ],



];
// return [
//     'class' => 'yii\db\Connection',
//     'dsn' => 'mysql:host=127.0.0.1;dbname=xianhuahua',
//     'username' => 'root',
//     'password' => '',
//     'charset' => 'utf8',
//     'tablePrefix' => 'pay_',
// ];