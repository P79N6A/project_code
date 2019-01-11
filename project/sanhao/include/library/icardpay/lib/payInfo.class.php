<?php
/*
1.6	付款通知商城结果
*/
class payInfo extends Processing {
    var $versionId;        //服务版本号
    var $merchantId;       //商户编号
    var $orderId;          //商品订单号
    var $status;           //消息处理状态
    var $cause;            //接收回执消息处理失败原因
    var $verifystring;     //验证摘要字符串
    var $signature;        //签名信息
    
    //初始化参数（顺序不可变，改变顺序奖导致验签失败）
    function init(){
        //初始化准备接收的参数
        $this->setParameter("versionId", "");    //服务版本号
        $this->setParameter("merchantId", "");    //商户编号
        $this->setParameter("orderId", "");       //商品订单号
        $this->setParameter("settleDate", "");    //对账日期
        $this->setParameter("completeDate", "");  //完成时间
        $this->setParameter("status", "");        //账单状态
        //$this->setParameter("verifystring", "");  //验证摘要字符串
        $this->setParameter("notifyTyp", "");     //通知类型
        $this->setParameter("payOrdNo", "");      //支付系统交易号
        $this->setParameter("orderAmt", "");      //订单总金额
        //$this->setParameter("notifyUrl", "");     //异步通知URL
        //$this->setParameter("signature", "");     //签名信息
        $this->setParameter("signType", "");      //签名方式
        
        //初始化准备返回的参数
        $this->versionId    ="3";         //服务版本号
        $this->merchantId   ="";          //商户编号
        $this->orderId      ="";          //商品订单号
        $this->status       ="";          //消息处理状态
        $this->cause        ="";          //接收回执消息处理失败原因
        $this->verifystring ="";          //验证摘要字符串
        $this->signature    ="";          //签名信息
    }
    
/*remember to delete*
/*
 * 组织返回数据准备签名
 * return 未签名的数据
 */
function getResponseData() {
    $responseData = "versionId=".$this->versionId
                   ."&merchantId=".$this->merchantId
                   ."&orderId=".$this->orderId
                   ."&status=".$this->status
                   ."&cause=".$this->cause
                   ."&verifystring=".$this->verifystring
                   ."&signature=".$this->signature;
    return $responseData;
}

    
/*
 * 对准备返回的数据进行编码
 */
function setDataUrlCode() {
    $this->versionId   = rawurlencode($this->versionId);
    $this->merchantId  = rawurlencode($this->merchantId);
    $this->orderId     = rawurlencode($this->orderId);
    $this->status      = rawurlencode($this->status);
    $this->cause       = rawurlencode($this->cause);
    $this->verifystring= rawurlencode($this->verifystring);
    $this->signature   = rawurlencode($this->signature);
}
/*remember to delete*/
}
?>