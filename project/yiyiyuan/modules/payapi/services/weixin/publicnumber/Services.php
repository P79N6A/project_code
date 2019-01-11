<?php

namespace app\modules\payapi\services\weixin\publicnumber;

use app\modules\payapi\services\weixin\publicnumber\Core;
use app\modules\payapi\services\weixin\publicnumber\Net;
use app\modules\payapi\config\weixin\Config;

if (function_exists("date_default_timezone_set")) {
    date_default_timezone_set(Config::$timezone);
}

class Services {
    public static function trade(Array $params) {
        return Core::createLinkString($params, false, true);
    }

    public static function query(Array $params, Array &$resp) {
        $req_str  = self::buildReq($params);
        $resp_str = Net::sendMessage($req_str, Config::QUERY_URL);
        #edit YangJinlong start(返回结果中没有funcode ，再此处连接上)
        if(!empty($resp_str)){
            parse_str($resp_str , $tmpArr);
            if(!isset($tmpArr[Config::TRADE_FUNCODE_KEY])){
                unset($tmpArr);
                $resp_str .= '&'.Config::TRADE_FUNCODE_KEY .'='.$params[Config::TRADE_FUNCODE_KEY];
            }
        }
        #edit Yangjinlong end 
        return self::verifyResponse($resp_str, $resp);
    }

    public static function buildSignature(Array $params) {
        $filteredReq = Core::paraFilter($params);
        return Core::buildSignature($filteredReq);
    }

    private static function buildReq(Array $params) {
        return Core::createLinkString($params, false, true);
    }

    public static function verifySignature($para) {
        #edit by YangJinlong start（只增加了以下三行代码 未对原来代码修改）
        if (!isset($para[Config::SIGNATURE_KEY])) {
            return FALSE;
        }
        #edit by YangJinlong end
        $respSignature = $para[Config::SIGNATURE_KEY];
        $filteredReq   = Core::paraFilter($para);
        $signature     = Core::buildSignature($filteredReq);
        if ($respSignature != "" && $respSignature == $signature) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function verifyResponse($resp_str, &$resp) {
        if ($resp_str != "") {
            parse_str($resp_str, $para);
            #edit by YangJinlong start
            $signIsValid = FALSE;
            if (isset($para[Config::SIGNATURE_KEY])) {
                $signIsValid = self::verifySignature($para);
            }
            #edit by YangJinlong end
            $resp = $para;
            if ($signIsValid) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

}
