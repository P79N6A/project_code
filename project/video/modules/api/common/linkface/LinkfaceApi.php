<?php

/**
 * 同盾验证
 * @author gaolian
 */

namespace app\modules\api\common\linkface;

use app\common\Logger;
use Exception;

class LinkfaceApi {

    private $config;

    public function __construct($env) {
        /**
         * 账号配置文件
         */
        $configPath = __DIR__ . "/config.{$env}.php";
        if (!file_exists($configPath)) {
            throw new Exception($configPath . "配置文件不存在", 6000);
        }
        $this->config = include( $configPath );
    }

    /**
     * 商汤人脸对比接口
     * @param identity 身份证号
     * @param $selfie_url 网络图片1
     * @param $historical_selfie_url 网络图片2
     */
    public function linkface($identity, $selfie_url, $historical_selfie_url) {
        //api id
        $api_id = $this->config['linkface_api_id'];
        //api_secret
        $api_secret = $this->config['linkface_api_secret'];
        //请求地址
        $api_url = $this->config['linkface_api_url'];

        $post_data = array(
            'api_id' => $api_id,
            'api_secret' => $api_secret,
            'selfie_url' => $selfie_url,
            'historical_selfie_url' => $historical_selfie_url,
            'selfie_auto_rotate' => true,
            'historical_selfie_auto_rotate' => true,
        );

        $result = $this->curlPost($api_url, $post_data);
        Logger::errorLog($identity . "--" . print_r($result, true), 'linkface');
        return $result;
    }

    /**
     * 验证身份证上的姓名与身份证号是否匹配
     * @param identity 身份证号
     * @param name 姓名
     */
    public function idnumber_verification($identity, $name) {
        //api id
        $api_id = $this->config['linkface_api_id'];
        //api_secret
        $api_secret = $this->config['linkface_api_secret'];
        //请求地址
        $api_url = $this->config['idnumber_verification_api_url'];

        $post_data = array(
            'api_id' => $api_id,
            'api_secret' => $api_secret,
            'id_number' => $identity,
            'name' => $name
        );

        $result = $this->curlPost($api_url, $post_data);
        Logger::errorLog($identity . "--" . print_r($result, true), 'idnumber_verification');
        return $result;
    }

    /**
     * 验证自拍照防伪
     * @param $selfie_url 网络图片1
     */
    public function selfie_hack_detect($selfie_url) {
        //api id
        $api_id = $this->config['linkface_api_id'];
        //api_secret
        $api_secret = $this->config['linkface_api_secret'];
        //请求地址
        $api_url = $this->config['hack_api_url'];

        $post_data = array(
            'api_id' => $api_id,
            'api_secret' => $api_secret,
            'url' => $selfie_url
        );

        $result = $this->curlPost($api_url, $post_data);
        Logger::errorLog("selfie_hack_detect--" . print_r($result, true), 'selfie_hack_detect');
        return $result;
    }

    /**
     * 获取上传商汤的图片里的内容 type=upload
     * @param type $img_id 图片ID
     * @param type $side 'idcard':正反面, 'front':正面, 'back':背面, 'auto':自动
     * @return json
     */
    public function ocr_pic_content($img_id, $side = 'auto') {
        $url = $this->config['h5_ocr_api_url'];
        $post_data = array('image_id' => $img_id);
        $authHead = new AuthHead($this->config);
        $Authorization = $authHead->CreateAuth();
        $header[] = 'Authorization: ' . $Authorization;
        $result = $this->ocrCurlPost($url, $header, $post_data);
        Logger::errorLog("H5--ocr-con-" . print_r($result, true), 'H5_ocr_content');
        return $authHead->back($result,'idcard');
    }

    /**
     * 为了获取图片上的身份信息，先把图片上传到商汤云
     * @param type $pic_url 图片地址
     * @return json
     */
    public function ocr_update_pic($pic_url) {
        $url = $this->config['h5_upload_url'];
        $post_data = array('data' => $pic_url);
        $authHead = new AuthHead($this->config);
        $Authorization = $authHead->CreateAuth();
        $header[] = 'Authorization: ' . $Authorization;
        $result = $this->ocrCurlPost($url, $header, $post_data);
        Logger::errorLog("H5--ocr-up-" . print_r($result, true), 'H5_ocr_up');

        return $authHead->back($result);
    }

    /**
     * POST请求
     */
    private function curlPost($url, $post_data) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //您可以根据需要，决定是否打开SSL验证
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    private function ocrCurlPost($url, $header, $post_data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //打开SSL验证时，需要安装openssl库。也可以选择关闭，关闭会有风险。
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}
