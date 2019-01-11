<?php

namespace app\modules\payapi\config\weixin;

/**
 * 
 * @author Jupiter
 * 配置类
 * 接口相关的配置信息，商户需要配置(appId、secure_key)
 */
class Config {

    static $appId            = "1467368647584712"; //商户的应用ID
    static $secure_key       = "e0DvRgKS5u4pS3f4wkR6vrrJFz0o6x79"; //商户的秘钥
    static $timezone         = "Asia/Shanghai"; //时间时区
    static $front_notify_url = "http://yyy.xianhuahua.com/payapi/publicnumber/frontnotify";
    static $back_notify_url  = "http://yyy.xianhuahua.com/payapi/publicnumber/backnotify";

    const TRADE_URL                = "https://pay.ipaynow.cn";
    const QUERY_URL                = "https://pay.ipaynow.cn";
    const TRADE_FUNCODE            = "WP001";
//    const QUERY_FUNCODE            = "MQ001";  //订单查询定值MQ002
    const QUERY_FUNCODE            = "MQ002";
    const NOTIFY_FUNCODE           = "N001";
    const FRONT_NOTIFY_FUNCODE     = "N002";
    const TRADE_TYPE               = "01";
    const TRADE_CURRENCYTYPE       = "156";
    const TRADE_CHARSET            = "UTF-8";
    const TRADE_DEVICE_TYPE        = "0600";
    const TRADE_SIGN_TYPE          = "MD5";
    const TRADE_QSTRING_EQUAL      = "=";
    const TRADE_QSTRING_SPLIT      = "&";
    const TRADE_FUNCODE_KEY        = "funcode";
    const TRADE_PAYCHANNELTYPE_KEY = "13";
    const TRADE_DEVICETYPE_KEY     = "deviceType";
    const TRADE_SIGNTYPE_KEY       = "mhtSignType";
    const TRADE_SIGNATURE_KEY      = "mhtSignature";
    const SIGNATURE_KEY            = "signature";
    const SIGNTYPE_KEY             = "signType";
    const VERIFY_HTTPS_CERT        = false;
    const OUTPUTTYPT               = 0;   //前期测试以调通接口为主 

}
