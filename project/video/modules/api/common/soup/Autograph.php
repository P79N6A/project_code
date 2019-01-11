<?php
/**
 * 商汤签名认证
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/16
 * Time: 10:53
 */

namespace app\modules\api\common\soup;

use app\common\Logger;
use yii\helpers\ArrayHelper;

class Autograph
{
    //const API_KEY = "00137141f7634d3399b99ebeef34ebe2";
    //const API_SECRET = "4dea6fda5a814ef497b6b5c46038999c";
    private $api_key;
    private $api_secret;

    private function signString($string_to_sign, $API_SECRET)
    {
        //对两个字符串做hamc-sha256 签名
        return hash_hmac("sha256", $string_to_sign, $API_SECRET);
    }

    private function makeNonce( $length) {
        // 生成随机 nonce。位数可以自己定
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $nonce = '';
        for ( $i = 0; $i < $length; $i++ )  {
            $nonce .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $nonce;
    }

    private function makeStringSignature($nonce,$timestamp,$API_KEY){
        //将timestamp、nonce、API_KEY 这三个字符串进行升序排列（依据字符串首位字符的ASCII码)，并join成一个字符串
        $payload = array(
            'API_KEY' => $this->api_key,
            'nonce' => $nonce,
            'timestamp' => $timestamp
        );
        //对首字母排序
        sort($payload);
        //join到一个字符串
        $signature = join($payload);
        return $signature;
    }

    /**
     * 签名认证
     * 说明：
     *  1.用户自己生成 timestamp（Unix 时间戳）；
     *  2.生成随机数nonce(注：最好是32位的) ;
     *  3.
     *      一）将timestamp、nonce、API_KEY 这三个字符串依据字符串首位字符的ASCII码进行升序排列，并join成一个字符串；
     *      二）然后用API_SECRET对这个字符串做hamc-sha256 签名，以16进制编码；
     *  4.将上述得到的签名结果作为 signature 的值，与 API_KEY, nonce, timestamp 一起放在HTTP HEADER 的 Authorization 中
     * @return string
     */
    public function make($aid = 0)
    {
        $project_info = $this->project($aid);
        Logger::dayLog("soup/make", "请求参数：", $aid.":".json_encode($project_info));
        $this->api_key = ArrayHelper::getValue($project_info, "API_KEY");
        $this->api_secret = ArrayHelper::getValue($project_info, "API_SECRET");

        //生成nonce
        $nonce = $this->makeNonce(16);
        //生成unix 时间戳timestamp
        $timestamp = (string) time();
        //将timestamp、nonce、API_KEY 这三个字符串进行升序排列（依据字符串首位字符的ASCII码)，并join成一个字符串stringSignature
        $stringSignature = $this->makeStringSignature($nonce,$timestamp,$this->api_key);
        //对stringSignature和API_SECRET做hamc-sha256 签名，生成signature
        $signature = $this->signString($stringSignature, $this->api_secret);
        //将签名认证字符串赋值给HTTP HEADER 的 Authorization 中
        $Authorization = "key=".$this->api_key.",timestamp=".$timestamp.",nonce=".$nonce.",signature=".$signature;
        return $Authorization;
    }

    public function sendHttp($authorization)
    {
        $testurl = 'https://v2-auth-api.visioncloudapi.com/info/api';
        $ch = curl_init();
        $header= array(
            'Content-Type: application/json',
            'Authorization: '.$authorization
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, $testurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //打开SSL验证时，需要安装openssl库。也可以选择关闭，关闭会有风险。
        $output = curl_exec($ch);
       # var_dump($output);
        $output_array = json_decode($output,true);
        curl_close($ch);
    }

    /**
     * 项目
     * @param $key
     * @return array|mixed
     */
    private function project($key)
    {
        if (empty($key)){
            $key = 0;
        }
        $default_info = [
            "API_KEY"           => "00137141f7634d3399b99ebeef34ebe2",
            "API_SECRET"        => "4dea6fda5a814ef497b6b5c46038999c",
        ];
        $configPath = __DIR__ . DIRECTORY_SEPARATOR  ."config.php";
        if( !file_exists($configPath) ){
            return $default_info;
        }
        $config = include( $configPath );

        if (in_array($key, $config)){
            return $config[$key];
        }
        //默认值
        return $default_info;

    }
}