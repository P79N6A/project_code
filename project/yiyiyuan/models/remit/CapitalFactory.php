<?php
/**
 * 资金方接入工厂类
 *
 */
namespace app\models\remit;

class CapitalFactory
{
    private static $capitalMapping = [
            '1'      => 'FundPeanut',
            '2'      => 'FundJf',
            '5'      => 'FundXiaonuo',
            '6'      => 'FundWeism',
            '10'     => 'FundCunguan',
            '11'     => 'FundPeanut',
    ];

    /**
     * 实例化资方操作对象
     *
     * @param string $fundId 资方ID
     * @return obj
     */
    public static function Create($fundId='')
    {
        if (!in_array($fundId, array_keys(self::$capitalMapping))) {
            return null;
        }
        $fundName = self::$capitalMapping[$fundId];
        $className = __NAMESPACE__ . '\\' . $fundName;
        $obj = new $className();
        $interface = __NAMESPACE__ . '\\CapitalInterface';
        if (!$obj instanceof $interface) {
            return null;
        }
        return $obj;
    }
}
