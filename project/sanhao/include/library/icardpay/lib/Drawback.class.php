<?php
class Drawback extends Processing {
    //初始化参数
    function init(){
        $this->setParameter("versionId", "3");  //服务版本号
        $this->setParameter("merchantId", "");  //商户编号
        $this->setParameter("prdOrdNo", "");    //商品订单号
        $this->setParameter("oriPrdOrdNo", ""); //原商品订单号
        $this->setParameter("retUrl", "");      //同步返回URL
        $this->setParameter("notifyUrl", "");   //异步通知URL
        $this->setParameter("signature", "");   //签名信息
        $this->setParameter("signType", "");    //签名方式
        $this->setParameter("rfReqDate", "");   //退款申请日期
        $this->setParameter("rfAmt", "");       //退款金额
        $this->setParameter("rfSake", "");      //退款理由
        $this->setParameter("custPayAcNo", ""); //买家支付账号
        $this->setParameter("merPayAcNo", "");  //卖方支付账号
    }
}
?>