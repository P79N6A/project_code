<?php
/**
 * 华付金科银行卡四要素验证
 * @author lian0707
 */
namespace app\modules\api\common\bank;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\modules\api\common\bank\Des;
use app\modules\api\common\bank\Rsa;

class HfjkApi{
      private $config;
      private $desObj;
      private $rsaObj;
  
      public function __construct($env){
        /**
         * 账号配置文件
         */
        $configPath = __DIR__ . "/config/config.{$env}.php";
        if( !file_exists($configPath) ){
          throw new \Exception($configPath."配置文件不存在",6000);
        }
        $this->config = include( $configPath );
        $this->desObj = new Des($env);
        $this->rsaObj = new Rsa($env);
      }

       public function send($params){
            $params['header']['userCode'] = $this->config['userCode'];
            $params['header']['sysCode'] = $this->config['sysCode'];
            $jsonParams = json_encode($params);
            unset($params);
            
            //数据加密
            $condition = $this->desObj->encrypt($jsonParams); 

            //生成签名
            $signature = $this->rsaObj->encode($condition);
            unset($this->rsaObj);

            //构造curl请求参数
            $urlParams = array(
              "condition" => $condition,
              "userCode"  => $this->config['userCode'],
              "signature" => $signature,
              "vector"    => $this->config['desIv'],
              );
            $param = http_build_query($urlParams);
            
            //发送请求
            $data = $this->sendPost($this->config['apiUrl'], $param);

            //解析返回数据
            $jsonData = json_decode($data);

            //结果处理
            if(is_object($jsonData)){ 
              if(!empty($jsonData->contents)){ //调用成功
                //解密
                $lastDate = $this->desObj->decrypt($jsonData->contents);
                Logger::dayLog("hfjkapi",$lastDate);
                return json_decode($lastDate);
              }elseif(!empty($jsonData->msg)){ //调用失败
                return $jsonData;
              }
            }else{ 
              return "检查api地址或网络";
            }
          } 

          protected function sendPost($url, $param){
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_POST, true);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($ch, CURLOPT_FRESH_CONNECT, false);
              curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
              curl_setopt($ch, CURLOPT_TIMEOUT, 30);
              curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
              $data = curl_exec($ch);
              curl_close($ch);
              return $data;
          }
}

