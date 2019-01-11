<?php
class queryOrderForm extends Processing {
    //初始化参数
    function init(){
        $this->setParameter("versionId", "3");      //服务版本号
        $this->setParameter("merchantId", "");      //特约商户编号
        $this->setParameter("queryType", "");       //查询方式
        $this->setParameter("orderId", "");         //商品订单号
        $this->setParameter("orderDateStart", "");  //查询交易起始日期
        $this->setParameter("orderDateEnd", "");    //查询交易结束日期
        $this->setParameter("prdOrdStatus", "");    //商品订单状态
        $this->setParameter("signature", "");       //签名信息
        $this->setParameter("signType", "");        //签名方式
    }
}
?>