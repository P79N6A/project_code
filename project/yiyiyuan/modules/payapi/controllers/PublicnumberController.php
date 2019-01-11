<?php

namespace app\modules\payapi\controllers;

use Yii;
use app\commonapi\Logger;
use app\commands\SubController;
use app\modules\payapi\services\weixin\publicnumber\Services;
use app\modules\payapi\config\weixin\Config;

/*
 * 微信扫码支付
 */

class PublicnumberController extends SubController {

    public $enableCsrfValidation = false;

    /**
     * @author YangJinlong
     * WP001-消费接口类:
     * 用于对支付信息进行重组和签名，并将请求发往现在支付
     */
    public function actionMain() {
        $req["mhtOrderName"]      = 'iphone6s';            //商品名称
        $req["mhtOrderAmt"]       = 1;                 //交易金额（单位/分）
        $req["mhtOrderDetail"]    = '购买iphone6s手机一部'; //订单详情;
//        $req["mhtOrderName"]=$_GET["mhtOrderName"];
//        $req["mhtOrderAmt"]=$_GET["mhtOrderAmt"];
//        $req["mhtOrderDetail"]=$_GET["mhtOrderDetail"];
        $req["funcode"]           = Config::TRADE_FUNCODE;
        $req["appId"]             = Config::$appId; //应用ID
        $req["mhtOrderNo"]        = date("YmdHis");
        $req["mhtOrderType"]      = Config::TRADE_TYPE;
        $req["mhtCurrencyType"]   = Config::TRADE_CURRENCYTYPE;
        $req["mhtOrderStartTime"] = date("YmdHis");
        $req["notifyUrl"]         = Config::$back_notify_url;
        $req["frontNotifyUrl"]    = Config::$front_notify_url;
        $req["mhtCharset"]        = Config::TRADE_CHARSET;
        $req["deviceType"]        = Config::TRADE_DEVICE_TYPE;
        $req["payChannelType"]    = Config::TRADE_PAYCHANNELTYPE_KEY;
        $req["mhtReserved"]       = "test";
        $req["outputType"]        = Config::OUTPUTTYPT;
        $req["mhtSignature"]      = Services::buildSignature($req);
        $req["mhtSignType"]       = Config::TRADE_SIGN_TYPE;
        $req_str                  = Services::trade($req);
        header("Location:" . Config::TRADE_URL . "?" . $req_str);
    }

    /*
     * @author YangJinlong
     * MQ002-商户支付订单查询
     */

    public function actionQuery() {
        $req                 = [];
        $req["funcode"]      = Config::QUERY_FUNCODE;
        $req["appId"]        = Config::$appId;
        $req["mhtOrderNo"]   = "20161215102717"; //商户欲查询交易订单号
        $req["mhtCharset"]   = Config::TRADE_CHARSET;
        $req["mhtSignature"] = Services::buildSignature($req);
        $req["mhtSignType"]  = Config::TRADE_SIGN_TYPE;

        $resp   = [];
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
        Logger::errorLog(print_r($request, true), 'log', 'wecharpay');
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
