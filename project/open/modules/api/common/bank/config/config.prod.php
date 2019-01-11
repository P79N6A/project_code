<?php
   /**
     * 测试环境
     */
return [
    // start 请求路径
    'apiUrl' => 'http://api.hfdatas.com/superapi/super/auth/smrz4',
    // end
    
    // start 平台分配的用户编号
    'userCode' => 'HFJK201701080016340000',
    // end 
    
    // start 平台KEY值
    'desKey' => 'x+aSFQ4+jM6tO/jBC9nluuq6',
    // end
    
    // start 偏移量
    'desIv' => '12345678',
    // end 
    
    // start 应用编号
    'sysCode' => 'HFJKAPP201701080019150000',
    // end 
    
    // start 私钥证书路径
    'privateKey' => dirname(__File__) . '/../key/prod/rsa_private_key.pem',
    //end 
    
    // start 公钥证书路径
    'publicKey' => dirname(__File__) . '/../key/prod/rsa_public_key.pem',
];