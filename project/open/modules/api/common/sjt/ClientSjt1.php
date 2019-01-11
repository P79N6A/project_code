<?php
/**
 *  
 */
namespace app\modules\api\common\sjt;

use Yii;
use app\models\SjtRequest;
use app\models\JxlStat;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Func;
use app\common\Crypt3Des;
class ClientSjt
{     
    private $oApi;
	public $errorInfo; 
    /**
     * 初始化接口
     */
    public function __construct(){
        $this->oApi = new SjtApi();
    }
    public function returnResdata($data){//登陆验证返回业务端数据
		if(empty($data)){
			return $this->returnError(false, '参数错误');
		}
		$requestid= ArrayHelper::getValue($data,'user_id');
		if(empty($requestid)){
			return $this->returnError(false,'参数缺失');
		}
		$oModel = new SjtRequest;
		$sjtdata = $oModel->getSjtData($requestid);
		if(empty($sjtdata)){
			$data['requestid'] = $requestid;
			$sjtdata = $oModel->saveData($data);
			if(empty($sjtdata)){
				return $this->returnError(false,$oModel->errinfo);
			}
		}
		//创建请求任务
		$result = $this->oApi->createTask($data);
		if(empty($result)){
			return $this->returnError(false,'创建任务请求超时');
		}
		$code = ArrayHelper::getValue($result,'code','');
		$message = ArrayHelper::getValue($result,'message','');
		$task_id = ArrayHelper::getValue($result,'task_id','');
		$user_mobile = ArrayHelper::getValue($result,'data.user_mobile');
		//更新数据
		$res = $sjtdata->saveCreateResult($code,$message,$task_id);
		if(!$res){
			return $this->returnError(false,$oModel->errors);
		}
		//创建任务失败直接返回
		if($code !=0){
			return $this->returnResult($code,$message);
		}
		//登录验证
		$data['task_id'] = $task_id;
		$result = $this->oApi->loginAuth($data);
		if(empty($result)){
			return $this->returnError(false,'登录验证超时');
		}
		$code = ArrayHelper::getValue($result,'code','');
		$message = ArrayHelper::getValue($result,'message','');
		//更新数据
		$res = $sjtdata->saveResult($code,$message);
		//如果返回code=100 查询登录验证接口 直到返回结果不为100
		if($code==100){
			$result = $this->loginAuth($sjtdata,$data);
			$code = ArrayHelper::getValue($result,'code','');
			$message = ArrayHelper::getValue($result,'message','');
		}
		//如果code=101,105,123 输入手机验证码或是图片验证码 跳转页面
		if(in_array($code,[101,105,123])){
			$res = $this->jumpCodeAuth($code,$sjtdata,$result);
			if(!$res){
				return $this->returnError(false,$oModel->errors);
			}
			//返回结果
			$requestid = $this->opEncrypt($requestid);//加密
            $url = Yii::$app->request->hostInfo.'/grab/sjtroute?id='.urlencode($requestid);
            $rdata['phone'] = $data['phone'];
            $rdata['requestid'] = $data['user_id'];
            $rdata['status'] = 2;//处理中
            $rdata['source'] = $data['source'];
			$rdata['url'] = $url;
			return $this->returnResult(0,$rdata);
		}elseif($code==137||$code==2007){
			//任务已完成 
			$res = $sjtdata->saveToDoing();
			if(!$res){
				Logger::dayLog('sjt/clientsjt','saveToDing','保存失败',$postdata);
			}
			//客户端通知
			$sjtNotify = new SjtNotify($sjtdata);
			$res = $sjtNotify->clientNotify();//post异步通知
			$rdata['phone'] = $data['phone'];
            $rdata['requestid'] = $data['user_id'];
            $rdata['source'] = $data['source'];
			$rdata['status'] = 1;//成功
			$rdata['url'] = '';
			return $this->returnResult(0,$rdata);
		}
		return $this->returnResult($code,$message);
	}
	/**
	 * Undocumented function
	 * 输入验证码 ajax提交
	 * @param [type] $data
	 * @return void
	 */
	public function returnAjaxData($data){//自定义页面发送验证码返回业务端结果（异步通知）
		
		if(empty($data)){
			return $this->returnResult('600001','参数缺失');
		}
		$requestid = ArrayHelper::getValue($data,'user_id');
		$smscode = ArrayHelper::getValue($data,'captcha');//手机验证码
		$imgcode = ArrayHelper::getValue($data,'imgcode');//图片验证码
		if(empty($requestid)){
			return $this->returnResult('600002','参数缺失');
		}
		//查询数据
		$oSjt = (new SjtRequest)->getSjtData($requestid);
		if(empty($oSjt)){
			return $this->returnResult('600003','参数错误');
		}
		if($oSjt->is_smscode==1 && empty($smscode)){
			return $this->returnResult('600004','手机验证码缺失');
		}
		if($oSjt->is_authcode==1 && empty($imgcode)){
			return $this->returnResult('600005','图片验证码缺失');
		}
		$postdata = [
			'task_id'	=> $oSjt->task_id,
			'sms_code'	=> $smscode,
			'auth_code'	=> $imgcode,
			'task_stage'=> $oSjt->task_stage
		];
		$result = $this->oApi->loginCodeAuth($postdata);
		if(empty($result)){
			return $this->returnResult('600006','验证码验证超时');
		}
		$code = ArrayHelper::getValue($result,'code','');
		$message = ArrayHelper::getValue($result,'message','');
		//更新数据
		$res = $oSjt->saveResult($code,$message);
		//如果返回code=100 查询登录验证接口 直到返回结果不为100
		if($code==100){
			$result = $this->loginCodeAuth($oSjt,$postdata);
			$code = ArrayHelper::getValue($result,'code','');
			$message = ArrayHelper::getValue($result,'message','');
		}
		//客户端通知
		$sjtNotify = new SjtNotify($oSjt);
		//成功
		if($code==137||$code==2007){
			//任务已完成 
			$res = $oSjt->saveToDoing();
			if(!$res){
				Logger::dayLog('sjt/clientsjt','saveToDing','保存失败',$postdata);
			}
			$callbackurl = $sjtNotify->clientBackurl();//get回调url
			Logger::dayLog('sjt/clientbackurl', 'url', $callbackurl);
			$res = $sjtNotify->clientNotify();//post异步通知
            
            if($oSjt->from == 2){//app成功后请求一下业务端
                file_get_contents($callbackurl);
            }
			$res_data = ['res'=>'y','callbackurl' => $callbackurl];
			return $this->returnResult(0,$res_data);
		}elseif($code == 122||$code==124){
			//code 122 124 验证码错误或过期 返回页面 
			return $this->returnResult($code,$message.'请尝试刷新');
			//重发验证码
			$result = $this->codeRetry($oSjt);
			if($result){
				$res_data = ['res'=>'n'];
				return $this->returnResult(0,$res_data);
			}
            return $this->returnResult('600007','请尝试从新刷新');
		}elseif(in_array($code,[101,105,123])){
			$this->jumpCodeAuth($code,$oSjt,$result);
			
			return $this->returnResult($code,$message);
		}else{
			$callbackurl = $sjtNotify->clientBackurl();//get回调url
			Logger::dayLog('sjt/clientbackurl', 'url', $callbackurl);
			$res = $sjtNotify->clientNotify();//post异步通知
            if($oSjt->from == 2){//app成功后请求一下业务端
                file_get_contents($callbackurl);
			}
            $res_data = ['msg' => $message,'callbackurl'=>$callbackurl];
            return $this->returnResult($code,$res_data);
		}
	}	
	/**
	 * Undocumented function
	 * 查询任务
	 * @param [type] $oSjt
	 * @return void
	 */
	public function taskQuery($oSjt){
		if(empty($oSjt)) return false;		
		$task_id = $oSjt->task_id;
		$result = $this->oApi->taskQuery($task_id);
		$res  = $this->parseResult($oSjt,$result);
		return $res;
	}
	/**
	 * Undocumented function
	 * 解析接口返回结果
	 * @param [type] $oSjt
	 * @param [type] $result
	 * @return void
	 */
	private function parseResult($oSjt,$result){
		if(empty($result)){
			//查询超时
			Logger::dayLog('sjt/taskQuery','查询任务超时',$oSjt->requestid);
			return false;
		}
		$code = ArrayHelper::getValue($result,'code');
		$message = ArrayHelper::getValue($result,'message','');
		$task_id = ArrayHelper::getValue($result,'task_id','');
		$data = ArrayHelper::getValue($result,'data','');
		$call_info = ArrayHelper::getValue($result,'data.task_data.call_info','');
		if($code==0){
			//成功
			if(empty($data)||empty($call_info)){
				Logger::dayLog('sjt/taskQuery','返回查询数据为空','data',$data,'call_info',$call_info,$oSjt->requestid);
				return false;				
			}
			//生成详单json			
			$res = $this->createDetailJson($oSjt,$call_info);
			if(!$res){
				Logger::dayLog('sjt/taskQuery','createDetailJson','生成详单json失败',$oSjt->requestid);
				return false;
			}
			return true;
		}else{
			Logger::dayLog('sjt/taskQuery','查询任务失败','code',$code,'message',$message,$oSjt->requestid);
			return false;
		}
	}
	/**
	 * Undocumented function
	 * 生成详单json
	 * @param [type] $oSjt
	 * @param [type] $data 通话详单 每月
	 * @return void
	 */
	private function createDetailJson($oSjt,$data){
		if(empty($data)) return false;
		$calls_data = [];
		foreach($data as $key=>$val){
			$call_record = $val['call_record'];
			if(!empty($call_record)){
				foreach($call_record as $k=>$v){
					$temp['update_time'] = date('Y-m-d H:i:s');
					$temp['start_time'] = $v['call_start_time'];
					$temp['init_type'] = $v['call_type_name'];
					$temp['use_time'] = (int)$v['call_time'];
					$temp['place'] = $v['call_address'];
					$temp['other_cell_phone'] = $v['call_other_number'];
					$temp['cell_phone'] = $oSjt->phone;
					$temp['subtotal'] = $v['call_cost'];
					$temp['call_type'] = $v['call_land_type'];
					array_push($calls_data,$temp);
				}
			}
		}
		$resObj['raw_data']['members']['transactions'][0]['calls'] = $calls_data;
		$sjtJson = json_encode($resObj);
		$requestid = $oSjt->requestid;
		$newPath = $this->writeLog($requestid.'_detail',$sjtJson);
		//保存详单状态
		$res = $oSjt->saveDetailSuccess();
		if(!$res){
			Logger::dayLog('sjt/saveDetailSuccess',$newPath,'requestid',$requestid);
			return false;
		}
		
		return true;
	}
	/**
	 * Undocumented function
	 * json数据存储
	 * @param [type] $filename
	 * @param [type] $data
	 * @return void
	 */
	public function writeLog($filename,$data){//详单、报告为json数据 并存储
        $path = '/ofiles/jxl/' . date('Ym/d/') . $filename . '.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $data);
        return $path;
    }
	/**
	 * Undocumented function
	 * 轮询查询
	 * @param [type] $oSjt
	 * @param [type] $data
	 * @return void
	 */
	private function loginAuth($oSjt,$data){
		$result = $this->oApi->loginAuth($data,false);
		$code = ArrayHelper::getValue($result,'code','');
		$message = ArrayHelper::getValue($result,'message','');
		//更新数据
		$res = $oSjt->saveResult($code,$message);
		//如果返回code=100 查询登录验证接口 直到返回结果不为100
		$code = ArrayHelper::getValue($result,'code','');
		if($code==100){
			sleep(1);
			return $this->loginAuth($oSjt,$data);
		}
		return $result;
	}
	/**
	 * Undocumented function
	 * 轮询验证查询
	 * @param [type] $oSjt
	 * @param [type] $data
	 * @return void
	 */
	private function loginCodeAuth($oSjt,$data){
		$result = $this->oApi->loginCodeAuth($data,false);
		$code = ArrayHelper::getValue($result,'code','');
		$message = ArrayHelper::getValue($result,'message','');
		//更新数据
		$res = $oSjt->saveResult($code,$message);
		//如果返回code=100 查询登录验证接口 直到返回结果不为100
		$code = ArrayHelper::getValue($result,'code','');
		if($code==100){
			sleep(1);
			return $this->loginCodeAuth($oSjt,$data);
		}
		return $result;
	}
	/**
	 * Undocumented function
	 * 处理验证码登录验证
	 * @param [type] $code
	 * @param [type] $oSjt
	 * @param [type] $result
	 * @return void
	 */
	private function jumpCodeAuth($code,$oSjt,$result){
		$is_smscode = 0;
		$is_authcode = 0;
		$auth_code = '';
		$auth_code_path = '';
		$next_stage = ArrayHelper::getValue($result,'data.next_stage','');
		switch($code){
			case 101://图片验证码
				$is_authcode = 1;
				$auth_code = ArrayHelper::getValue($result,'data.auth_code','');
				$auth_code_path = $this->createImgurl($auth_code);
				break;
			case 105://手机验证码
				$is_smscode = 1;break;
			case 123://手机验证码和图片验证码
				$is_smscode = 1;
				$is_authcode = 1;
				$auth_code = ArrayHelper::getValue($result,'data.auth_code','');
				$auth_code_path = $this->createImgurl($auth_code);
				break;
		}
		$data = [
			'is_smscode'	=> $is_smscode,
			'is_authcode'	=> $is_authcode,
			'auth_code'		=> $auth_code,
			'auth_code_path'=> $auth_code_path,
			'task_stage'	=> $next_stage
		];
		$res = $oSjt->saveCodeResult($data);
		return $res;
	}
	/**
	 * Undocumented function
	 * 生成图片
	 * @param [type] $captcha
	 * @param string $imageType
	 * @return void
	 */
	private function createImgurl($captcha,$imageType='jpg'){
		if(empty($captcha)) return '';
        $path = '/ofiles/sjt/'.time().'.'.$imageType;
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        $FP = fopen($filePath,"w+");
        fwrite($FP,base64_decode($captcha));
        fclose($FP);
        return $path;
	}
	/**
	 * Undocumented function
	 * 加密
	 * @param [type] $requestid
	 * @return void
	 */
	private function opEncrypt($requestid){//加密
        $requestid = Crypt3Des::encrypt($requestid, Yii::$app->params['trideskey']);
        return $requestid;
    }
	/**
	 * 返回错误信息
	 * @param  false | null $result 错误信息
	 * @param  str $errinfo 错误信息
	 * @return false | null 同参数$result
	 */
	public function returnError($result, $errinfo){
		Logger::dayLog('sjt/clientsjt','returnError',$errinfo);
		$this->errorInfo = $errinfo;
		return $result;
	}
	/**
	 * Undocumented function
	 * 返回采集结果
	 * @param [type] $code
	 * @param [type] $message
	 * @return void
	 */
	public function returnResult($code,$message){
		return [
			'res_code'=>$code,
			'res_data'=>$message
		];
	}
	private function codeRetry($oSjt){
		$result = $this->oApi->codeRetry($oSjt->task_id);
		Logger::dayLog('sjt/retry','刷新验证码',$oSjt->requestid,$result);
		$code 	= ArrayHelper::getValue($result,'code','');
		$message = ArrayHelper::getValue($result,'message','');
		//如果code=101,105,123 输入手机验证码或是图片验证码,发送成功
		if(in_array($code,[101,105,123])){
			$res = $this->jumpCodeAuth($code,$oSjt,$result);
			return $res;
		}
		return $result;
	}
	/**
	 * Undocumented function
	 * 刷新验证码
	 * @param [type] $requestid
	 * @return void
	 */
	public function rsendCode($requestid){
		if(empty($requestid)){
			return $this->returnError(false,'参数缺失');
		}
		$oSjt = (new SjtRequest)->getSjtData($requestid);
		if(empty($oSjt)){
			return $this->returnError(false,'查询不到记录');
		}
		$result = $this->codeRetry($oSjt);
		if(!$result){
			return $this->returnError(false,'请从新尝试刷新');
		}
		return true;
	}
	/**
	 * Undocumented function
	 * 刷新图片验证码
	 * @param [type] $requestid
	 * @return void
	 */
	public function sendImgCode($requestid){
		if(empty($requestid)){
			return $this->returnError(false,'参数缺失');
		}
		$oSjt = (new SjtRequest)->getSjtData($requestid);
		if(empty($oSjt)){
			return $this->returnError(false,'查询不到记录');
		}
		$result = $this->codeRetry($oSjt);
		if(!$result){
			return $this->returnError(false,'请从新尝试刷新');
		}
		return $oSjt->auth_code_path;
	}
}
