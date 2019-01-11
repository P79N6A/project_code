<?php
namespace app\modules\api\common\baofoo\functions;

class SignatureUtils{
     /**
      * 数据签名
      * @param type $Data  原数据
      * @param type $PfxPath 私钥路径
      * @param type $Pwd 私钥密码
      * @return string 失败'' 成功签名串
      */
     public static function Sign($Data,$PfxPath,$Pwd) {
        if (!function_exists( 'hex2bin')) {
            Logger::dayLog('bfxy', 'addSign_Fail: hex2bin PHP5.4及以上版本支持此函数，也可自行实现！');
            return '';
        }
        if(!file_exists($PfxPath)) {
            Logger::dayLog('bfxy', 'addSign_Fail: 私钥文件不存在！路径：'.$PfxPath);
            return '';
        }

        $pkcs12 = file_get_contents($PfxPath);
        $PfxPathStr=array();
        if (openssl_pkcs12_read($pkcs12, $PfxPathStr, $Pwd)) {
            $PrivateKey = $PfxPathStr['pkey'];
            $BinarySignature=NULL;
            if (openssl_sign($Data, $BinarySignature, $PrivateKey, OPENSSL_ALGO_SHA1)) {
                return bin2hex($BinarySignature);
            } else {
                Logger::dayLog('bfxy', 'addSign_Fail: 加签异常！');
                return '';
            }
        } else {
            Logger::dayLog('bfxy', 'addSign_Fail: 私钥读取异常【密码和证书不匹配】！');
            return '';
        }
    }

    /**
     * 验证签名自己生成的是否正确
     * @param string $Data 签名的原文
     * @param string $CerPath  公钥路径
     * @param string $SignaTure 签名
     * @return bool
     */
    public static function VerifySign($Data,$CerPath,$SignaTure) {
        if (!function_exists( 'hex2bin')) {
            Logger::dayLog('bfxy', 'checkSign_Fail: hex2bin PHP5.4及以上版本支持此函数，也可自行实现！');
            return false;
        }
        if(!file_exists($CerPath)) {
            Logger::dayLog('bfxy', 'checkSign_Fail: 公钥文件不存在！路径：'.$CerPath);
            return false;
        }

        $PubKey = file_get_contents($CerPath);
        $Certs = openssl_get_publickey($PubKey);
        $ok = openssl_verify($Data,hex2bin($SignaTure), $Certs);
        if ($ok == 1) {
            return true;
        }
        return false;
    }
 }