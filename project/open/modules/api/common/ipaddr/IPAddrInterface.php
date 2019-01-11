<?php
namespace app\modules\api\common\ipaddr;

interface IPAddrInterface
{

    /**
     * 获取IP信息
     * @param [type] $params
     * @return void
     */
    public function getIPAddress($params);
}
