<?php
namespace app\modules\api\common;
/**
 * aes简单的签名认证
 */
class ServiceSign 
{
    private $config;

    public function __construct() {
        // 获取配置文件
        $this->config = $this->getConfig();
    }

    /**
     * 手机号黑名单接口加入签名
     * @param [] $data
     */
    public  function setBlackSign($black_params,$time) {
        $service_id = $black_params['service_id'];
        $skey = $black_params['skey'];
        $md5_str = md5($service_id.'&'.$time.'&'.$skey);
        $sign_str = substr($md5_str,0,20);
        return  $sign_str;
    }
    /**
     * @desc 获取服务参数
     * @param  str $cfg 
     * @return  []
     */
    
    private function getParams($service_id) {

    }
    /**
     * @desc 获取配置文件
     * @param  str $cfg 
     * @return  []
     */
    private function getConfig() {
        $configPath = __DIR__ . "/../config/params.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }

    public  function chkSign($chk_arr) {
        $sign = substr($signStr, 0, 32);
        $enRandStr =  substr($signStr, 32);

        $randStr = AES::decode($enRandStr, $this->key);
        $sign2 =  md5(md5($str) . $randStr);

        return $sign === $sign2;
    }

    public function randStr(){
        $num = rand(0,10000);
        $num = $num ^ time();
        return $num;
    }
}
