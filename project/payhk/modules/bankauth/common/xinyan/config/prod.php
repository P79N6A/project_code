<?php
/**
 * 正式
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/18
 * Time: 16:29
 */
header("Content-type: text/html; charset=utf-8");
//====================配置商户的宝付接口授权参数============================
$path = dirname(dirname(__FILE__));
$rsapath = $path.DIRECTORY_SEPARATOR."library".DIRECTORY_SEPARATOR."rsa".DIRECTORY_SEPARATOR;	//证书路径

return [
    //默认编码格式//
    'char_set'              => "UTF-8",
    //商户私钥   ---请修改为自己的//
    'pfxpath'               => $rsapath."xianhuahua_pri.pfx",
    //商户私钥密码 ---请修改为自己的//
    'pfx_pwd'               => "123456",
    //公钥 ---请修改为自己的//
    'cerpath'               => $rsapath."bfkey_8021805860.cer",
    //终端号 ---请修改为自己的//
    'terminal_id'           => "8021805860",
    //商户号 ---请修改为自己的//
    'member_id'             => "8021805860",
    //数据类型////json/xml
    'data_type'             => "json",
    //======2.2.1 银行卡认证(三要素、四要素)======
    //测试地址
    'bankCardAuthUrl'       => "https://api.xinyan.com/bankcard/v3/auth",
    //======2.2.2 银行卡四要素验证短信申请======
    //测试地址
    'authApplyUrl'          => "https://api.xinyan.com/bankcard/v1/authsms",
    //======2.2.3银行卡四要素验证确认======
    //测试地址
    'authConfirmUrl'        =>"https://api.xinyan.com/bankcard/v1/authconfirm",
    //======卡bin======
    #测试地址
    'bankCardBinUrl'        => "https://api.xinyan.com/product/bankcard/v1/bin/info",
];











