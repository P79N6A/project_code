<?php

namespace app\modules\payapi\services\weixin\scancode;

use app\modules\payapi\config\weixin\ScancodeConfig;
    /**
     * 
     * @author Jupiter
     * 核心工具类
     * 说明:以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己的需要，按照技术文档编写，并非一定要使用该代码。该代码仅供参考
     */
    class Core{
        public static function paraFilter(Array $params) {
            $result=array();
            $flag= $params[ScancodeConfig::TRADE_FUNCODE_KEY];
            foreach($params as $key => $value){
                if (($flag==ScancodeConfig::TRADE_FUNCODE)&&!($key==ScancodeConfig::TRADE_FUNCODE_KEY||$key==ScancodeConfig::TRADE_DEVICETYPE_KEY
                    ||$key==ScancodeConfig::TRADE_SIGNTYPE_KEY||$key==ScancodeConfig::TRADE_SIGNATURE_KEY)){
                    $result[$key]=$value;
                    continue;
                }
                if(($flag==ScancodeConfig::NOTIFY_FUNCODE||$flag==ScancodeConfig::FRONT_NOTIFY_FUNCODE)&&!($key==ScancodeConfig::SIGNTYPE_KEY||$key==ScancodeConfig::SIGNATURE_KEY)){
                    $result[$key]=$value;
                    continue;
                }
                if (($flag==ScancodeConfig::QUERY_FUNCODE)&&!($key==ScancodeConfig::TRADE_SIGNTYPE_KEY||$key==ScancodeConfig::TRADE_SIGNATURE_KEY
                    ||$key==ScancodeConfig::SIGNTYPE_KEY||$key==ScancodeConfig::SIGNATURE_KEY || $key==ScancodeConfig::TRADE_FUNCODE_KEY)) {
                    $result[$key]=$value;
                    continue;
                }
            }
            return $result;
        }
        
        
        public static function buildSignature(Array $para){
            $param = self::checkEmpty($para);
            $prestr=self::createLinkString($param, true, false);
            $prestr.=ScancodeConfig::TRADE_QSTRING_SPLIT.md5(ScancodeConfig::$secure_key);
            return md5($prestr);
        }
        public static function createLinkString(Array $para,$sort,$encode) {
            if ($sort) {
                $para=self::argSort($para);
            }

            $linkStr = '';
            foreach ($para as $key => $value){
                if ($encode) {
                    $value=urlencode($value);
                }
                $linkStr.=$key.ScancodeConfig::TRADE_QSTRING_EQUAL.$value.ScancodeConfig::TRADE_QSTRING_SPLIT;
            }
            $linkStr=substr($linkStr, 0,count($linkStr)-2);
            return $linkStr;
        }
        private static function argSort($para) {
            ksort($para);
            reset($para);
            return $para;
        }
        
        private static function checkEmpty($para) {
            $param = [];
            if(!empty($para)){
                foreach($para as $key => $val){
                    $tmp = trim($val);
                    if($tmp!='' && $tmp!=null){
                        $param[$key] = $val;
                    }
                }
            }
            return $param;
        }
    }