<?php

namespace app\common;

use app\commonapi\Logger;
use Yii;
use app\common\ApiCrypt;

define("XHH_CLIENT_APP_ID17", '260986860209201811');
define("XHH_CLIENT_AUTH_KEY17", '90CdDbCiMc328kMkea3849r34a7YGY');

class Api7ClientCrypt extends ApiCrypt {

    //这两个参数由用户自行设置
    /**
     * 客户自己的app_id;需要在先花花开发平台申请
     */
    private $app_id = XHH_CLIENT_APP_ID17;

    /**
     * 客户自己的auth_key;需要在先花花开发平台申请
     */
    private $auth_key = XHH_CLIENT_AUTH_KEY17; // 

    /**
     * 先花花开发平台地址
     */
    private $selectionDomain;
    private $xhApiDomain;
    private $payApiDomain;
    private $payApiNobelDomain;
    private $youxinDomain;
    private $yaoyuefuDomain;
    private $paySystemApiDomain;
    private $xhhApiDomain; //先花花7-14
    private $videoDomain;
    private $bankauth;

    /**
     * 先花云平台地址
     */
    private $xhCloudDomain = 'http://cloud.xianhuahua.com/'; // 先花云

    public function __construct() {
        $this->selectionDomain = Yii::$app->params['selection_url'];
        $this->xhApiDomain = Yii::$app->params['xhApiDomain'];
        $this->payApiDomain = Yii::$app->params['payApiDomain'];
        $this->payApiNobelDomain = Yii::$app->params['payApiNobelDomain'];
        $this->youxinDomain = Yii::$app->params['youxinDomain'];
        $this->yaoyuefuDomain = Yii::$app->params['yaoyuefuDomain'];
        $this->paySystemApiDomain = Yii::$app->params['paySystemApiDomain'];
        $this->xhhApiDomain = Yii::$app->params['xhhApiDomain'];
        $this->videoDomain = Yii::$app->params['videoDomain'];
        $this->bankauth = Yii::$app->params['bankauth'];
    }

    /**
     * 设置app_id
     * @param $app_id;
     */
    public function setAppId($app_id) {
        $this->app_id = $app_id;
    }

    /**
     * 设置auth
     * @param $app_id;
     */
    public function setAuthKey($auth_key) {
        $this->auth_key = $auth_key;
    }

    /**
     * 获取key
     */
    public function getKey() {
        return $this->auth_key;
    }

    /**
     * 绑定ip和host方便测试
     */
    public function setHost($ip, $domain) {
        $this->hostMap[$domain] = $ip;
    }

    /*
     * @author yangjinlong 
     *  请求新的绑卡地址 向开发平台发送数据包
     */

    public function sents($path, $data, $type = 1) {
        $url = $this->buildUrls($path, $type);
        $data = $this->buildRequest($data);
        $request = ['app_id' => $this->app_id, 'data' => $data];
        return $this->curlByHost($url, $request);
    }

    /**
     * 7-14借款请求接口连接
     * @param $path
     * @param $data
     * @return mixed
     */
    public function sent_loan($path, $data) {
        $url = $this->xhhApiDomain . $path;
        $sign = $this->encrySign($data);
        $data['sign'] = $sign;
        return $this->curlByHost($url, $data);
    }

    /**
     * @author yangjinlong 
     * @param type $path
     * @return type 请求新的绑卡类型地址组合api链接地址
     */
    private function buildUrls($path) {
        return $this->paySystemApiDomain . $path;
    }

    // 向开发平台发送数据包
    public function sent($path, $data, $type = 1) {
        $url = $this->buildUrl($path, $type);
        $data = $this->buildRequest($data);
        $request = ['app_id' => $this->app_id, 'data' => $data];
        return $this->curlByHost($url, $request);
    }

    // 向开发平台发送数据包Rong360
    public function sentRong($path, $data, $type = 1) {
        $url = $this->bankauth;
        $url = $url . $path;
        $data = $this->buildRequest($data);
        $request = ['app_id' => $this->app_id, 'data' => $data];
        return $this->curlByHost($url, $request);
    }

    // 组合api链接地址
    private function buildUrl($path, $type) {
        switch ($type) {
            case 1:
                return $this->xhApiDomain . $path;
                break;
            case 3:
                return $this->payApiNobelDomain . $path;
                break;
            case 4:
                return $this->youxinDomain . $path;
                break;
            case 5:
                return $this->yaoyuefuDomain . $path;
                break;
            case 6:
                return $this->videoDomain . $path;
                break;
            case 7:
                return $this->selectionDomain . $path;
                break;
            default :
                return $this->payApiDomain . $path;
        }
    }

    /**
     * 建立请求
     */
    public function buildRequest($data) {
        return $this->buildData($data, $this->auth_key);
    }

    // 客户端对响应进行解析
    public function parseResponse($data) {
        $return = json_decode($data, true);
        // 判断数据格式是否正确
        if (empty($return) || !is_array($return) || !isset($return['res_code'])) {
            return ['res_code' => 20, 'res_data' => '无响应内容'];
        }
        if ($return['res_code'] != '0') {
            return $return;
        }
        return $this->parseReturnData($return['res_data']);
    }

    /**
     * 直接格式化返回结果
     */
    public function parseReturnData($data) {
        return $this->parseData($data, $this->auth_key);
    }

    /**
     * 指定host的curl请求
     * $hostMap = ['www.baidu.com'=>$ip]
     */
    private function curlByHost($url, $data, $hostMap = null) {
        if (is_array($hostMap)) {
            // 获取host,ip映射关系
            list($host, $ip) = each($hostMap);

            // 将链接指定为ip地址
            $url = str_ireplace($host, $ip, $url);

            // 修改host信息
            $httpHeader = array('Host: ' . $host);
        }
        return $this->curlData($url, $data);
    }

    /**
     * 接口请求方式
     * @param unknown $url
     * @param unknown $data
     * @return mixed
     */
    private function curlData($url, $data, $httpHeader = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);

        if (!empty($httpHeader) && is_array($httpHeader)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
        }

        $ret = curl_exec($ch);

        curl_close($ch);
        return $ret;
    }

    /**
     * 加密数据
     * @param $data
     * @return string
     */
    public function encrySign($data) {
        if (empty($data) || !is_array($data)) {
            return '';
        }
        foreach ($data as &$val) {
            $val = strval($val);
        }
        ksort($data);
        $signstr = http_build_query($data);
        //系统分配的密匙
        $key = Yii::$app->params['app_key'];
        //签名
        $sign = md5($signstr . $key);
        return $sign;
    }

    /**
     * 将金额格式化
     * 单位分转为元
     */
    public static function getAmount($amount) {
        if (empty($amount)) {
            return null;
        }
        $new_amount = round($amount) / 100;
        return $new_amount;
    }

}
