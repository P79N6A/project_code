<?php
/**
 * 融360
 *
 */
namespace app\modules\api\common\rong;
use Yii;
use app\common\Func;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\modules\api\common\rong\Rsa;

class RongApi{
      public $config;
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
        $this->rsaObj = new Rsa($env);
      }

    public function operatorSend($bizData, $method){
        $params = array(
            'method'    => $method,
            'app_id'    => $this->config['appId'],
            'version'   => '1.0',
            'sign_type' => 'RSA',
            'format'    => 'json',
            'timestamp' => time()
        );
        $publicBizData = array(
            'merchant_id' => $this->config['appId'],
            'app_name' => 'xianhuahua',
            'app_version' => '2.0.0',
            'platform' => 'app',
            'notice_url' => 'http://182.92.80.211:8093/api/rongback/callback',
        );
//        if($method != 'tianji.api.tianjireport.collectuser'){
//            $bizData = array_merge($publicBizData,$bizData);//合并$bizData
//        }
        $bizData = array_merge($publicBizData,$bizData);//合并$bizData
//        print_r($bizData);exit;
        $params['biz_data'] = json_encode($bizData);

        $params['sign'] = $this->rsaObj->encode($this->getSignContent($params));
        //Yii::log('################################'.$params['sign']);

        $resp = $this->_crulPost($params, $this->config['apiUrl']);
        return ($resp);
    }

    public function getApiName($method){//获取接口名
        $oldName = 'crawler_api_mobile_v4_';
        $arrayName = $oldName.$method.'_response';
        return $arrayName;
    }


    protected function getSignContent($params){
        ksort($params);

        $i = 0;
        $stringToBeSigned = "";
        foreach ($params as $k => $v) {
            if ($i == 0) {
                $stringToBeSigned .= "$k" . "=" . "$v";
            } else {
                $stringToBeSigned .= "&" . "$k" . "=" . "$v";
            }
            $i++;
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }

    public function writeLog($filename,$data){//融360详情、报告日志 内容为json数据
        $path = '/ofiles/jxl/' . date('Ym/d/') . $filename . '.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $data);
        return $path;
    }

    private function _crulPost($postData, $url=''){
        if(empty($url)){
            //Yii::log('openapi curl post数据时，目标url为空','error');
            return false;
        }

        try
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSLVERSION, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            $res = curl_exec($curl);

            $errno = curl_errno($curl);
            $curlInfo = curl_getinfo($curl);
            $errInfo = 'curl_err_detail: ' . curl_error($curl);
            $errInfo .= ' curlInfo:'. json_encode($curlInfo);

            $arrRet = json_decode($res, true);

            //统一记录日志
            $logLevel = 'info';
            if(!is_array($arrRet) || $arrRet['error']!=200) {
                $logLevel = 'warning';
            }
            curl_close($curl);
        }catch(Exception $e)
        {
            print_r($e->getMessage());
        }

        //Yii::log("openapi curl post url: \t $url \t post: \t " . json_encode($postData) . " \t errno: $errno return: $res " . $errInfo, $logLevel);

        if($arrRet['errno']==0){
            return $arrRet;
        }

        return $arrRet;
    }

}

