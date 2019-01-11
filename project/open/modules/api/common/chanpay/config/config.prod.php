<?php
    /**
     * 生产环境
     */
    /**
     * 接口版本，畅捷支付接口文档参数
     */
    define("chanpay_version", "1.0");//接口版本
    /**
     * 商户号，由畅捷支付提供
     */
    define("chanpay_partner_id", "200000780025");//收银台模式商户号
    /**
     * 商户接口字符集，畅捷支付接口文档参数
     */
    define("chanpay_input_charset", "UTF-8");//接口字符集编码
    /**
     * 3desc加密KEY
     */
    define("chanpay_3des_key", "PZUeaBgNoe8M6AOGmTbsMRzztJOZ1chb");//3des加密key
    /**
     * 商户签名类型，畅捷支付接口文档参数
     */
    define("chanpay_sign_type", "RSA");//签名类型
    /**
     * 商户签名私钥，由商户自己生成
     */
    define("chanpay_rsa_private_key", dirname(__File__) . "/../key/prod/rsa_private_key.pem");//签名私钥
    /**
     * 商户验证签名公钥，由畅捷提供
     */
    define("chanpay_rsa_public_key", dirname(__File__) . "/../key/prod/rsa_public_key.pem");//验证签名公钥
    /**
     *异步回调地址，商户自定义自己系统的回调地址
     */
    define("chanpay_notify_url", "http://open.xianhuahua.com/api/chanpayback/onlinenotify");
    /**
     *同步返回地址，商户自己定义自己系统的同步返回地址
     */
    define("chanpay_return_url", "http://open.xianhuahua.com/api/chanpayback/onlinereturn");
    /**
     *畅捷网关地址
     */
    define("chanpay_net_url", "https://pay.chanpay.com/mag/gateway/receiveOrder.do");
