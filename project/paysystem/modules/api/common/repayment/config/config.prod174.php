<?php
// 正式账号

return [
    "Version"       =>"1.0",  #版本
    'PayVersion'    => '1', //快捷版本0标准版 1升级版
    "PartnerId"     =>"200001820008",//商户编号
    "query_url"     =>'https://pay.chanpay.com/mag-unify/gateway/receiveOrder.do?',//查询第三方接口
    'NotifyUrl'     =>'http://pay.xianhuahua.com/cjback/notify',//异步回调地址    生产
    //'NotifyUrl'     =>'http://paytest.xianhuahua.com/cjback/notify',//异步回调地址  测试
    'url'           =>'https://pay.chanpay.com/mag-unify/gateway/receiveOrder.do?',//请求第三方接口
    "InputCharset"  =>"UTF-8",//编码类型
    "SignType"      =>"RSA",//加密方式
    "server_key"    =>"MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDv0rdsn5FYPn0EjsCPqDyIsYRawNWGJDRHJBcdCldodjM5bpve+XYb4Rgm36F6iDjxDbEQbp/HhVPj0XgGlCRKpbluyJJt8ga5qkqIhWoOd/Cma1fCtviMUep21hIlg1ZFcWKgHQoGoNX7xMT8/0bEsldaKdwxOlv3qGxWfqNV5QIDAQAB",//服务器公钥
    "private_key"   =>"-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQDVDGfpt4+f0HswjMTBCYQ3DWDWM+M7iNK7SraluB9kvQRbJtSk
S51xBuEmijpehgCwMm0h93T81wrTaIFriI8xmF9IawnPfs9dO7Cabamps7+2qB9O
xeUCrz6sPmG9ICIdGwyvWFKDTm54vK/XbUxGRIRUN6kKRw5s9qM8rACyawIDAQAB
AoGALNNQZcdea7S0xrFHkIoNDHw/HLKMI/GUzR1aMqH70PlIGlmgMfVK6gYVh0Nc
JpkxOeFSPuxO7Afe2j5JxLNV1So388j0Ggl2VuMX/LJxztzdW1R79AwGdOPTgj+V
0ZqEt52E+S9RRxdyT3SFtZ4QifYpaaSURtgbX26Yc+P7I8kCQQDvzSeavlu18q/+
3iesEXjApWos5aIzglZ4wwFP49Q/zCJUW9qSLet4HTFBxsAHcka4wudDPHD+/6T6
SHTALkvHAkEA43CeCjPkj0iJMaEfoV+6Ta4KIZEun6tD9lAvlJ1pb7FgC/KiAq0w
zbuWVLybFlngh0r7L3bNS4HEGyzvAxo8PQJBAI1r1iGChh+lwnlylr75htdGNnnH
64KpKUoK5ykwWapOPyi1CxAOmMG7paL/DZzWnjK0byLN8G3SYn9gX8o/A10CQC7D
ggsw/kajsGm+6kUA7Mp6BWU/d6mmyjOD6zSQZRRtEQWD3uHUAFvcvR0xJmYjFrJa
rWjl8XIb/VllFMO7ijUCQQCYVEfheCJ3aJWQ/mX6bc9j41sMfar9f9epNGstUNYB
2pIbzqcRNSmbPaj+FDjdgSIQT1arGy6upX5FjK24Y85d
-----END RSA PRIVATE KEY-----",
//私钥
    "public_key"    =>"-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDVDGfpt4+f0HswjMTBCYQ3DWDWM+M7iNK7SraluB9kvQRbJtSkS51xBuEmijpehgCwMm0h93T81wrTaIFriI8xmF9IawnPfs9dO7Cabamps7+2qB9OxeUCrz6sPmG9ICIdGwyvWFKDTm54vK/XbUxGRIRUN6kKRw5s9qM8rACyawIDAQAB
-----END PUBLIC KEY-----",
//公钥

    "action_url" => 'https://pay.chanpay.com/mag-unify/gateway/receiveOrder.do',

];