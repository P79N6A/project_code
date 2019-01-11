<?php

namespace app\modules\thirdapi\common\alipay;

use app\modules\thirdapi\common\alipay\AlipayApi;
class AlipayServer {

   public function getAlipayUrl($alipayOrder,$accountInfo){
        if(empty($accountInfo)){
           return false;
        }
        if(empty($alipayOrder)){
            return false;
        }
        $merid = $accountInfo->merid;
        $key   = $accountInfo->key;
        $orderno = $alipayOrder->cli_orderid;
        $amount  = $alipayOrder->amount*100;
        $alipayApi = new AlipayApi($merid,$key);
        $alipayurl = $alipayApi->getAlipayUrl($orderno,$amount);
        return $alipayurl;
   }

}

?>