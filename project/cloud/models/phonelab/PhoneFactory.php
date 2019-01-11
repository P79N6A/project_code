<?php
/**
 * 获取号码标签信息工厂类
 */
namespace app\models\phonelab;

class PhoneFactory
{
    /**
     * 实例化对象
     *
     * @param  string  $gateWayId 第三方获取号码标签接口
     * @return object
     */
    public static function Create($gateWay)
    {
        $gateWayName = $gateWay['gateway'];
        $className = __NAMESPACE__ . '\\' . $gateWayName;
        $obj = new $className();
        $interface = __NAMESPACE__ . '\\PhoneInterface';
        if (!$obj instanceof $interface) {
            return null;
        }
        return $obj;
    }
}
