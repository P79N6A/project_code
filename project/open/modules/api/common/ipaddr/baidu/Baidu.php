<?php
/**
 * 调用百度IP库接口
 */
namespace app\modules\api\common\ipaddr\baidu;

use app\common\Curl;
use app\modules\api\common\ipaddr\IPAddrInterface;

class Baidu implements IPAddrInterface
{
    protected static $req_url = "http://api.map.baidu.com/location/ip?ak=";
    protected static $ak_key = "keuKY1esNo2AGrkE7o9yqAbn3F5nDuyO&coor=bd09ll";

    /**
     * 调用百度API获取IP地址信息
     * @param $ip
     * @return array
     */
    public function getIPAddress($ip){
        $url =(self::$req_url).(self::$ak_key)."&ip=".$ip;
        $curl = new Curl();
        $result = $curl->get($url);
        $data = json_decode($result, true);
        if($data['status'] != 0){
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
        $addressDetail = $data["content"]["address_detail"];
        $point = $data["content"]["point"];
        $res["ip"] = $ip;
        $res["country"] = "中国";
        $res["province"] = $addressDetail["province"];
        $res["city"] = $addressDetail["city"];
        $res["district"] = $addressDetail["district"];
        $res["street"] = $addressDetail["street"];
        $res["longitude"] = $point["x"];
        $res["latitude"] = $point["y"];
        $res["source"] = "baidu";
        return $res;
    }
}