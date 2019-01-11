<?php
class creOrderForm extends Processing {
    //初始化参数
    function init(){
        $this->setParameter("versionId", "3");   //(1,2,3)(必输)服务版本号,本接口版本号:1或2或3,10
        $this->setParameter("merchantId", "");   //(1,2,3)(必输)商户编号,海科融通平台统一分配商户编号,12
        $this->setParameter("orderId", "");      //(1,2,3)(必输)商品订单号,商户平台生成的用于标识该笔订单的唯一号码,50
        $this->setParameter("orderAmount", "");  //(1,2,3)(必输)订单金额,以人民币分为单位,13
        $this->setParameter("orderDate", "");    //(1,2  )(可输)订单日期,YYYY-MM-DD如：（2010-01-01）,10
        $this->setParameter("currency", "RMB");     //(1,2,3)(可输)货币类型,RMB：人民币  其他币种代号另行提供,8
        $this->setParameter("transType", "");    //(1,2  )(可输)交易类别,0101：特约商户电子消费   0102：特约商户实物商品,4
        $this->setParameter("retUrl", "");       //(1,2,3)(必输)异步通知URL,结果返回URL，仅适用于立即返回处理结果的接口。支付系统处理完请求后，立即将处理结果返回给这个URL,200
        $this->setParameter("bizType", "");      //(3    )(必输)商户业务类型,2
        $this->setParameter("returnUrl", "");    //(3    )(必输)同步返回URL,针对该交易的交易状态同步通知接收URL,120
        $this->setParameter("prdDisUrl", "");    //(3    )(可输)商品展示网址,120
        $this->setParameter("prdName", "");      //(3    )(可输)商品名称,50
        $this->setParameter("prdShortName", ""); //(3    )(可输)商品简称,30
        $this->setParameter("prdDesc", "");      //(3    )(可输)商品描述,500
        $this->setParameter("merRemark", "");    //(3    )(可输)商户备注,500
        $this->setParameter("rptType", "");      //(3    )(可输)收款方式,1
        $this->setParameter("prdUnitPrice", ""); //(3    )(可输)商品单价,13
        $this->setParameter("buyCount", "");     //(3    )(可输)购买数量,10
        $this->setParameter("defPayWay", "");    //(3    )(可输)默认支付方式,1
        $this->setParameter("buyMobNo", "");     //(3    )(可输)买方手机号,15
        $this->setParameter("cpsFlg", "");       //(3    )(必输)CPS返利标志,0-否  1-是,1
        $this->setParameter("signType", "");     //(3    )(必输)签名方式,4
    }
}
?>