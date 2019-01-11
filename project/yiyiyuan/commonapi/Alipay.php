<?php

namespace app\commonapi;

class Alipay {

    private $url;
    private $key;
    private $res_url;
    private $merid;
    private $noncestr;

    /*
     * 初始化数据
     */

    public function __construct($is_yq = FALSE) {
        if (SYSTEM_ENV == 'prod') {//（先花有信）线上环境
            if ($is_yq) {
                $this->merid = "101104115";
                $this->key = "47b6dae645614152806f4618d7219101";
            } else {
                $this->merid = "101104116";
                $this->key = "676c5a4bbe914fc6ac1db5fedba1c299";
            }
        } elseif (SYSTEM_ENV == 'dev') {
            if ($is_yq) {
                $this->merid = "101104115";
                $this->key = "47b6dae645614152806f4618d7219101";
            } else {
                $this->merid = "101104116";
                $this->key = "676c5a4bbe914fc6ac1db5fedba1c299";
            }
        } else {//（先花信息）测试环境
            if ($is_yq) {
                $this->merid = "101104115";
                $this->key = "47b6dae645614152806f4618d7219101";
            } else {
                $this->merid = "101104116";
                $this->key = "676c5a4bbe914fc6ac1db5fedba1c299";
            }
//            $this->key = "859b309b814148ed85ca143d1799722e";//key
//            $this->merid = "101104100";//商户号
        }
        $this->url = "http://pay.ebjfinance.com/alijspay.php"; //支付请求url 方式二
        $this->al_res_url = "http://pay.ebjfinance.com/alipay/alipayquery.php"; //查询结果url
        $this->wx_res_url = "http://pay.ebjfinance.com/weixin/wechatpayquery.php"; //查询结果url
        $this->noncestr = md5('zfb' . date('YmdHis') . rand(1, 100));
    }

    /**
     * 生成组合参数后的支付url
     * @param string $merchantOutOrderNo 商户订单号，保证唯一
     * @param numeric $orderMoney 订单金额，大于0的数字，保留2位小数
     * @return string
     */
    public function getAlipayUrl($merchantOutOrderNo, $orderMoney) {
        //生成签名并申请支付
        $merid = $this->merid;
        $arr = array(
            "merchantOutOrderNo" => $merchantOutOrderNo,
            "merid" => $merid,
            "noncestr" => $this->noncestr,
            "orderMoney" => $orderMoney,
            "orderTime" => date("YmdHis")
        );
        $sign = $this->verificationSign($arr);
        $params = "?merchantOutOrderNo=" . $arr['merchantOutOrderNo'] . "&merid=" . $merid . "&noncestr=" . $arr['noncestr'] . "&orderMoney=" . $arr['orderMoney'] . "&orderTime=" . $arr['orderTime'] . "&sign=" . $sign;
        $aliPayURL = $this->url . $params;
        return $aliPayURL;
    }

    /**
     * 支付结果查询
     * @param string $merchantOutOrderNo 商户订单号
     * @return array
      [
      arraymerid          商户号	    varchar		商户id
      merchantOutOrderNo	商户订单号	varchar		商户订单号，保证唯一
      orderMoney	        订单金额	    numeric		订单金额，大于0的数字，保留2位小数
      orderNo	            支付订单号	varchar		平台订单号
      tradeNo	            支付宝交易号	varchar
      thirdNo	            银行交易号	varchar
      payResult	        支付结果	    boolean		订单返回结果,true为成功,false为支付失败或者未支付
      payTime	            支付时间	    varchar		yyyyMMddHHmmss
      ]
     *
     */
    public function getPayRes($merchantOutOrderNo, $platform = "al") {
        $merid = $this->merid;
        $arr = array(
            "merchantOutOrderNo" => $merchantOutOrderNo,
            "merid" => $merid,
            "noncestr" => $this->noncestr
        );
        $sign = $this->verificationSign($arr);
        $arr['sign'] = $sign;
        Logger::errorLog(print_r($arr, true), 'result_alipay', 'alipay_notify');
        if ($platform == "wx") {
            $url = $this->wx_res_url;
        } else {
            $url = $this->al_res_url;
        }
        $result = Http::sendTemplatePost($arr, $url);
        Logger::errorLog(print_r($result, true), 'result_alipay', 'alipay_notify');
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