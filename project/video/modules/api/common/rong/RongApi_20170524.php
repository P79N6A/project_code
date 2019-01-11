<?php
/**
 * 融360
 *
 */
namespace app\modules\api\common\rong;
use Yii;
use app\common\Func;
use app\models\JxlRequestModel;
use app\modules\api\common\ApiController;
use app\common\Logger;
use app\modules\api\common\rong\Rsa;

class RongApi{
      public $config;
      private $rsaObj;
      private $log;
  
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
        $this->log = new Logger();
      }

    public function getRouteurl(){//返回路由地址
        $route_url = $this->config['route_url'];
        return $route_url;

    }

    public function operatorSend($bizData, $method){//请求融360
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
            'notice_url' => $this->config['notifyUrl'],
        );
//        if($method != 'tianji.api.tianjireport.collectuser'){
//            $bizData = array_merge($publicBizData,$bizData);//合并$bizData
//        }
        $bizData = array_merge($publicBizData,$bizData);//合并$bizData
        $params['biz_data'] = json_encode($bizData);

        $params['sign'] = $this->rsaObj->encode($this->getSignContent($params));
        $resp = $this->_crulPost($params, $this->config['apiUrl']);
        Logger::dayLog('rong','请求数据字段:'.json_encode($params));
        return ($resp);
    }

    public function getApiName($method){//获取接口名
        $oldName = 'crawler_api_mobile_v4_';
        $arrayName = $oldName.$method.'_response';
        return $arrayName;
    }

    public  function getUserdata($uid=0){//抓取用户详情数据
        $bizData = array(
            'user_id' => $uid
        );
        $res = $this->operatorSend($bizData, 'wd.api.mobilephone.getdata');

        if (!is_array($res)) {
            Logger::dayLog('rongback/notify','getUserdata:服务器异常 请重试 userid:'.$uid);
        }elseif($res['error'] != 200){
            Logger::dayLog('rongback/notify','getUserdata:'.$res.'userid:'.$uid);
        }else{//抓取数据成功
            $datalist = $res['wd_api_mobilephone_getdata_response']['data']['data_list'];
            if(empty($datalist)){
                Logger::dayLog('rongback/notify','getUserdata:拉取失败，请重试 userid:'.$uid);
            }else{
                $opr = $datalist[0]['userdata']['user_source'];
                $opr = strtolower($opr);
                $request = new JxlRequestModel();
                $request = $request->getById($uid);
                $request->website = $opr;//用户运营商
                $request->save();

                $this->writeLog($uid.'_detail',json_encode($res));//记录详情json
                return true;
            }
        }

    }

    public function getReport($bizData=array()){//获取运营商报告
        $method = 'tianji.api.tianjireport.collectuser';
        $Data = array(
            'type' => 'mobile',
            'platform' => 'api',
            'notifyUrl' => $this->config['notifyUrl'],
            'version' => '2.0'

        );
        $bizData = array_merge($Data,$bizData);
        $userid = isset($bizData['userId'])?$bizData['userId']:0;
        $res = $this->operatorSend($bizData, $method);
        if (!is_array($res)) {
            Logger::dayLog('rongback/notify','getReport:服务器异常 请重试 userid:'.$userid);
        }elseif($res['error'] != 200){
            Logger::dayLog('rongback/notify','getReport:'.$res.'userid:'.$userid);
        }else{
            Logger::dayLog('rongback/notify','getReport:'.$res);
            $outUniqueId = $res['tianji_api_tianjireport_collectuser_response']['outUniqueId'];
            return $outUniqueId;
        }
    }

    public function getDetail($bizData=array()){//获取运营商报告详情
        $method = 'tianji.api.tianjireport.detail';
        /*$bizData = array(
            'userId' => $this->uid,
            'outUniqueId' => $this->outUniqueId,
            'reportType' => 'html'

        );*/
        $res = $this->operatorSend($bizData, $method);
        $userid = isset($bizData['userId'])?$bizData['userId']:0;
        if (!is_array($res)) {
            Logger::dayLog('rongback/notify','getDetail:服务器异常 请重试 userid:'.$userid);
        }elseif($res['error'] != 200){
            Logger::dayLog('rongback/notify','getDetail:'.$res.'userid:'.$userid);
        }else{
            $url = $this->writeLog($this->uid,json_encode($res));//存储报告json
            $oJxlStat = new JxlStat;
            $request = new JxlRequestModel();
            $postData = [
                'aid' => $this->appData['id'],
                'requestid' => $this->uid,
                'name' => $this->reqData['name'],
                'idcard' => $this->reqData['idcard'],
                'phone' =>  $this->reqData['phone'],
                'website' => 'rong360mobile',
                'is_valid' => 3,
                'url' => $url,
            ];

            //5 保存到DB中
            $result = $oJxlStat->saveStat($postData);
            if (!$result) {
                return $this->dayLog('rong','saveStat','保存失败', $postData);
            }
            $request = $request->getById($this->uid);
            $request->result_status = 1;
            $request->process_code = 10008;
            $request->save();

//            $this->resp(0, $res);
            return $this->resp(0, [
                'requestid' => $this->uid,
                'token' => '',
                'phone' => $this->reqData['phone'],
                'process_code' => 10008,
                'response_type' => '',
                'status' => true,
            ]);
        }
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
        $path = '/ofiles/rong/' . date('Ym/d/') . $filename . '.json';
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

