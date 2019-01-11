<?php

/**
 * 主要用于反欺诈模型接口验签传输
 */

namespace app\commonapi;

use app\commonapi\ApiSign;
use Yii;

class ApiYaoyuefu {

    private $apiSignModel;
    private $cryt_key = 'DYBRO4Hv%TAusi@Q098x735E';
    
    public function __construct() {
        $this->apiSignModel = new ApiSign();
        $this->Fundsyaoyuefu = Yii::$app->params['yaoyuefuDomain'];
    }

    public function send($path, $data,$type=1) {
        $data = $this->createSign($data);
        $url = $this->buildUrl($path, $type);
        $ret = $this->curlData($url, $data);
        $result = json_decode($ret, true);
        $isVerify = $this->verifyData($result);
        if ($isVerify) {
            return json_decode($result['data'], true);
        }
        return ['res_code' => 20, 'res_data' => '验签失败'];
    }
    
    private function buildUrl($path, $type) {
        if ($type == 1) {
            return $this->Fundsyaoyuefu . $path;
        } else{
            return $this->Fundsyaoyuefu . $path;
        }
    }

    /**
     * 生成签名进行验证
     * @param type $data
     * @return type
     */
    private function createSign($data) {
        $sign = $this->apiSignModel->signData($data);
        return $sign;
    }

    public function verifyData($result) {
        if (empty($result) || !isset($result['_sign']) || !isset($result['data'])) {
            return FALSE;
        }
        $isVerify = $this->apiSignModel->verifyData($result['data'], $result['_sign']);
        return $isVerify;
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

}
