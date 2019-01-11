<?php
namespace app\modules\api\common\phonelab;

interface PhoneInterface
{

    /**
     * 获取号码标签信息
     * @param [type] $params
     * @return void
     */
    public function getPhoneInfo($params);
}
