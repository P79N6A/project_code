<?php

namespace app\modules\payapi\controllers;

use Yii;
use app\commonapi\Logger;
use app\commands\SubController;
use app\modules\payapi\services\weixin\scancode\Services;
use app\modules\payapi\config\weixin\ScancodeConfig;

/*
 * 微信扫码支付
 */

class ScancodeController extends SubController {

    public $enableCsrfValidation = false;

    /**
     * @author YangJinlong
     * WP001-消费接口类:
     * 用于对支付信息进行重组和签名，并将请求发往现在支付
     */
    public function actionMain() {
//        $result = 'responseCode=A001&tn=data%3Aimage%2Fpng%3Bbase64%2CiVBORw0KGgoAAAANSUhEUgAAASwAAAEsCAIAAAD2HxkiAAAFvUlEQVR42u3cQZIbMRADQf3%2F0%2Bs3%2BMABQGadFV4Np5OXVvj3JynazxFIEEoQSoJQglAShBKEkiCUIJQEoQShJAglCCVBKEEoCUIJQkkQShBKglCCUBKEEoSSIJQglAShBKEkCCUIJUEoQSgJQglCSRMIfyOlvv%2Fpv%2Fu%2F%2F755%2BOYcIIQQQgghhBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIX0UYW48%2BNnwp5OYBQgghhNChQ2geIIQQQggdOoTmAUIIIYTQoUNoHiCEEEIIb1xSn%2F67qeVy2%2Fnf%2BqMFCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBCCO869NSyO7V8Nw8QQgghhA4dQvMAIYQQQujQITQPEEIIIYQOHULzACGEEELo0Pv%2Bs%2BCVSwRCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBC%2F%2Fnvwvc5%2FbLbMKTO4e75hBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghfA9hW23YXvv8yjxACCGEEELo8xBCCCGEEELo8xBCCCGEEELo8xBCCCGEEL5R29L89NBsDfH8dDkCCCGEEEIIIRSEEEIIIYQQCkIIIYQQQggFIYTXIjy9NE8N8el%2FJ%2FUjhLblfmo%2BIYQQQgghhBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIfz2IduWwuvL9NeeawU%2FhBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgjhqwhT2NaX%2Fm2XVNtzdWKDEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBCCCF8D%2BEK2vXhXl%2B%2Bn37vne8XQgghhBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYTwXoRth7vyUlPPtYL%2FjiU%2BhBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhF8NTduQ3fpjg7ZlfSdOCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBDCexGuvNS2SyT1fdaX8hBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBB2I7x1aduGPPX51PtquzQhhBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQggh3ESeGtbTz5s6%2Fz9BCCGEEEIIoSCEEEIIIYRQEEIIIYQQQgghhBBC%2BATCX6i2IWsbyrZLJHWZQgghhBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQtiNcOVS8H22fgyQRQshhBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQghh67I7tdS%2B9XlXghBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhLAbYdvQnB7ulcvi1nOGEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBCCCGE8I1WMKz8COH09%2BzEBiGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBC%2BB7C30ip50ohaVvub%2BGBEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBCCCGEcG1Jmloupy6LFPK292VZDyGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBC2I1wfQm%2B8lypSyR1SaUuEQghhBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgjvQpg6t7ZhartEtpb4EEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBCCCGEELYiPP09V4Z4ZZm%2BcnlBCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBC%2BG2pl%2FrmMvocttPvF0IIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBCCCGEEMJvD%2F21JXjbJXLrZdQZhBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgjhvQglQShBKAlCCUJJEEoQSoJQglAShBKEkiCUIJQEoQShBKEkCCUIJUEoQSgJQglCSRBKEEqCUIJQEoQShJIglCCUBKEEoSQIJQglHeofHTYCzKS6DI0AAAAASUVORK5CYII%3D&appId=1474856993942270&mhtOrderNo=20161215153425&signType=MD5&nowPayOrderNo=200301201612151540017415245&responseMsg=E000%23%E6%88%90%E5%8A%9F%5B%E6%88%90%E5%8A%9F%5D&funcode=WP001&signature=f68c942da586f49c8f103e9e499c2004&responseTime=20161215154002';
//        $tmpArr = explode('&', $result);
//        $tnStr = explode('=', $tmpArr[1]);
//        $src = urldecode($tnStr[1]);
//        echo "<img src ='$src'>";die;
        $req                      = [];
        $req["mhtOrderName"]      = 'iphone6s';            //商品名称
        $req["mhtOrderAmt"]       = 1;                     //交易金额（单位/分）
        $req["mhtOrderDetail"]    = '购买iphone6s手机一部'; //订单详情;
//        $req["mhtOrderName"]=$_GET["mhtOrderName"];
//        $req["mhtOrderAmt"]=$_GET["mhtOrderAmt"];
//        $req["mhtOrderDetail"]=$_GET["mhtOrderDetail"];
        $req["funcode"]           = ScancodeConfig::TRADE_FUNCODE;
        $req["appId"]             = ScancodeConfig::$appId; //应用ID
        $req["mhtOrderNo"]        = date("YmdHis");
        $req["mhtOrderType"]      = ScancodeConfig::TRADE_TYPE;
        $req["mhtCurrencyType"]   = ScancodeConfig::TRADE_CURRENCYTYPE;
        $req["mhtOrderTimeOut"]   = ScancodeConfig::$trade_time_out;
        $req["mhtOrderStartTime"] = date("YmdHis");
        $req["notifyUrl"]         = ScancodeConfig::$back_notify_url;
        $req["frontNotifyUrl"]    = ScancodeConfig::$front_notify_url;
        $req["mhtCharset"]        = ScancodeConfig::TRADE_CHARSET;
        $req["deviceType"]        = ScancodeConfig::TRADE_DEVICE_TYPE;
        $req["payChannelType"]    = ScancodeConfig::TRADE_PAYCHANNELTYPE;
        $req["outputType"]        = ScancodeConfig::TRADE_OUTPUTTYPE;
        $req["mhtReserved"]       = "test";
        $req["mhtSignature"]      = Services::buildSignature($req);
        $req["mhtSignType"]       = ScancodeConfig::TRADE_SIGN_TYPE;
        $req_str                  = Services::trade($req);
        header("Location:" . ScancodeConfig::TRADE_URL . "?" . $req_str);
    }

    /*
     * @author YangJinlong
     * MQ002-商户支付订单查询
     */

    public function actionQuery() {
        $req                 = array();
        $req["funcode"]      = ScancodeConfig::QUERY_FUNCODE;
        $req["appId"]        = ScancodeConfig::$appId;
        $req["mhtOrderNo"]   = "20161215154002"; //商户欲查询交易订单号
        $req["mhtCharset"]   = ScancodeConfig::TRADE_CHARSET;
        $req["mhtSignature"] = Services::buildSignature($req);
        $req["mhtSignType"]  = ScancodeConfig::TRADE_SIGN_TYPE;

        $resp   = array();
        $result = Services::query($req, $resp);
        if ($result) {
            print_r($resp);
        } else {
            echo '签名验证失败';
        }
    }

    /*
     * @author YangJinlong
     * 异步接收回调通知
     * 用于被动接收支付系统发过来的通知信息，并对通知进行验证签名，
     * 通知频率:2min、10min、30min、1h、2h、6h、10h、15h
     */

    public function actionBacknotify() {
        $request = file_get_contents('php://input');
        if (empty($request)) {
            $request = $_POST;
        }
        Logger::errorLog(print_r($request, true), 'scanlog', 'wecharpay');
        parse_str($request, $request_form);
        if (Services::verifySignature($request_form)) {
            $tradeStatus = $request_form['tradeStatus'];
            echo "success=Y";
            if ($tradeStatus != "" && $tradeStatus == "A001") {
                //在这里对数据进行处理
            } else {
                //支付失败
            }
        } else {
            //验证签名失败
        }
    }

}
