<?php
// 测试环境配置
return [
    //支付路由
    'pay_system' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=pay_system',
        'username' => 'root',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'pay_',
    ],
    //开发平台
    'xhh_open' =>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_open',
        'username' => 'root',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'xhh_',
    ],
    //一亿元
    'xhh_yyy' =>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_test',
        'username' => 'root',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'yi_',
    ],
    //存管
    'xhh_ourbank' =>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_ourbank',
        'username' => 'root',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'ob_',
    ],
    //债匹
    'xhh_matching' =>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=claim_matching_new',
        'username' => 'root',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'cm_',
    ],
    //米富
    'xhh_peanut' =>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_peanut',
        'username' => 'root',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'pea_',
    ],
    //zrys
    'xhh_yxl'=>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=yx_test',
        #'dsn' => 'mysql:host=127.0.0.1;dbname=xhh_open',
        'username' => 'xhhadmin',
        #'username' => 'root',
        'password' => 'Xhuahua#Db!332',
        #'password' => 'root',
        'charset' => 'utf8',
        'tablePrefix' => 'yx_'
    ],


];
// return [
//     'class' => 'yii\db\Connection',
//     'dsn' => 'mysql:host=127.0.0.1;dbname=xianhuahua',
//     'username' => 'root',
//     'password' => '',
//     'charset' => 'utf8',
//     'tablePrefix' => 'pay_',
// ];