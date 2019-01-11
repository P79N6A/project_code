<?php

namespace app\modules\payapi\config\yyyweixin;

class Sdkconfig {

    private $cfg = array(
        'url'        => 'https://pay.swiftpass.cn/pay/gateway',
        'mchId'      => '755437000006',
        'key'        => '7daa4babae15ae17eee90c9e',
        'version'    => '1.0',
        'notify_url' => 'https://yyy.xianhuahua.com/payapi/yyysdk/notify',
    );

    public function C($cfgName) {
        return $this->cfg[$cfgName];
    }

}

?>