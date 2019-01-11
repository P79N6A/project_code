<?php
/**
 * 第三方鉴权平台接入工厂类
 *
 */
namespace app\modules\bankauth\common;


use app\models\AuthbankChannel;
use app\models\CardInfoChannel;

class AuthBankFactory
{
    private static $gateWayList;
    private static $cardInfoList;

    /**
     * 获取鉴权通道程序入口
     * @return array
     */
    private static function getGateWayList($type) {
        switch ($type) {
            case 'auth':
                $oAuthChannel = new AuthbankChannel();
                break;
            case 'cardbin':
                $oAuthChannel = new CardInfoChannel();
                break;
            default:
                return array();
                break;
        }
        $oAuthChannels = $oAuthChannel->getByChannelId();
        $gateways = self::mapGetWay($oAuthChannels, $type);
        return $gateways;
    }
    private static function mapGetWay($oChannels, $type) {
        if (empty($oChannels)) {
            ExceptionHandler::make_throw('2000006');
        }
        $gatewayRes = [];
        foreach ($oChannels as $oChannel) {
            switch ($type) {
                case 'auth':
                    $gatewayRes[$oChannel['id']] = $oChannel['gateway'];
                    break;
                case 'cardbin':
                    $gatewayRes[$oChannel['channel_id']] = $oChannel['gateway'];
                    break;
            }
        }
        return $gatewayRes;
    }

    /**
     * 实例化鉴权平台操作对象
     *
     * @param  string  $gateWayId 第三方鉴权平台
     * @return obj
     */
    public static function Create($gateWayId='')
    {
        self::$gateWayList = self::getGateWayList('auth');
        if (empty(self::$gateWayList)||(!in_array($gateWayId, array_keys(self::$gateWayList)))) {
            ExceptionHandler::make_throw('2000010');
        }
        $gateWayName = self::$gateWayList[$gateWayId];
        $className = __NAMESPACE__ . '\\' . $gateWayName;
        $obj = new $className();
        $interface = __NAMESPACE__ . '\\AuthBankInterface';
        if (!$obj instanceof $interface) {
            ExceptionHandler::make_throw('2000011');
        }
        return $obj;
    }
    /**
     * 实例化获取卡bin信息对象
     *
     * @param  string  $gateWayId 第三方鉴权平台
     * @return object
     */
    public static function CreateCardinfo($gateWayId='')
    {
        self::$cardInfoList = self::getGateWayList('cardbin');
        if (empty(self::$cardInfoList)||(!in_array($gateWayId, array_keys(self::$cardInfoList)))) {
            ExceptionHandler::make_throw('2000010');
        }
        $gateWayName = self::$cardInfoList[$gateWayId];
        $className = __NAMESPACE__ . '\\' . $gateWayName;
        $obj = new $className();
        return $obj;
    }
}
