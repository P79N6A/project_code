<?php
namespace app\common\sms;
use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;
/**
 * 短信新版
 */
class CSmsNew {

    private $platform_host = "http://msg.xianhuahua.com/push-platform";
    private $platform_host_test = "http://47.93.121.86:8090/push-platform";
    private function getHost(){
        $is_prod = SYSTEM_PROD ? true : false;
        //$is_prod = true;
        $platform_host     = $is_prod ? $this->platform_host: $this->platform_host_test;
        //var_dump($platform_host);
        return $platform_host;
    }
    /**
     * Undocumented function
     * 批量发送营销类短信
     * @return void
     */
    public function sendMarketing($postData){
        if(empty($postData) || !is_array($postData)) return false;
        $msgSendDetails = json_encode($postData,JSON_UNESCAPED_UNICODE);
        $oApi = new SmsApi;
        $data = [
            'msgSendDetails'=>$msgSendDetails//json
        ];
        $host = $this->getHost();
        $url = $host.'/api/sms/v2/send_marketing_sms';
        $result = $oApi->send($url,$data);
        if(empty($result)){
            return ['rsp_code'=>'-1','rsp_msg'=>'响应超时'];
        }
        return json_decode($result,true);

    }
   
    /**
     * Undocumented function
     * 发送触发类短信
     * @return void
     */
    public function sendAuth($postData){   
        $oApi = new SmsApi;
        $host = $this->getHost();
        $url = $host.'/api/sms/v2/send_industry_sms';
        $result = $oApi->send($url,$postData);
        if(empty($result)){
            return ['rsp_code'=>'-1','rsp_msg'=>'响应超时'];
        }
        return json_decode($result,true);

    }

}
