<?php
   /**
     * 测试环境
     */
return [
    // start 请求路径
    'apiUrl' => 'http://test.api.hfdatas.com/superapi/super/auth/smrz4',
    // end
    
    // start 平台分配的用户编号
    'userCode' => 'TEST10001',
    // end 
    
    // start 平台KEY值
    'desKey' => 'l4mdofLTvHkyONpdlyXBiaTv',
    // end
    
    // start 偏移量
    'desIv' => '12345678',
    // end 
    
    // start 应用编号
    'sysCode' => 'TESTAPP10001',
    // end 
    
    // start 私钥证书路径
    'privateKey' => dirname(__File__) . '/../key/dev/rsa_private_key.pem',
    //end 
    
    // start 公钥证书路径
    'publicKey' => dirname(__File__) . '/../key/dev/rsa_public_key.pem',
];