<?php
/**
 * 调用淘宝IP库接口
 */
namespace app\modules\api\common\ipaddr\taobao;

use app\common\Curl;
use app\modules\api\common\ipaddr\IPAddrInterface;

class Taobao implements IPAddrInterface
{
    protected static $req_url = "http://ip.taobao.com/service/getIpInfo.php?ip=";

    /**
     * 调用淘宝API获取IP地址信息
     * @param $ip
     * @return array
     */
    public function getIPAddress($ip){
        $url= self::$req_url.$ip;
        $curl = new Curl();
        $result = $curl->get($url);
        $data = json_decode($result, true);
        if($data['code'] != 0){
            return [];
        }
        $ipInfo = self::mergeData($data, $ip);
        return $ipInfo;
    }

    /**
     * @param $arr
     * @return mixed
     */
    protected static function mergeData($data, $ip){
        $ipinfo = $data['data'];
        $res["ip"] = $ip;
        $res["country"] = $ipinfo["country"];
        $res["province"] = $ipinfo["region"];
        $res["city"] = $ipinfo["city"];
        $res["district"] = "";
        $res["street"] = "";
        $res["longitude"] = "";
        $res["latitude"] = "";
        $res["source"] = "taobao";
        return $res;
    }
}