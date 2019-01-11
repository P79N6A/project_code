<?php
namespace app\common;
/**
 * aes简单的签名认证
 */
class ApiSign {
    private $key = "CUSO8YSu%TAusi@Q098x735E";
    private $cloud_key = "AUqO8YSu&T3usi%b898xSZ5E";
    /**
     * 加入签名
     * @param [] $data
     */
    public  function signData($data,$type=0) {
        $str = json_encode($data,JSON_UNESCAPED_UNICODE);
        $sign = $this->sign($str,$type);

        $t = [];
        $t['data'] = $str;
        $t['_sign'] = $sign;
        return $t;
    }

    public  function verifyData($str, $signStr,$auth_key = null) {
        $sign = substr($signStr, 0, 32);
        $enRandStr =  substr($signStr, 32);
        if (empty($auth_key)) {
            $auth_key = $this->key;
        }
        $randStr = AES::decode($enRandStr, $auth_key);
        $sign2 =  md5(md5($str) . $randStr);

        return $sign === $sign2;
    }

    public function  sign($str,$type){
        $randStr = $this->randStr();
        $sign = md5(md5($str) . $randStr);
        if ($type == 0) {
            $aesStr = AES::encode($randStr, $this->key);
        } else {
            $aesStr = AES::encode($randStr, $this->cloud_key);
        }
        return  $sign . $aesStr;
    }
    public function randStr(){
        $num = rand(0,10000);
        $num = $num ^ time();
        return $num;
    }

    public  function verifyCloud($str, $signStr) {
        $sign = substr($signStr, 0, 32);
        $enRandStr =  substr($signStr, 32);

        $randStr = AES::decode($enRandStr, $this->cloud_key);
        $sign2 =  md5(md5($str) . $randStr);

        return $sign === $sign2;
    }
}
