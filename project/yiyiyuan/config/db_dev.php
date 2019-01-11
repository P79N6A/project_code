<?php

return [
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_test',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
    ],
    'dbxs' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_cloudnew',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'dc_',
    ],
    'dbown' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_test',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'yi_',
    ],
    'dbxsnew' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_cloudnew',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => 'dc_',
    ],
    'dbhaotian' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=xhh_sysloan',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => '',
    ],
    'dbanalysis' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=182.92.80.211;dbname=analysis_repertory',
        'username' => 'xhhadmin',
        'password' => 'Xhuahua#Db!332',
        'charset' => 'utf8',
        'tablePrefix' => '',
    ],
];
