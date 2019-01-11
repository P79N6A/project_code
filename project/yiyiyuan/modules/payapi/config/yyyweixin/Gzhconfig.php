<?php

namespace app\modules\payapi\config\yyyweixin;

class Gzhconfig {

    private $cfg = array(
        //接口请求地址，固定不变，无需修改
        'url'     => 'https://pay.swiftpass.cn/pay/gateway',
        //测试商户号，商户需改为自己的
        'mchId'   => '7551000001',
        //测试密钥，商户需改为自己的
        'key'     => '9d101c97133837e13dde2d32a5054abb',
        //版本号默认为2.0
        'version' => '2.0',
        //公众号异步通知返回地址
        'notify_url' => 'https://yyy.xianhuahua.com/payapi/yyygzhpay/notify',
        //公众号callback_url
        'gzhcallback_url' => 'https://yyy.xianhuahua.com/payapi/yyygzhpay/callback',
    );

    public function C($cfgName) {
        return $this->cfg[$cfgName];
    }

}

?>