<?php

namespace app\modules\payapi\services\union;

use app\modules\payapi\config\union\Config;
use app\modules\payapi\services\union\llJson;

/**
 * 类名:网络通信类
 * 功能:发送并接受HTTP消息
 */
class Services {

    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
    static function buildRequestForm($para_temp, $url, $method, $button_name='') {
        //待请求参数数组
//		$para = self::buildRequestPara($para_temp);
        $sHtml = "<form id='llpaysubmit' name='llpaysubmit' action='" . $url . "' method='" . $method . "'>";
        $sHtml .= "<input type='hidden' name='req_data' value='" . $para_temp . "'/>";
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='" . $button_name . "'></form>";
        $sHtml = $sHtml . "<script>document.forms['llpaysubmit'].submit();</script>";
        return $sHtml;
    }

    /*
     * 验证数据来源签名
     */

    public static function verifySign($data) {
        if (!isset($data['sign'])) {
            return FALSE;
        }
        $sign       = $data['sign'];
        $linkString = self::buildSign($data, true, true);
        if ($linkString == $sign) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*
     * md5签名
     */

    public static function buildSign($data) {
        $linkString = self::createLinkString($data, true, true);
        $linkString = stripslashes($linkString);
        $linkString .= "&key=" . Config::$secure_key;
        return md5($linkString);
    }

    private static function createLinkString(Array $para, $sort = true, $empty = false) {
        if ($empty) {
            $para = self::checkEmpty($para);
        }
        if ($sort) {
            $para = self::argSort($para);
        }
        $linkStr = '';
        foreach ($para as $key => $value) {
            $linkStr .= $key . '=' . $value . '&';
        }
        $linkStr = substr($linkStr, 0, count($linkStr) - 2);
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        return $linkStr;
    }

    private static function checkEmpty($para) {
        $param = [];
        if (!empty($para)) {
            foreach ($para as $key => $val) {
                $tmp = trim($val);
                if ($tmp != '' && $tmp != null && $tmp != 'sign') {
                    $param[$key] = $val;
                }
            }
        }
        return $param;
    }

    private static function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 签名字符串
     * @param $prestr 需要签名的字符串
     * @param $key 私钥
     * return 签名结果
     */
    static function md5Sign($prestr, $key) {
        $prestr = $prestr . "&key=" . $key;
        //file_put_contents("log.txt","签名原串:".$logstr."\n", FILE_APPEND);
        return md5($prestr);
    }

    /**
     * 验证签名
     * @param $prestr 需要签名的字符串
     * @param $sign 签名结果
     * @param $key 私钥
     * return 签名结果
     */
    static function md5Verify($prestr, $sign, $key) {
        $prestr = $prestr . "&key=" . $key;
        //file_put_contents("log.txt","prestr:".$logstr."\n", FILE_APPEND);
        $mysgin = md5($prestr);
        //file_put_contents("log.txt","1:".$mysgin."\n", FILE_APPEND);
        if ($mysgin == $sign) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    static function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each($para)) {
            if ($key == "sign" || $val == "")
                continue;
            else
                $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    function getJsonVal($json, $k) {
        if (isset($json->{$k})) {
            return trim($json->{$k});
        }
        return "";
    }

    /**
     * 针对return_url验证消息是否是连连支付发出的合法消息
     * @return 验证结果
     */
    static function verifyReturn($res_data) {
        //  file_put_contents("log.txt", "返回结果:" . $res_data . "\n", FILE_APPEND);
        $json        = new llJson();
        //error_reporting(3); 
        //商户编号
        $oid_partner = $json->decode($res_data)->{'oid_partner' };
        //首先对获得的商户号进行比对
        if (trim($oid_partner) != Config::OID_PARTNER) {
            //商户号错误
            return false;
        }

        $obj       = $json->decode($res_data);
        $sign      = $json->decode($res_data)->{'sign' };
        $parameter = [];
        foreach ($obj as $key => $val) {
            $parameter[$key] = $json->decode($res_data)->{$key };
        }

        //生成签名结果
        if (!self::getSignVeryfy($parameter, $sign)) {
            return false;
        }
        return true;
    }

    /**
     * 获取返回时的签名验证结果
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     * @return 签名验证结果
     */
    static function getSignVeryfy($para_temp, $sign) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = self::paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = self::argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = self::createLinkstring($para_sort);

        // file_put_contents("log.txt", "原串:" . $prestr . "\n", FILE_APPEND);
        // file_put_contents("log.txt", "sign:" . $sign . "\n", FILE_APPEND);
        $isSgin = false;
        switch (strtoupper(trim(Config::SIGN_TYPE))) {
            case "MD5" :
                $isSgin = self::md5Verify($prestr, $sign, Config::$secure_key);
                break;
            case "RSA" :
                $isSgin = self::Rsaverify($prestr, $sign);
                break;
            default :
                $isSgin = false;
        }

        return $isSgin;
    }

}
