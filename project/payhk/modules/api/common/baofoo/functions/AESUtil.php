<?php
namespace app\modules\api\common\baofoo\functions;
class AESUtil{
    /**
     * AES加密
     * @param type $data    待加密的数据
     * @param type $key     密钥
     * @return string 失败'' 成功加密串
     */
    public static function AesEncrypt($data,$key){
        if (!function_exists( 'bin2hex')) {
            Logger::dayLog('bfxy', 'aesEncrypt_Fail: bin2hex PHP5.4及以上版本支持此函数，也可自行实现！');
            return '';
        }
        if(!(strlen($key) == 16)){
            Logger::dayLog('bfxy', 'aesEncrypt_Fail: AES密码长度固定为16位！当前KEY长度为：'.strlen($key));
            return '';
        }
        $iv=$key;//偏移量与key相同
        $encrypted=mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key,$data,MCRYPT_MODE_CBC,$iv);
        $data=bin2hex($encrypted);
        return $data;
    }
    /**
     * 解密
     * @param type $sData   待解密的数据
     * @param type $sKey    密钥
     * @return string 失败'' 成功解密串
     */
    public static function AesDecrypt($sData,$sKey){
        if (!function_exists( 'hex2bin')) {
            Logger::dayLog('bfxy', 'checkSign_Fail: hex2bin PHP5.4及以上版本支持此函数，也可自行实现！');
            return '';
        }
        if(!(strlen($sKey) == 16)){
            Logger::dayLog('bfxy', 'aesEncrypt_Fail: AES密码长度固定为16位！当前KEY长度为：'.strlen($key));
            return '';
        }
        $iv=$sKey;//偏移量与key相同
        $sData=hex2bin($sData);
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$sKey,$sData,MCRYPT_MODE_CBC,$iv);
    }
}