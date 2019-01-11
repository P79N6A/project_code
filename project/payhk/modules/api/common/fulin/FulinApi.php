<?php
/**
 * 数据魔盒H5 对接接口公共类
 */
namespace app\modules\api\common\fulin;

use app\common\Logger;
use yii\helpers\ArrayHelper;
use Yii;

class FulinApi
{
    const STATUS_INIT = 0; // 初始  只请求未抓取
    const STATUS_DOING = 1; // 抓取中
    const STATUS_SUCCESS = 2; // 成功
    const STATUS_FAILURE = 11; // 通知失败
    /**
     * 获取配置文件
     * @param $cfg
     * @return mixed
     * @throws \Exception
     */
    public function getConfig()
    {
        $is_prod = SYSTEM_PROD ? true : false;
        $cfg = $is_prod ? "prod" : 'dev';
        $configPath = __DIR__ . DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 100);
        }
        $config = include $configPath;
        return $config;
    }

   /**
    请求孚临接口
    * @param $postdata 三要素
  */
   public function  createResport($postdata){

       if (empty($postdata['name']) || empty($postdata['idCardno']) || empty($postdata['mobile'])) {
           Logger::dayLog('fulin','Request/error','数据不全：',$postdata);
           return false;
       }
       $config = $this->getConfig();
       $data = [
           'customerId' => ArrayHelper::getValue($config,'customerId'),
           'customerProdId' => ArrayHelper::getValue($config,'customerProdId'),
           'name' => ArrayHelper::getValue($postdata,'name'),
           'mobile' => ArrayHelper::getValue($postdata,'mobile'),
           'idCardNo' => ArrayHelper::getValue($postdata,'idCardno'),
           'timestamp' => isset($postdata['timestamp']) ? $postdata['timestamp'] : time() . '001',
       ];
       $signData = [];
       foreach ($data as $key => $v) {
           $signData[] = $key . '=' . $v;
       }
       $str = implode('&', $signData);
       $sign = $this->encrypt($str);//加密
       $data['sign'] = $sign;
       $url =  ArrayHelper::getValue($config,'query_url');
       $res = $this->Posturl($url,json_encode($data, JSON_UNESCAPED_UNICODE));
       $result = $this->decrypt(ArrayHelper::getValue($res,'data'));//解密
       $rst= json_decode($result,true);
       $status = ArrayHelper::getValue($res,'status');
       //200代表请求成功
       if($status == 200){
           return $rst;
       }else{
           //记录返回错误信息
           Logger::dayLog('fulin','result/error','数据不全：',$res);
           return false;
       }

   }

    //加密
    function encrypt($str)
    {
        $config = $this->getConfig();
        $key =  ArrayHelper::getValue($config,'key');
        $size = mcrypt_get_block_size( MCRYPT_DES, MCRYPT_MODE_CBC );
        $str = $this->Pkcs5Pad ( $str, $size );
        $data = strtoupper(bin2hex(mcrypt_encrypt(MCRYPT_DES,$key, $str,  MCRYPT_MODE_CBC,$key)));
        return $data;
    }
    //解密
    function decrypt($str)
    {
        $config = $this->getConfig();
        $key =  ArrayHelper::getValue($config,'key');
        $str = hex2bin($str);
        $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, "cbc", $key);
        $str = $this->pkcs5Unpad( $str );
        return $str;
    }

    function pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen( $text ) % $blocksize);
        return $text . str_repeat( chr( $pad ), $pad );
    }

    function pkcs5Unpad($text)
    {
        $pad = ord ( $text {strlen ( $text ) - 1} );
        if ($pad > strlen ( $text ))
            return false;
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
            return false;
        return substr ( $text, 0, - 1 * $pad );
    }

    /*
     * post application/json请求
    */
    public function Posturl($url,$data){

       $ch = curl_init();//打开
        curl_setopt($ch, CURLOPT_POST, true);//传输方式
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//返回的内容作为变量储存，而不是直接输出
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);//post传输的数据

        curl_setopt($ch, CURLOPT_HEADER,0);//过滤http头
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ));
        $result = curl_exec($ch);
        //var_dump(curl_error($ch));die;//打印错误信息
        curl_close($ch);
        if (empty($result)){
            return false;
        }
        $result_data = json_decode($result, true);
        return $result_data;
    }

    /*
       *$parm  $value  用户传输的数据
       *$parm
       */
    public function sendFormat($value){

        $data=[
            'name'    => ArrayHelper::getValue($value,'name'),
            'mobile'        => ArrayHelper::getValue($value,'mobile'),
            'idcardno'       =>   ArrayHelper::getValue($value,'idCardno'),
            'status'   =>  self::STATUS_INIT,
            'create_time'   =>   date('Y-m-d H:i:s'),

        ];
        return $data;

    }


}