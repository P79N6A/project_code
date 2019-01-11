<?php
namespace app\common;
/**
 * aes简单的签名认证
 */
class ApiSign {
    private $key = "DYBRO4Hv%TAusi@Q098x735E";
    /**
     * 加入签名
     * @param [] $data
     */
    public  function signData($data) {
        $str = json_encode($data,JSON_UNESCAPED_UNICODE);
        $sign = $this->sign($str);

        $t = [];
        $t['data'] = $str;
        $t['_sign'] = $sign;
        return $t;
    }

    public  function verifyData($str, $signStr) {
        $sign = substr($signStr, 0, 32);
        $enRandStr =  substr($signStr, 32);

        $randStr = AES::decode($enRandStr, $this->key);
        $sign2 =  md5(md5($str) . $randStr);

        return $sign === $sign2;
    }

    public function  sign($str){
        $randStr = $this->randStr();
        $sign = md5(md5($str) . $randStr);
        $aesStr = AES::encode($randStr, $this->key);
        return  $sign . $aesStr;
    }
    public function randStr(){
        $num = rand(0,10000);
        $num = $num ^ time();
        return $num;
    }
}
