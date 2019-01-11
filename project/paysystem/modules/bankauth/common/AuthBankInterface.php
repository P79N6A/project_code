<?php
namespace app\modules\bankauth\common;

interface AuthBankInterface
{

    /**
     * 鉴权请求
     *
     * @param [type] $params
     * @return void
     */
    public function requestAuth($params);


    /**
     * 解除绑卡
     *
     * @param [type] $params1
     * @param [type] $params2
     * @return void
     */
    public function overAuth($params1, $params2);
}
