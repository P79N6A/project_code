<?php
class queryDtlForm extends Processing {
    //初始化参数
    function init(){
        $this->setParameter("versionId", "3");      //服务版本号
        $this->setParameter("merchantId", "");      //特约商户编号
        $this->setParameter("SettleDate", "");      //查询日期
        $this->setParameter("payAcNo", "");         //支付账户
        $this->setParameter("signType", "");        //签名方式
        $this->setParameter("signature", "");       //签名信息
    }
}
?>