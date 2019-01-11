<?php

namespace app\commonapi;

class Wxpay {

    private $url;
    private $key;
    private $res_url;
    private $merid;
    private $noncestr;

    /*
     * 初始化数据
     */

    public function __construct($type = 0) {
        if (SYSTEM_ENV == 'prod') {
            if ($type == 1) {
                //线上环境（正常还款商户号）
                $this->merid = "101104116";
                $this->key = "676c5a4bbe914fc6ac1db5fedba1c299";
            } elseif ($type == 2) {
                //线上环境(逾期还款商户号)
                $this->merid = "101104115";
                $this->key = "47b6dae645614152806f4618d7219101";
            } else {
                //（先花有信）线上环境(备用还款商户号)
                $this->key = "863f50f44e473e7f56cd017d7a46bc48"; //key
                $this->merid = "101104104"; //商户号
            }
        } elseif (SYSTEM_ENV == 'dev') {
            if ($type == 1) {
                //测试环境（正常还款商户号）
                $this->merid = "101104116";
                $this->key = "676c5a4bbe914fc6ac1db5fedba1c299";
            } elseif ($type == 2) {
                //测试环境(逾期还款商户号)
                $this->merid = "101104115";
                $this->key = "47b6dae645614152806f4618d7219101";
            } else {
                //（先花有信）测试环境(备用还款商户号)
                $this->key = "863f50f44e473e7f56cd017d7a46bc48"; //key
                $this->merid = "101104100"; //商户号
            }
        } else {
            if ($type == 1) {
                //测试环境（正常还款商户号）
                $this->merid = "101104116";
                $this->key = "676c5a4bbe914fc6ac1db5fedba1c299";
            } elseif ($type == 2) {
                //测试环境(逾期还款商户号)
                $this->merid = "101104115";
                $this->key = "47b6dae645614152806f4618d7219101";
            } else {
                //（先花有信）测试环境(备用还款商户号)
                $this->key = "863f50f44e473e7f56cd017d7a46bc48"; //key
                $this->merid = "101104100"; //商户号
            }
            //（先花信息）测试环境
//            $this->key = "859b309b814148ed85ca143d1799722e";//key
//            $this->merid = "101104100";//商户号
        }
        //$this->url = "http://pay.ebjfinance.com/wechatpay.php";//普通支付
        $this->url = "http://pay.ebjfinance.com/wechatcompactpay.php"; //精简支付
        $this->res_url = "http://pay.ebjfinance.com/weixin/wechatpayquery.php"; //查询结果url
        $this->noncestr = md5('zfb' . date('YmdHis') . rand(1, 100));
    }

    /**
     * 生成组合参数后的支付url
     * @param string $merchantOutOrderNo 商户订单号，保证唯一
     * @param numeric $orderMoney 订单金额，大于0的数字，保留2位小数
     * @return string
     */
    public function getWxpayUrl($merchantOutOrderNo, $orderMoney) {
        //生成签名并申请支付
        $arr = array(
            "merchantOutOrderNo" => $merchantOutOrderNo,
            "merid" => $this->merid,
            "noncestr" => $this->noncestr,
            "orderMoney" => $orderMoney,
            "orderTime" => date("YmdHis")
        );
        $sign = $this->verificationSign($arr);

        $params = "?merchantOutOrderNo=" . $arr['merchantOutOrderNo'] . "&merid=" . $arr['merid'] . "&noncestr=" . $arr['noncestr'] . "&orderMoney=" . $arr['orderMoney'] . "&orderTime=" . $arr['orderTime'] . "&sign=" . $sign;
        $wxPayURL = $this->url . $params;
        return $wxPayURL;
    }

    /**
     * 支付结果查询
     * @param string $merchantOutOrderNo 商户订单号
     * @return array
      [
     * arraymerid          商户号        varchar        商户id
     * merchantOutOrderNo    商户订单号    varchar        商户订单号，保证唯一
     * orderMoney            订单金额        numeric        订单金额，大于0的数字，保留2位小数
     * orderNo                支付订单号    varchar        平台订单号
     * tradeNo                支付宝交易号    varchar
     * thirdNo                银行交易号    varchar
     * payResult            支付结果        boolean        订单返回结果,true为成功,false为支付失败或者未支付
     * payTime                支付时间        varchar        yyyyMMddHHmmss
     * ]
     *
     */
    public function getPayRes($merchantOutOrderNo) {
        $arr = array(
            "merchantOutOrderNo" => $merchantOutOrderNo,
            "merid" => $this->merid,
            "noncestr" => $this->noncestr
        );
        $sign = $this->verificationSign($arr);
        $arr['sign'] = $sign;
        $result = Http::sendTemplatePost($arr, $this->res_url);
        Logger::errorLog(print_r($result, true), 'result_wxpay', 'wxpay_notify');
        return $result;
    }

    /**
     * 生成签名
     * @param $array_notify
     * @return string
     */
    private function verificationSign($array_notify) {
        $paramkey = array_keys($array_notify);
        sort($paramkey);
        $signstr = '';
        foreach ($paramkey as $key => $val) {
            $signstr .= '&' . $paramkey[$key] . "=" . $array_notify[$val];
        }
        $signstr = substr($signstr, 1);
        //益倍嘉支付分配的密匙
        $key = $this->key;

        $sign = md5($signstr . '&key=' . $key);
        return $sign;
    }

}

?>