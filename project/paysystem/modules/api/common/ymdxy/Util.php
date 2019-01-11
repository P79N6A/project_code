<?php

namespace app\modules\api\common\ymdxy;
use yii\helpers\ArrayHelper;
use app\common\Logger;
class Util {

    // 异步验签
    public function rsaVerify($data,$config) {
        $prestr = 'MerNo=' . $data['MerNo'] . '&BillNo=' . $data['BillNo'] . '&OrderNo=' . $data['OrderNo'] . '&Amount=' . $data['Amount'] . '&Succeed=' . $data['Succeed'];
        $sign = base64_decode($data['SignInfo']);
        $pubkey = self::_redPubkey($config);
        return openssl_verify($prestr, $sign, $pubkey) == 1 ? true : false;
    }


    private function curl_post($posturl, $poststr, $header = array()) {
        $ch = curl_init();//打开
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);   //设置curl请求最长等待时间，单位：秒
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    //请求Https时需要这两行配置
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_URL, $posturl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $poststr);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function _encrypt($input, $key) {
        //PKCS5Padding
        $input = self::pkcs5Padding($input);
        $data = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $input, MCRYPT_MODE_ECB, "1");
        return base64_encode($data);
    }

    public function _decrypt($sStr, $key) {
        $encryptedData = base64_decode($sStr);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encryptedData, MCRYPT_MODE_ECB, "1");
        $decrypted = self::removePadding($decrypted);
        return $decrypted;
    }

    //删除填充符
    private function removePadding($str) {
        $len = strlen($str);
        $newstr = "";
        $str = str_split($str);
        for ($i = 0; $i < $len; $i++) {
            if (!in_array($str[$i], array(chr(0), chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8)))) {
                $newstr .= $str[$i];
            }
        }
        return $newstr;
    }

    private function pkcs5Padding($input) {
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $padding = $size - strlen($input) % $size;
        // 添加Padding
        $input .= str_repeat(chr($padding), $padding);
        return $input;

    }

    // RSA加密
    private function _reaEncodePri($str,$config) {
        $prikey = self::_redPrikey($config);
        return openssl_sign($str, $sign, $prikey, OPENSSL_ALGO_SHA1) ? base64_encode($sign) : false;
    }

    // RSA加密
    private function _reaEncodePub($str,$config) {
        $pubkey = self::_redPubkey($config);
        $crypto = '';
        foreach (str_split($str, 117) as $chunk) {

            openssl_public_encrypt($chunk, $encryptData, $pubkey);

            $crypto .= $encryptData;
        }
        return base64_encode($crypto);
    }

    private function _redPubkey($config) {
        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($config['reapalPublicKey'], 64, "\n") . "-----END PUBLIC KEY-----\n";
        return openssl_pkey_get_public($pem);
    }

    private function _redPrikey($config) {
        $pem = "-----BEGIN RSA PRIVATE KEY-----\n" . chunk_split($config['merchantPrivateKey'], 64, "\n") . "-----END RSA PRIVATE KEY-----\n";
        return openssl_pkey_get_private($pem);
    }

    private function _getRandomString($count) {
        $base = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
        $ret = '';
        $strlen = strlen($base);
        for ($i = 0; $i < $count; ++$i) {
            $ret .= $base[((int)rand(0, $strlen - 1))];
        }

        return $ret;
    }


    /**
     * 一麻袋协议支付--请求第三方
     * @param $url
     * @param $paramArr
     * @param $config
     * @return mixed
     */
    function send_rsa($url,$paramArr, $config){
        $key = self::_getRandomString(16);
        $data_json = json_encode($paramArr, 320);
        $post = [
            'requestTime' => date('YmdHis'),
            'version' => $config['version'],
            'merchantNo' => $config['merchantNo'],
            'requestData' => self::_encrypt(base64_encode($data_json), $key),
            'encryptKey' => self::_reaEncodePub(base64_encode($key),$config),
        ];
        $post['sign'] = self::_reaEncodePri(base64_encode($post['requestData'] . $post['requestTime'] . $post['merchantNo']),$config);
        $poststr = http_build_query($post);
        $res = $this->curl_post($url, $poststr);
        return  $res;
    }

    /**
     * 一麻袋协议支付请求第三方----主动查询
     * 返回xml字符串
     * @param $url
     * @param $paramArr
     * @param $config
     * @return mixed
     */
    function send_querst($url,$paramArr, $config){
        $sing = self::_reaEncodePri($config['merchantNo'],$config);
        $xml = '<?xml version="1.0" encoding="utf-8"?>'.
            '<root  tx="1001">'.
            '<merCode>'.$config['merchantNo'].'</merCode>'.
            '<orderNumber>'.$paramArr['order_no'].'</orderNumber>'.
            '<beginTime></beginTime>'.
            '<endTime></endTime>'.
            '<pageIndex></pageIndex>'.
            '<sign>'.$sing.'</sign>'.
            '<tx>1001</tx>'.
            '</root>';
        $post['requestDomain'] = base64_encode($xml);
        $poststr = http_build_query($post);
        $res = $this->curl_post($url, $poststr);
        return $res;
    }



}