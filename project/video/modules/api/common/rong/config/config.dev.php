<?php
   /**
     * 测试环境
     */
return [
    // start 请求路径
    //'apiUrl' => 'https://openapisandbox.rong360.com/gateway',
    'apiUrl' => 'https://openapi.rong360.com/gateway',
    //回调地址
    'notifyUrl' => 'http://182.92.80.211:8093/api/rongback/callback',
    'route_url' => Yii::$app->request->hostInfo.'/api/grabroute/route',
    //'appId' => '1000132',
    'appId' => '2010196',
    // end

    
    // start 私钥证书路径
    'privateKey' => dirname(__File__) . '/../key/dev/rsa_private_key.pem',
    //end 
    
    // start 公钥证书路径
    'publicKey' => dirname(__File__) . '/../key/dev/rsa_public_key.pem',
];