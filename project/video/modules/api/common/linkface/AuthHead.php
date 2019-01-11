<?php

namespace app\modules\api\common\linkface;

/* *
 * AuthHead
 * 功能：用于生成商汤V2版本请求API时候需要的head auth,错误码对照
 * 详细：
 * 版本：1.1
 * 日期：2017-04-11
 * 说明：
 */

class AuthHead {

    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * 接口返回错误码处理，下列错误码为接口错误码，统一返回提示
     * @param type $json
     * @param type $type
     * @return type
     */
    public function back($json, $type = 'upload') {
        if (!$json) {
            return json_encode(array('code' => '3000', 'message' => '系统错误'));
        }
        $back = [
            'upload' => [
                '1100' => '账号密码不匹配',
                '1200' => '输入参数无效',
                '1101' => '账号过期',
                '1103' => '该接口没有权限',
                '1002' => '使用频率超过限制',
                '2001' => '下载超时',
                '2002' => '下载出错',
//                '2003' => '图片大小不符合要求',
//                '2004' => '输入内容长度不符合要求',
//                '2005' => '图片类型不符合要求',
//                '2006' => '图片损坏',
            ],
            'idcard' => [
                '1100' => '账号密码不匹配',
                '1200' => '输入参数无效',
                '1101' => '账号过期',
                '1103' => '该接口没有权限',
                '1002' => '使用频率超过限制',
                '2000' => '资源没找到',
                '4001' => '身份证服务失败',
            ],
        ];
        $arr = json_decode($json,TRUE);
        $rsp = $back[$type];
        if (in_array($arr['code'], array_keys($rsp))) {
            return json_encode(array('code' => '3000', 'message' => '系统错误'));
        }
        return $json;
    }

    public function CreateAuth() {
        //生成nonce
        $nonce = $this->makeNonce(16);
        //生成unix 时间戳timestamp
        $timestamp = (string) time();
        //将timestamp、nonce、API_KEY 这三个字符串进行升序排列（依据字符串首位字符的ASCII码)，并join成一个字符串stringSignature
        $stringSignature = $this->makeStringSignature($nonce, $timestamp);
        //对stringSignature和API_SECRET做hamc-sha256 签名，生成signature
        $signature = $this->signString($stringSignature);
        //将签名认证字符串赋值给HTTP HEADER 的 Authorization 中
        $Authorization = "key=" . $this->config['linkface_api_id'] . ",timestamp=" . $timestamp . ",nonce=" . $nonce . ",signature=" . $signature;
        return $Authorization;
    }

    private function signString($string_to_sign) {
        //对两个字符串做hamc-sha256 签名
        return hash_hmac("sha256", $string_to_sign, $this->config['linkface_api_secret']);
    }

    private function makeNonce($length) {
        // 生成随机 nonce。位数可以自己定
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $nonce = '';
        for ($i = 0; $i < $length; $i++) {
            $nonce .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $nonce;
    }

    private function makeStringSignature($nonce, $timestamp) {
        //将timestamp、nonce、API_KEY 这三个字符串进行升序排列（依据字符串首位字符的ASCII码)，并join成一个字符串
        $payload = array(
            'API_KEY' => $this->config['linkface_api_id'],
            'nonce' => $nonce,
            'timestamp' => $timestamp
        );
        //对首字母排序
        sort($payload);
        //join到一个字符串
        $signature = join($payload);
        return $signature;
    }

}
