<?php
// $service = $_SERVER;
// if (isset($service['REQUEST_URI'])) {
//     $array = explode('/', $service['REQUEST_URI']);
//     if (in_array('entrance', $array)) {
//         $arrays['rsp_code'] = '10080';
//         $arrays['rsp_msg'] = '系统升级，请于05月17日 01：00后进行此操作';
//         echo json_encode($arrays);
//         exit;
//     } else {
//         //重定向浏览器 
//         header("Location: /stop/index.html");
//         //确保重定向后，后续代码不会被执行 
//         exit;
//     }
// } else {
//     //重定向浏览器 
//     header("Location: /stop/index.html");
//     //确保重定向后，后续代码不会被执行 
//     exit;
// }
include(__DIR__ . '/../config/defines.php');
// comment out the following two lines when deployed to production
ini_set('session.gc_maxlifetime', 432000);

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
