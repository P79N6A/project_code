<?php
/**
 * 融360
 *
 */
namespace app\modules\api\common\rong;
use Yii;
use app\common\Func;
use app\models\JxlRequestModel;
use app\models\RongRequest;
use app\modules\api\common\ApiController;
use app\common\Logger;
use app\modules\api\common\rong\Rsa;
use app\common\Crypt3Des;

class RongApi{
      public $config;
      private $rsaObj;
      private $log;
      public $errorInfo;
      private $rongObj;
  
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
        $this->rongObj = new RongRequest();
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

        $bizData = array_merge($publicBizData,$bizData);//合并$bizData
        $params['biz_data'] = json_encode($bizData);
        $params['sign'] = $this->rsaObj->encode($this->getSignContent($params));
        $resp = $this->_crulPost($params, $this->config['apiUrl']);
        Logger::dayLog('rong','请求数据字段:'.json_encode($params));
        // Logger::dayLog('rong','结果:'.json_encode($resp));
        return ($resp);
    }

    public function getApiName($method){//获取接口名
        $oldName = 'crawler_api_mobile_v4_';
        $arrayName = $oldName.$method.'_response';
        return $arrayName;
    }

    public function loginRule($data) {//请求登陆验证
        $bizData = array(
            'cellphone' => $data['phone'],
            'password' => $data['password'],
            'user_id' => $data['user_id']
        );
        //获取基本信息
        $res = $this->operatorSend($bizData, 'crawler.api.mobile.v4.getLoginRule');

        if (!is_array($res)) {
            Logger::dayLog('rong','Loginrule:请求失败 userid:'.$data['user_id']);
            return $this->returnError(false, '服务器异常 请重试');
        }
        if($res['error'] != 200){
            Logger::dayLog('rong','Loginrule:'.$res.' userid:'.$data['user_id']);
            return $this->returnError(false, $res['error'].':'.$res['msg']);
        }
        //运营商请求登陆
        $res = $this->userLogin($bizData);
        if(empty($res)){
            Logger::dayLog('rong','userLogin:请求失败 userid:'.$data['user_id']);
            return $this->returnError(false,'返回数据异常 请重试');
        }
        $callData = $this->getResParam($res,'login');
        if(!is_array($callData) || empty($callData)){
            Logger::dayLog('rong','Loginrule:calldata请求失败 userid:'.$data['user_id']);
            return $this->returnError(false, '数据异常 请重试');
        }
        $request = new JxlRequestModel();
        $request = $request->getById($data['user_id']);
        if ($callData['flag'] == 1) {//成功拉取
            $request->process_code = 10008;
            $request->save();
            return $request;
        } else {
            $methodObj = RongRequest::find()->where(['requestid' => $data['user_id']])
                ->limit(1)
                ->one();
            $methodObj->method = $callData['method'];
            $methodObj->modify_time = time();
            $methodObj->save();

            $request->process_code = 10002;//发送短信或者图片或者图片+短信验证码
            $request->save();
            return $request;

        }

    }

    public  function userLogin($bizData){//运营商登陆信息 已发送短信
        $method = 'login';
        if(empty($bizData)){
            return $this->returnError(false, '参数异常');
        }
        $postData = [
            'requestid' => $bizData['user_id'],
            'method' => $method,
        ];
        $this->rongObj->saveRongData($postData);//记录字表融
        $res = $this->operatorSend($bizData, 'crawler.api.mobile.v4.'.$method);
        if (!is_array($res)) {
            Logger::dayLog('rong','userLogin:数据返回异常 请重试 userid:'.$bizData['user_id']);
            return $this->returnError(false, '数据返回异常 请重试');
        }
        if($res['error'] != 200){
            Logger::dayLog('rong','userLogin:'. $res['error'].':'.$res['msg'].' userid:'.$bizData['user_id']);
            return $this->returnError(false, $res['msg']);
        }
        return $res;

    }

    public function publicData($data){//第二：获取next接口信息
        $method = $data['method'];
        $request = new JxlRequestModel();
        $request = $request->getById($data['user_id']);
        if ($method == '') {
            return $this->returnError(false, 'method不能为空');
        }
        $data['message_code'] = $data['captcha'];
        $data['cellphone'] = $request->phone;
        $data['password'] = $request->password;
        $res = $this->operatorSend($data, 'crawler.api.mobile.v4.'.$method);
        if (!is_array($res)) {
            Logger::dayLog('rong','Publicdata:请求失败 userid:'.$data['user_id']);
            return $this->returnError(false, '数据返回异常 请重试');
        }
        if($res['error'] != 200){
            Logger::dayLog('rong','Publicdata:'.$res['error'].':'.$res['msg'].' userid:'.$data['user_id']);
            return $this->returnError(false, $res['msg']);
        }
        $callData = $this->getResParam($res,$method);
        if(!is_array($callData)){
            Logger::dayLog('rong','Publicdata:calldata请求失败 userid:'.$data['user_id']);
            return $this->returnError(false, '数据返回异常 请重试');
        }
        if ($callData['flag'] == 1) {//成功拉取
            $request->process_code = 10008;
            $request->save();
            return $request;
        } else {
            $methodObj = RongRequest::find()->where(['requestid' => $data['user_id']])
                ->limit(1)
                ->one();
            $methodObj->method = $callData['method'];
            $methodObj->modify_time = time();
            $methodObj->save();

            $request->process_code = 10002;//发送短信或者图片或者图片+短信验证码
            $request->save();
            return $request;
        }


    }

    public  function getUserdata($uid=0){//抓取用户详情数据
        $bizData = array(
            'user_id' => $uid
        );
        $res = $this->operatorSend($bizData, 'wd.api.mobilephone.getdata');
        if (!is_array($res) || $res['error'] != 200) {
            Logger::dayLog('rongback/notify','getUserdata:服务器异常 请重试 userid:'.$uid);
        }
        //抓取数据成功
        $datalist = $res['wd_api_mobilephone_getdata_response']['data']['data_list'];
        if(empty($datalist)){
            Logger::dayLog('rongback/notify','getUserdata:拉取失败，请重试 userid:'.$uid);
        }
        $opr = $datalist[0]['userdata']['user_source'];
        $opr = strtolower($opr);
        $request = new JxlRequestModel();
        $request = $request->getById($uid);
        $request->website = $opr;//用户运营商
        $request->save();
        $resJson = json_encode($res);
        $jxlJson = $this->changeJxlJson($resJson);
        $this->writeLog($uid.'_rong_detail',$resJson);//记录详情json
        $this->writeLog($uid.'_detail',$jxlJson);//转聚信立json格式
        return true;


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
        }
        if($res['error'] != 200){
            Logger::dayLog('rongback/notify','getReport:'.$res.'userid:'.$userid);
        }
        Logger::dayLog('rongback/notify','getReport:'.$res);
        $outUniqueId = $res['tianji_api_tianjireport_collectuser_response']['outUniqueId'];
        return $outUniqueId;

    }

    public  function rpicCode($phone,$user_id){//刷新图片验证码
        $method = 'refreshPicCode';
        $bizData = array(
            'cellphone' => $phone,
            'piccode_type' => 2,
            'user_id' => $user_id
        );

        $res = $this->operatorSend($bizData, 'crawler.api.mobile.v4.'.$method);

        if (!is_array($res)) {
            return $this->returnError(false, '服务器异常 请重试');
        }
        if($res['error'] != 200){
            return $this->returnError(false, $res['error'].':'.$res['msg']);
        }

        $request = new JxlRequestModel();
        $request = $request->getById($user_id);
        $request->process_code = 10011;//刷新图片验证码
        $request->save();
        $img = $res->crawler_api_mobile_login_response->pic_code;
        $request->img = $img;
        return $request;


    }

    public  function rmsgCode($phone,$user_id){//刷新短信验证码
        $method = 'refreshMessageCode';
        $bizData = array(
            'cellphone' => $phone,
            'messagecode_type' => 1,
            'user_id' => $user_id
        );

        $res = $this->operatorSend($bizData, 'crawler.api.mobile.v4.'.$method);

        if (!is_array($res)) {
            return $this->returnError(false, '服务器异常 请重试');
        }
        if($res['error'] != 200){
            return $this->returnError(false, $res['error'].':'.$res['msg']);
        }
        $request = new JxlRequestModel();
        $request = $request->getById($user_id);
        $request->process_code = 10006;//刷新短信验证码
        $request->save();
        return $request;

    }
    
    // flog=1  登录验证完成直接拉取数据  0 未完成还需要next请求
    public function getResParam($res=array(),$method=''){//获取next参数
        $callDate = array();
        if(!is_array($res) && empty($res) && $method == ''){
            return $this->returnError(false, '数据返回为空');
        }
        $mName = $this->getApiName($method);
        if($mName == ''){
            return $this->returnError(false, 'method返回为空');
        }
        if(array_key_exists('next',$res[$mName])){//有next-method
            $callDate['method'] = $res[$mName]['next']['method'];
            if(!isset($res[$mName]['next'])){
                return $this->returnError(false, '参数返回为空');
            }
            if(array_key_exists('param',$res[$mName]['next'])){//获取param的参数
                foreach($res[$mName]['next']['param'] as $key=>$val){
                    $callDate[$val['key']] = $val['value'];
                }
            }
            if(array_key_exists('hidden',$res[$mName]['next'])){//获取hidden的参数
                foreach($res[$mName]['next']['hidden'] as $key=>$val){
                    $callDate[$val['key']] = $val['value'];
                }
            }
            $callDate['flag'] = 0;

        }else{
            $callDate['flag'] = 1;
        }
        return $callDate;
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

    public function changeJxlJson($json){//转换聚信立json格式
    // $dataObj = $dataObj['raw_data']['members']['transactions'][0]['calls'];//聚信立格式
        $rongDate = json_decode($json,true);
        $phone = $rongDate['wd_api_mobilephone_getdata_response']['data']['data_list'][0]['userdata']['phone'];
        $teldata = $rongDate['wd_api_mobilephone_getdata_response']['data']['data_list'][0]['teldata'];
        if(!$phone || empty($teldata)){
            Logger::dayLog('rongback/notify','changeJxlJson:转换聚信立json失败');
        }
        $newObj = array();
        foreach($teldata as $key=>$m){
            foreach($m['teldata'] as $key=>$vals){
                $init_type = $vals['call_type'] == 1 ? '主叫': '被叫';
                if($vals['trade_type']==1){
                    $trade_type = '本地';
                }elseif($vals['trade_type']==2){
                    $trade_type = '漫游国内';
                }else{
                    $trade_type = '其他';
                }
                $newObj[$key]['update_time'] = '';
                $newObj[$key]['start_time'] = $vals['call_time'];
                $newObj[$key]['init_type'] = $init_type;
                $newObj[$key]['use_time'] = $vals['trade_time'];
                $newObj[$key]['place'] = $vals['trade_addr'];
                $newObj[$key]['other_cell_phone'] = $vals['receive_phone'];
                $newObj[$key]['cell_phone'] = $phone;
                $newObj[$key]['subtotal'] = '';
                $newObj[$key]['call_type'] = $trade_type;
            }

        }
        $resObj['raw_data']['members']['transactions'][0]['calls'] = $newObj;//聚信立格式
        $jxlJson = json_encode($resObj);
        return $jxlJson;
    }

    public function writeLog($filename,$data){//融360详情、报告日志 内容为json数据
        $path = '/ofiles/jxl/' . date('Ym/d/') . $filename . '.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $data);
        return $path;
    }

    /**	 * 返回错误信息
     * @param  false | null $result 错误信息
     * @param  str $errinfo 错误信息
     * @return false | null 同参数$result
     */
    public function returnError($result, $errinfo){
        $this->errorInfo = $errinfo;
        return $result;
    }

    private function _crulPost($postData, $url=''){
        if(empty($url)){
            return false;
        }

        try{
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
        }catch(Exception $e){
            print_r($e->getMessage());
        }

        //Yii::log("openapi curl post url: \t $url \t post: \t " . json_encode($postData) . " \t errno: $errno return: $res " . $errInfo, $logLevel);

        if($arrRet['errno']==0){
            return $arrRet;
        }

        return $arrRet;
    }

    public function returnResdata($data){//登陆验证返回业务端数据
        if(empty($data)){
            return $this->returnError(false, '参数错误');
        }
        $requestRes = $this->loginRule($data);
        if (!$requestRes) {
            return $this->returnError(false, '返回数据异常');
        }
        $rdata = [
            'requestid' => $requestRes->id, // 查询时使用这个就可以了
            'phone' => $requestRes->phone,
            'source' => 3
        ];
        if($requestRes->process_code == '10008'){//成功
            $result = $requestRes->clientNotify();//异步通知
            Logger::dayLog('grab/clientBack', 'quicknotify', $requestRes->id, json_encode($result));
            $rdata['status'] = 1;
            $rdata['url'] = '';
            return ['res_code'=>0, 'res_data'=>$rdata];
        }elseif($requestRes->process_code == '10002'){//输入验证码跳转自定义页面
            $requestid = $this->opEncrypt($requestRes->id);//加密
            $url = Yii::$app->request->hostInfo.'/grab/route?id='.urlencode($requestid);
            $rdata['status'] = 2;//处理中
            $rdata['url'] = $url;
            return ['res_code'=>0, 'res_data'=>$rdata];
        }else{
            return ['res_code'=>25012, 'res_data'=>'返回数据异常'];
        }
    }

    public function returnAjaxData($data){//自定义页面发送验证码返回业务端结果（异步通知）
        if(empty($data)){
            return $this->returnError(false, '参数错误');
        }
        $requestRes = $this->publicData($data);
        if (!$requestRes) {
            return $this->returnError(false, $this->errorInfo);
        }
        if($requestRes->process_code == '10008'){
            $result = $requestRes->clientNotify();//post异步通知
            Logger::dayLog('grab/clientBack', 'quicknotify', $requestRes->id, json_encode($result));
            $callbackurl = $requestRes->clientBackurl();//get回调url
            Logger::dayLog('grab/getclientBack', 'url', $callbackurl);
            if($requestRes->from == 2){//app成功后get请求一下业务端
                file_get_contents($callbackurl);
            }
            $res_data = ['from'=>$requestRes->from,'res'=>'y','callbackurl' => $callbackurl];
            return ['res_code'=>0, 'res_data'=>$res_data];
        }elseif($requestRes->process_code == '10002'){
            $method = $this->getMethod($requestRes->id,$requestRes->source);
            $res_data = ['res'=>'n','method' =>$method,'msg' =>'再次输入验证码'];
            return ['res_code'=>0, 'res_data'=>$res_data];
        }else{
            $result = $requestRes->clientNotify();//post异步通知
            $callbackurl = $requestRes->clientBackurl();//get回调url
            if($requestRes->from == 2){//app成功后请求一下业务端
                file_get_contents($callbackurl);
            }
            $res_data = ['from'=>$requestRes->from,'callbackurl' => $callbackurl];
            return ['res_code'=>25021, 'res_data' => $res_data];
        }
    }

    /*
     * 融获取method
     */
    public function getMethod($requestid,$source){
        if($source == 3){//融
            $rongobj = RongRequest::find()->where(['requestid' => $requestid])
                ->limit(1)
                ->one();
        }
        if(!empty($rongobj)){
            $method = $rongobj->method;
        }else{
            $method = 'login';
        }
        return $method;
    }

    private function opEncrypt($requestid){//加密
        $requestid = Crypt3Des::encrypt($requestid, Yii::$app->params['trideskey']);
        return $requestid;
    }
}

