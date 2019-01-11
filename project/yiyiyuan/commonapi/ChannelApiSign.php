<?php
namespace app\commonapi;
/**
 * 导流平台验签
 */
class ChannelApiSign {
    private $sKey = 'A5YTVKJ67H1KZ9ZH';
    public function send($url,$data){
        ksort($data);
        $sign = $this->buildSign($data);
        $data['sign'] = $sign;
        $result = $this->sendHttpRequest($data,$url);
        return $result;
    }
    /**
     * Undocumented function
     * 创建签名
     * @param [type] $data
     * @return void
     */
    public function buildSign($data){
        $sign = '';
        foreach ($data as $key => $val) {
                $sign .= $val;
        }
        $md5Sign = md5($sign);
        $aseSign = $this->encode($md5Sign,$this->sKey);
        return $aseSign;
    }
    private function sendHttpRequest($params, $url) {
        $opts = http_build_query($params);
        //echo $opts;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证HOST
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-type:application/x-www-form-urlencoded;charset=UTF-8',
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $html;
    }
    /**
     * Undocumented function
     * AES加密
     * @param [type] $input
     * @param string $key
     * @return void
     */
    private function encode($input, $key) {

        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);

        $input = $this->pkcs5_pad($input, $size);

        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');

        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);

        mcrypt_generic_init($td, $key, $iv);

        $data = mcrypt_generic($td, $input);

        mcrypt_generic_deinit($td);

        mcrypt_module_close($td);

        $data = base64_encode($data);

        return $data;

    }

    private function pkcs5_pad($text, $blocksize) {

        $pad = $blocksize - (strlen($text) % $blocksize);

        return $text . str_repeat(chr($pad), $pad);

    }
    /**
     * Undocumented function
     * AES解密
     * @param [type] $sStr
     * @param string $sKey
     * @return void
     */
    private function decode($sStr, $sKey) {

        $decrypted = mcrypt_decrypt(

            MCRYPT_RIJNDAEL_128,

            $sKey,

            base64_decode($sStr),

            MCRYPT_MODE_ECB

        );

        $dec_s = strlen($decrypted);

        $padding = ord($decrypted[$dec_s - 1]);

        $decrypted = substr($decrypted, 0, -$padding);

        return $decrypted;

    }



    public function verifyData($data,$sign){
        //测试用 上线后去掉
//        if(isset($data['echo_sign']) && $data['echo_sign']==1){
//            $echoSign = true;
//            unset($data['echo_sign']);
//        }else{
//            $echoSign = false;
//            unset($data['echo_sign']);
//        }
        
        ksort($data);
        $sign_n = $this->buildSign($data);
//        echo $sign_n;die;        
        
        if ($sign_n == $sign) {
            return true;
        }
        return false;
    }
    
}
