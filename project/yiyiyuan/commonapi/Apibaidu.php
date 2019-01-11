<?php

namespace app\commonapi;

class Apibaidu
{
    private $key;

    public function __construct()
    {
        $this->key = 'C8a39eafd5b9029b7dcb2b2c6b62dcaf';
    }

    //post方式请求
    public function httpPost($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    //get方式请求
    public function httpGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    //逆地理编码
    public function sendReverse($gps)
    {
        if (empty($gps)) {
            return false;
        }
        $url = 'http://api.map.baidu.com/geocoder/v2/?callback=renderReverse&callback=0&location=' . $gps . '&output=json&pois=0&ak=' . $this->key;
        $res = $this->httpGet($url);
        Logger::dayLog('baidu/sendReverse', ['gps' => $gps], $res);
        $result = json_decode($res, true);
        if (empty($result) || !is_array($result) || $result['status'] !== 0) {
            return false;
        }
        return $result['result'];
    }
}
