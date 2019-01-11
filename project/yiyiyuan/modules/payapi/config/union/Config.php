<?php

namespace app\modules\payapi\config\union;

/**
 * 连连支付配置类
 */
class Config {

    static $base_url   = 'https://yyy.xianhuahua.com';
    static $notify_url = "https://yyy.xianhuahua.com/payapi/unionpay/notify";
    static $secure_key = "c404d6afca19d83dffc1fc5535954372";

    const OID_PARTNER         = "201612161001339313";   #商户号
    const VERSION             = "1.0";                  #版本
    const APP_REQUEST         = 3;              #请求应用标识 1-Android 2-ios 3-WAP
    const SIGN_TYPE           = 'MD5';          #签名方式
    const ID_TYPE             = 0;              #证件类型 0 身份证
    const TRADE_QSTRING_EQUAL = '=';
    const TRADE_QSTRING_SPLIT = '&';
    const VALID_ORDER         = 10080;  # 订单有效时间 默认7天 单位分钟

}
