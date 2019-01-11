<?php
namespace app\models\phonelab;


interface PhoneInterface
{

    /**
     * 获取号码标签信息
     * @param [type] $params
     * @return void
     */
    public function getPhoneInfo($params);
}
