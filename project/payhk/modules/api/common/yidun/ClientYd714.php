<?php
/**
 * 蚁盾-上数
 *
 */
namespace app\modules\api\common\yidun;
use Yii;
use app\common\Func;
use app\models\JxlRequestModel;
use app\models\YidunRequest;
use app\models\YidunPull;
use app\models\JxlStat;
use app\common\Logger;
use app\common\Crypt3Des;

class ClientYd714{
    public $config;
    private $log;
    public $errorInfo;

    public function __construct($env){
        $configPath = __DIR__ . "/config/config.{$env}.php";
        if( !file_exists($configPath) ){
            throw new \Exception($configPath."配置文件不存在",6000);
        }
        $this->config = include( $configPath );
        $this->log = new Logger();
    }

    public function returnResdata($data){//登陆验证返回业务端数据
        if(empty($data)){
            return $this->returnError(false, '参数错误');
        }
        $requestRes = $this->submitUserInfo($data);//提交用户信息
        if (empty($requestRes) || empty($requestRes->formList)) {
            $errorInfo = $this->getErrorMessage($requestRes);
            return $this->returnError(false, $errorInfo);
        }
        $requestid = $data['id'];
        $bizno = isset($requestRes->bizNo)?$requestRes->bizNo:'';
        $data['bizno'] = $bizno;
        $state = $this->isSmsImgcode($requestRes->formList);
        $this->updateRequestRow($requestid,$requestRes);
        if($state == 0){//不需要发送验证码
            return $this->subLoginGetdate($data);
        }else{//需要验证码
            $res = $this->sendSmsImg($requestid,$requestRes);//发送验证码
            if(isset($res['res_code']) && $res['res_code']){
                return $res;
            }
            $requestid = $this->opEncrypt($requestid);//加密
            $url = Yii::$app->request->hostInfo.'/grab/ydroute?id='.urlencode($requestid);
            $res_data = ['res'=>'n','callbackurl' => $url];
			return ['res_code'=>0, 'res_data'=>$res_data];
        }
    }

    public function returnAjaxData($data){//自定义页面发送验证码返回业务端结果（异步通知）
        if(empty($data) || !isset($data['user_id'])){
            return ['res_code'=>25059, 'res_data'=>'参数错误'];
        }
        if(!$data['user_id']){
           return ['res_code'=>25060, 'res_data'=>'参数错误'];
        }
        $request = new YidunRequest();
        $request = $request->getOneRequest($data['user_id']);
        if(!$request){
            return ['res_code'=>25061, 'res_data'=>'数据异常'];
        }
        $captcha = isset($data['captcha'])?$data['captcha']:'';
        $imgcode = isset($data['imgcode'])?$data['imgcode']:'';
        $ydInfo['idCardNo'] = $request->idcard;
        $ydInfo['name'] = $request->name;
        $ydInfo['phoneNo'] = $request->phone;
        $ydInfo['servicePassword'] = $request->password;
        $ydInfo['bizNo'] = $request->bizno;
        if($request->is_smscode){
            $ydInfo['smsCode'] = $captcha;
        }
        if($request->is_imgcode){
            $ydInfo['captcha'] = $imgcode;
        }
        if($request->is_smscodejldx){
            $ydInfo['smsCodeJldx'] = $captcha;
        }
        $requestRes = $this->validateLogin($ydInfo);
        if(empty($requestRes) || !isset($requestRes->success) || $requestRes->success=='false'){
            $errorInfo = $this->getErrorMessage($requestRes);
            if($requestRes->errorCode == 42000 || $requestRes->errorCode == 42002){
                $errorInfo = '验证码错误或过期，请重新获取';
            }
            Logger::dayLog('yidun','returnAjaxData:'.$errorInfo);
            if($requestRes->errorCode == 40000 || $requestRes->errorCode == 40003 || $requestRes->errorCode == 60000){
                $request->process_code = $requestRes->errorCode;
                $res = $request->save();
                if (!$res) {
                    Logger::dayLog('yidun','returnAjaxData:更新数据失败');
                }
                 $callbackurl = $request->clientBackurl();//get回调url
                 $errorInfo = ['msg' => '服务密码错误 请重试','callbackurl'=>$callbackurl];
            }
            return ['res_code'=>$requestRes->errorCode, 'res_data'=>$errorInfo];
        }
        if(!isset($requestRes->code)){
            return ['res_code'=>25046, 'res_data'=>'数据异常请重试'];
        }
        $returnObj = $this->updateRequestRow($data['user_id'],$requestRes);//更改流程码、上数流水号
        if($requestRes->code == '10000'){//成功
            $result = $returnObj->clientNotify();//post异步通知
            $callbackurl = $returnObj->clientBackurl();//get回调url
            Logger::dayLog('grab/clientBack', 'url', $callbackurl);
            if($returnObj->from == 2){//app成功后请求一下业务端
                $this->getRequest($callbackurl);
            }
            $res_data = ['res'=>'y','callbackurl' => $callbackurl];
            return ['res_code'=>0, 'res_data'=>$res_data];
        }elseif($requestRes->code == '10001'){
            $res = $this->sendSmsImg($data['user_id'],$requestRes);
            $res_data = ['res'=>'n','msg' => '再次输入验证码'];
            return ['res_code'=>0, 'res_data'=>$res_data];
        }else{//失败
            $result = $returnObj->clientNotify();//post异步通知
            $callbackurl = $returnObj->clientBackurl();//get回调url
            if($returnObj->from == 2){//app成功后请求一下业务端
                $this->getRequest($callbackurl);
            }
            $res_data = ['msg' => '请求异常 请重试','callbackurl'=>$callbackurl];
            return ['res_code'=>40000, 'res_data' => $res_data];
        }
    }

    public function submitUserInfo($postData){//提交用户信息
        if(empty($postData)){
            return $this->returnError(false,'请求参数不合法');
        }
        $url = $this->config['apiUrl'].'rest/authcoll/submituserinfo/v1';
        $data['accessToken'] = $this->getAccessToken();
        $data['orgBizNo'] = $this->getSerialNumber(10);
        $data['sceneId'] = $this->config['secneId'];
        $data['phoneNo'] = $postData['phone'];
        $data['idCardNo'] = $postData['idcard'];
        $data['name'] = $postData['name'];
        $requestRes = $this->postCurl($data,$url);
        if(empty($requestRes) || !isset($requestRes->success) || $requestRes->success == 'false'){
            $errorInfo = $this->getErrorMessage($requestRes);
            Logger::dayLog('yidun','submitUserInfo:', $errorInfo);
            return $this->returnError(false,$errorInfo);
        }
        $postData['bizno'] = $requestRes->bizNo;
        $postData['orgbizno'] = $requestRes->orgBizNo;
        $yidunObj = new YidunRequest();
        if(isset($postData['user_id'])){//存在记录就不插入
            $res = $yidunObj->getOneRequest($postData['user_id']);
        }
        if(!$res){
            $newData = $yidunObj->saveYidunData($postData);//save请求上数表
            if (!$newData) {
                Logger::dayLog('yidun','submitUserInfo:数据保存失败');
		    }
        }
        
        return $requestRes;
    }

    public function subLoginGetdate($data){//不需要验证码直接登录验证
        if(empty($data)){
            return $this->returnError(false,'请求参数不合法');
        }
        $ydInfo['phoneNo'] = $data['phone'];
        $ydInfo['servicePassword'] = $data['password'];
        $ydInfo['bizNo'] = $data['bizno'];
        $resInfo = $this->validateLogin($ydInfo);
        if(!$resInfo || !isset($resInfo->success) || $resInfo->success=='false'){
            $errorInfo = $this->getErrorMessage($resInfo);
            return ['res_code'=>25059, 'res_data'=>$errorInfo];
            Logger::dayLog('yidun','subLoginGetdate:'.$errorInfo);
        }
        if(!isset($resInfo->code)){
            return ['res_code'=>25047, 'res_data'=>'流程码返回错误'];
        }
        $returnObj = $this->updateRequestRow($data['user_id'],$resInfo);//更改流程码、上数流水号
        if(!is_object($returnObj)){
            return ['res_code'=>25101, 'res_data'=>'数据异常'];
        }
        if($resInfo->code == '10000'){//成功
            $result = $returnObj->clientNotify();//post异步通知
            $callbackurl = $returnObj->clientBackurl();//get回调url
            Logger::dayLog('grab/clientBack', 'quicknotify', $requestid, json_encode($result));
            $rdata['phone'] = $data['phone'];
            $rdata['requestid'] = $data['user_id'];
            $rdata['status'] = 4;
            $rdata['source'] = 4;
            $rdata['from'] = $returnObj->from;
            $rdata['url'] = '';
            $rdata['res'] = 'y';
            $rdata['callbackurl'] = $callbackurl;
            return ['res_code'=>0, 'res_data'=>$rdata];
        }else{
            return ['res_code'=>25040, 'res_data'=>'返回数据异常'];
        }

    } 

    public function sendSmsImg($requestid,$requestRes){//获得验证码
        if(empty($requestRes) || !isset($requestRes->success) || $requestRes->success == 'false' || !isset($requestRes->bizNo) || !isset($requestRes->formList)){
            return ['res_code'=>25043, 'res_data'=>'返回参数错误'];
        }
        $bizno = $requestRes->bizNo;
        $ydmodel = new YidunRequest();
        $ydmodel = $ydmodel -> getOneRequest($requestid,$bizno);
        if(!$ydmodel){
            return ['res_code'=>25100, 'res_data'=>'返回数据异常'];
        }
        $ydmodel->process_code = $requestRes->code;

        if(in_array('smsCode',$requestRes->formList) && in_array('captcha',$requestRes->formList)){//短信+图片验证码
            $ydmodel->is_smscode = 1;
            $ydmodel->is_imgcode = 1;
            $returnObj = $this->sendSmsCode($bizno);
            $imgPath = $this->sendImgCode($bizno,$requestid);
            $ydmodel->captcha_path = $imgPath;
        }

        if(in_array('smsCode',$requestRes->formList) && !in_array('captcha',$requestRes->formList)){//短信验证码
            $ydmodel->is_smscode = 1;
            $ydmodel->is_imgcode = 0;
            $returnObj = $this->sendSmsCode($bizno);
        }
        if(in_array('captcha',$requestRes->formList) && !in_array('smsCode',$requestRes->formList)){//图片验证码
            $ydmodel->is_imgcode = 1;
            $ydmodel->is_smscode = 0;
            $imgPath = $this->sendImgCode($bizno,$requestid);
            $ydmodel->captcha_path = $imgPath;
        }
        if(in_array('smsCodeJldx',$requestRes->formList)){//吉林电信短信验证码 手动下发
            $ydmodel->is_smscodejldx = 1;
        }
        $res = $ydmodel->save();
        
        if (!$res) {
            Logger::dayLog('yidun','sendSmsImg:更新数据失败');
        }
        if(isset($returnObj) && $returnObj->success == 'false'){
            $errorInfo = $this->getErrorMessage($requestRes);
            Logger::dayLog('yidun','sendSmsImg:'.$errorInfo);
            return ['res_code'=>25058, 'res_data'=>$errorInfo];
        }
        return ['res_code'=>0, 'res_data'=>$ydmodel];

    }

    public function validateLogin($formList){//验证登陆
        if (empty($formList) || !isset($formList['bizNo'])) {
            return ['res_code'=>25048, 'res_data'=>'登陆信息有误'];
        }
        $formJson = json_encode($formList);
        $url = $this->config['apiUrl'].'rest/authcoll/submitauth/v1';
        $data['accessToken'] = $this->getAccessToken();
        $data['bizNo'] = $formList['bizNo'];
        $data['formMap'] = $formJson;
        $resInfo = $this->postCurl($data,$url);
        return $resInfo;
    }

    public function sendSmsCode($bizno){//发送短信验证码
        if (!$bizno) {
            return ['res_code'=>25049, 'res_data'=>'参数有误'];
        }
        $url = $this->config['apiUrl'].'rest/authcoll/sendsms/v1';
        $data['accessToken'] = $this->getAccessToken();
        $data['bizNo'] = $bizno;
        $resInfo = $this->postCurl($data,$url);
        return $resInfo;
    }

    public function sendImgCode($bizno,$requestid){//发送图片验证码
        if (!$bizno || !$requestid) {
            return ['res_code'=>25050, 'res_data'=>'参数有误'];
        }
        $accessToken = $this->getAccessToken();
        $url = $this->config['apiUrl'].'rest/authcoll/getcaptcha/v1?accessToken='.$accessToken.'&bizNo='.$bizno;
        $resInfo = $this->getRequest($url);
        $resInfo = json_decode($resInfo);
        $imgPath = '';
        if(!empty($resInfo) && isset($resInfo->success) && $resInfo->success=='true'){
            $imgPath = $this->createImgurl($resInfo->captcha->imageContent,$resInfo->captcha->imageType);
        }
        return $imgPath;
    }

    public function createImgurl($captcha,$imageType='jpg'){
        if(!$captcha){
            return ['res_code'=>25051, 'res_data'=>'参数有误'];
        }
        $path = '/ofiles/yidun/'.time().'.'.$imageType;
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        $FP = fopen($filePath,"w+");
        fwrite($FP,base64_decode($captcha));
        fclose($FP);
        return $path;
    }

    public function getAccessToken(){
        $url = $this->config['apiUrl'].'accessToken?accessId='.$this->config['accessId'].'&accessKey='.$this->config['accessKey'];
        $returnRes = $this->getRequest($url);
        $returnRes = json_decode($returnRes);
        if(!$returnRes || !isset($returnRes->accessToken)){
            return ['res_code'=>25052, 'res_data'=>'accessToken获取失败'];
        }
        return $returnRes->accessToken;
    }
    /**	是否需要验证码跳自定义页面
     * param  formlist
     * return 0:不需要 1:需要 
     */
    public function isSmsImgcode($formList){
        if(empty($formList)){
            return ['res_code'=>25053, 'res_data'=>'提交用户信息有误'];
        }
        $state = 0;
        if(in_array('smsCode',$formList)){
            $state = 1;
        }
        if(in_array('captcha',$formList)){
            $state = 1;
        }
        if(in_array('smsCodeJldx',$formList)){
            $state = 1;
        }
        return $state;
    }

    public function updateCaptchaStatus($requestid,$ydObj){//更新验证码状态

        if(!$requestid || empty($ydObj) || !isset($ydObj->bizNo)){
            return ['res_code'=>25054, 'res_data'=>'数据异常'];
        }
        
        $ydmodel = new YidunRequest();
        $ydmodel = $ydmodel -> getOneRequest($requestid,$ydObj->bizNo);
        $ydmodel->process_code = $ydObj->code;
        switch ($state) {
			case '1':
				$ydmodel->is_smscode = 1;
				break;
			case '2':
				$ydmodel->is_imgcode = 1;
				break;
			case '3':
                $ydmodel->is_smscode = 1;
				$ydmodel->is_imgcode = 1;
				break;
            case '4':
                $ydmodel->is_smscodejldx = 1;
				break;
            default:
			    $ydmodel->is_smscode = 0;
				break;
		}
        $res = $ydmodel->save();
        if (!$res) {
            Logger::dayLog('yidun','updateCaptchaStatus:更新数据失败');
        }
        return $ydmodel;

    }

    public function updateRequestRow($requestid,$resInfo){
        if(!$requestid || empty($resInfo) || !isset($resInfo->bizNo) || !$resInfo->bizNo){
            return ['res_code'=>25055, 'res_data'=>'数据异常'];
        }
        $bizno = isset($resInfo->bizNo)?$resInfo->bizNo:'';
        $ydmodel = new YidunRequest();
        $ydmodel = $ydmodel -> getOneRequest($requestid,$resInfo->bizNo);
        if(!$ydmodel){
             return ['res_code'=>25091, 'res_data'=>'数据异常'];
        }
        $ydmodel->bizno = $bizno;
        $ydmodel->orgbizno = $resInfo->orgBizNo;
        $ydmodel->process_code = $resInfo->code;
        
        $res = $ydmodel->save();
        if (!$res) {
            Logger::dayLog('yidun','updateRequestRow:更新数据失败');
        }
        return $ydmodel;
    }
    public function getNextRequestPrame($requestRes){//获取下一步请求参数列表
        if(empty($requestRes) || !isset($requestRes->formList)){
            return ['res_code'=>25056, 'res_data'=>'提交用户信息有误'];
        }
        return $requestRes->formList;
    }

    public function callbackGetCollectionInfo($bizno){
        if (!$bizno) {
            return ['res_code'=>25057, 'res_data'=>'参数有误'];
        }
        $accessToken = $this->getAccessToken();
        $url = $this->config['apiUrl'].'/rest/authcoll/authdataquery/v1?accessToken='.$accessToken.'&bizNo='.$bizno.'&queryType=dataReport';
        $resInfo = $this->getRequest($url);
        // $resInfo = json_decode($resInfo);
        return $resInfo;
    }

    public function getErrorMessage($requestRes){//获得上数错误信息
        if(empty($requestRes)){
            return ['res_code'=>25058, 'res_data'=>'数据异常'];
        }
        $errinfo = isset($requestRes->errorMessage)?$requestRes->errorMessage:'失败';
        return $errinfo;
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
    //生成商户业务流水号
    public function getSerialNumber($len=10){
        $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $string=time();
        for(;$len>=1;$len--){
            $position=rand()%strlen($chars);
           $position2=rand()%strlen($string);
            $string=substr_replace($string,substr($chars,$position,1),$position2,0);
        }
        $string = time().$string;
        return $string;
    }
    //GET request DATA

    public  function getRequest($url) {//get https的内容    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //不输出内容
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /*public function getRequest($url){
        $options = array (
            'https' => array (
                'header'=> "Accept-Encoding: gzip, deflate\r\n"
            ),
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            )
        );
        $context = stream_context_create($options);
        $res = file_get_contents($url,0,$context);
        return $res;
    }*/
    //POST-x-www-form-urlencoded 方式
    public function postCurl($postData,$url){
        Logger::dayLog('yidun','请求数据字段:'.json_encode($postData));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT,'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $result = curl_exec($curl);
        Logger::dayLog('yidun','结果:'.$result,'url',$url);
        curl_close($curl);
        return json_decode($result);
    }

    private function opEncrypt($requestid){//加密
        $requestid = Crypt3Des::encrypt($requestid, Yii::$app->params['trideskey']);
        return $requestid;
    }

    public function writeLog($filename,$data){//详单、报告为json数据 并存储
        $path = '/ofiles/jxl/' . date('Ym/d/') . $filename . '.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Logger::dayLog('yidun', '定时建目录'.$filePath.'---'.$path);
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $data);
        return $path;
    }
//定时批次请求上数查询接口获取详单、报告
    public function timerPulldata($subData,$limit=10){
        $ydPullObj = new YidunPull();
        $dataList = $ydPullObj->getPullList($subData,$limit);//10条批次
        if(!$dataList){
            Logger::dayLog('yidun', 'timerPulldata-没有要查询的数据');
            return false;
        }
        $num = 0;
        foreach($dataList as $key=>$vals){
            if(!isset($vals['bizno']) || !$vals['bizno']){
                continue;
            }
            $queryres = $this->queryYidun($vals);//查询拉取

            $request = new YidunRequest();
            $request = $request->getOneUserInfo($vals['bizno']);
            if(!$request){
                continue;
            }
            if(!$queryres){//采集失败
                $request->result_status = 2;//采集失败
                $request->save();
            }
            $request->clientNotify();//post异步通知成功、失败

            if($queryres){
                $num += 1;
            }
        }
        Logger::dayLog('yidun', 'timerPull-执行批次查询任务成功'.$num);
        return true;
    }
    //请求上数 拉取 并保存
    public function queryYidun($pullData){
        if (!$pullData || !isset($pullData['bizno']) || !$pullData['bizno']) {
            Logger::dayLog('yidun', 'queryYidun-bizno 不存在');
            return false;
        }
        $bizNo = $pullData['bizno'];
        $callbackInfo = $this->callbackGetCollectionInfo($bizNo);
        $callObj  = json_decode($callbackInfo,true);
        if(empty($callObj) || (isset($callObj['success']) && $callObj['success']=='false')){
            $pullData->pull_status = 2;//拉取失败
            $pull = $pullData->save();
            Logger::dayLog('yidun', 'queryYidun-查询数据不存在 bizno:'.$bizNo);
            return false;
        }
        $request = new YidunRequest();
        $request = $request->getOneUserInfo($bizNo);
        if(!$request){
            $pullData->pull_status = 2;//拉取失败
            $pull = $pullData->save();
            Logger::dayLog('yidun', 'getOneUserInfo-无数据 bizno:'.$bizNo);
            return false;
        }
        $oJxlStat = new JxlStat();
        $oHistory = $oJxlStat->getHistoryNew($request->phone,$request->aid);
        if ($oHistory) {
            $pullData->pull_status = 3;//数据已有
            $pull = $pullData->save();
            Logger::dayLog('yidun', 'jxl_stat-数据已有 bizno:'.$bizNo);
            return false;
        }
        // $jsonPath = $this->writeLog($request->requestid.'_all', $callbackInfo);//存储报告json
        // $changeJsonPath = $this->changeJsonDataFormat($jsonPath,$request->requestid);//转换成聚信立格式的json
        $jsonPath = $this->writeLog($request->requestid, $callbackInfo);//存储报告json
        $changeJsonPath = $this->changeJsonDataFormat($callbackInfo,$request->requestid,$pullData);//转换成聚信立格式的json
        if(!$changeJsonPath){
            Logger::dayLog('yidun', 'changeJsonDataFormat-异常 bizno:'.$bizNo);
            return false;
        }
        $changeJsonPath = str_replace("_detail.json", ".json", $changeJsonPath);
        $request = $request->getOneRequest($request->requestid);//为了得到手机运营商
        
        $statData = $oJxlStat->getByRequestid($request->requestid);//是否已存在
        if(!$statData){
            $postData = [
                'aid' => $request->aid,
                'requestid' => $request->requestid,
                'name' => $request->name,
                'idcard' => $request->idcard,
                'phone' => $request->phone,
                'website' => $request->website,
                'is_valid' => 3,
                'url' => $changeJsonPath,
                'source' => $request->source
            ];
            //5 保存到DB中
            $result = $oJxlStat->saveStat($postData);
            
        }else{
            $statData->url = $changeJsonPath;
            $result = $statData->save();
        }
        if (!$result) {
            Logger::dayLog('yidun', 'saveStat保存失败'.json_encode($postData));
        }
        $request->result_status = 1;
        $res = $request->save();
        if (!$res) {
            Logger::dayLog('yidun', 'save-result_status保存失败');
        }
        $pullData->pull_status = 1;//拉取成功修改状态
        $pull = $pullData->save();
        if (!$pull) {
            Logger::dayLog('yidun', 'save-yidun_pull_back保存失败');
        }
        return true;
    }
//蚁盾数据转换成聚信立格式--详单
//-/ofiles/jxl/201706/12/538_all.json
    // public function changeJsonDataFormat($jsonPath,$requestid){
    public function changeJsonDataFormat($jsonString ,$requestid,$pullData){  
        if(!$jsonString || !$requestid){
            Logger::dayLog('yidun', 'changeJsonDataFormat-参数不合法 requestid:'.$requestid);
            return false;
        }
        $dataObj = json_decode($jsonString,true);
        $importantData = json_decode($dataObj['bizContent'],true);
        $operator = isset($importantData['itemSubKey'])?$importantData['itemSubKey']:'';
        $operator = strtolower($operator);
        $importantData = json_decode($importantData['bizContent'],true);
        if(empty($importantData) || !isset($importantData['operatorVoices']) || empty($importantData['operatorVoices'])){
            $pullData->pull_status = 1;//拉取成功修改状态
            $pull = $pullData->save();
            Logger::dayLog('yidun','转换json:importantData为空 requesid:'.$requestid);
        }
        // $dataObj = $dataObj['raw_data']['members']['transactions'][0]['calls'];//聚信立格式
        $request = new YidunRequest();
        $request = $request->getOneRequest($requestid);

        $request->website = $operator;
        $res = $request->save();
        if (!$res) {
            Logger::dayLog('yidun', 'website保存失败 requesid'.$requestid.$operator);
        }
        $dataObj = $importantData['operatorVoices'];
        if(!isset($dataObj) || !$dataObj){
            Logger::dayLog('yidun','转换json:详单列表为空 requesid:'.$requestid);
        }
        $newObj = [];
        foreach($dataObj as $key=>$vals){
            $temp = [];
            $temp['update_time'] = $vals['gmtCreate'];
            $temp['start_time'] = $vals['voiceDate'];
            $temp['init_type'] = $vals['voiceType'];
            $temp['use_time'] = (int)$vals['voiceDuration'];
            $temp['place'] = $vals['voicePlace'];
            $temp['other_cell_phone'] = $vals['voiceToNumber'];
            $temp['cell_phone'] = $vals['phoneNum'];
            $temp['subtotal'] = 0;
            $temp['call_type'] = $vals['voiceStatus'];
            $newObj[$key] = $temp;
        }
        $resObj = [];
        $resObj['raw_data']['members']['transactions'][0]['calls'] = $newObj;//聚信立格式
        $jxlJson = json_encode($resObj);
        $newPath = $this->writeLog($requestid.'_detail',$jxlJson);
        return $newPath;

    }

}

