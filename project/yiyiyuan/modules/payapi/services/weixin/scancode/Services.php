<?php

namespace app\modules\payapi\services\weixin\scancode;

use app\modules\payapi\services\weixin\scancode\Core;
use app\modules\payapi\services\weixin\scancode\Net;
use app\modules\payapi\config\weixin\ScancodeConfig;
if (function_exists("date_default_timezone_set")) {
    date_default_timezone_set(ScancodeConfig::$timezone);
}

class Services{
    
    public static function trade(Array $params) {
        return Core::createLinkString($params, false, true);
    }

    public static function query(Array $params, Array &$resp) {
        $req_str  = self::buildReq($params);
        $resp_str = Net::sendMessage($req_str, ScancodeConfig::QUERY_URL);
        #edit YangJinlong start(返回结果中没有funcode ，再此处连接上)
        if(!empty($resp_str)){
            parse_str($resp_str , $tmpArr);
            if(!isset($tmpArr[ScancodeConfig::TRADE_FUNCODE_KEY])){
                unset($tmpArr);
                $resp_str .= '&'.ScancodeConfig::TRADE_FUNCODE_KEY .'='.$params[ScancodeConfig::TRADE_FUNCODE_KEY];
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
        if (!isset($para[ScancodeConfig::SIGNATURE_KEY])) {
            return FALSE;
        }
        #edit by YangJinlong end
        $respSignature = $para[ScancodeConfig::SIGNATURE_KEY];
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
            if (isset($para[ScancodeConfig::SIGNATURE_KEY])) {
                $signIsValid = self::verifySignature($para);
            }
            #edit by YangJinlong end
//                $signIsValid=self::verifySignature($para);  #原来的代码
            $resp = $para;
            if ($signIsValid) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

}
