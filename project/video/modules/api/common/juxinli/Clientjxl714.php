<?php
/**
 *  
 */
namespace app\modules\api\common\juxinli;

use Yii;
use app\models\JxlRequestModel;
use app\models\JxlStat;
use yii\helpers\ArrayHelper;
use app\common\Crypt3Des;
use app\common\Logger;

class Clientjxl714
{     
    private $jxlRequest;

	public $errorInfo; 
    /**
     * 初始化接口
     */
    public function __construct(){
        $this->jxlRequest = new JxlRequest(2);// 默认使用2,即包月的
    }
    /******************采集流程*******************/
	public function actionDatasources() {
		$content = $this->jxlRequest->datasources();
		$this->resp(0, $content);
	}
    /**
	 * 发送请求并提交采集动作，相当于 actionRequest  和 actionPostreq
	 * 使用这个就可以了
	 */
	public function postRequest($data) {
		$jxlModel = new JxlRequestModel();
        $jxlRequestModel = $jxlModel->getById($data['user_id']);
		//5 发送请求部分
		if($data['website']== 'jingdong'){
			$skip_mobile =  true;
		}else{
			$skip_mobile =  false;
		}
		$requestData = [
			'name' => $data['name'],
			'id_card_num' => $data['idcard'],
			'cell_phone_num' => $data['phone'],
			'uid' => $data['user_id'],
			'contacts' => isset($data['contacts']) ? $data['contacts'] : null,
			// 新增加两个参数
			'website' => $data['website'],
			'skip_mobile' => $skip_mobile,
		];

		$oJxlRequest = new JxlRequest($jxlRequestModel->source);
		$result = $oJxlRequest->request($requestData);
		$isOk = is_array($result) && isset($result['success']) && $result['success'] == true;
		if (!$isOk) {
			return $this->returnError(false, '发送请求失败');
		}
		//6  获取并保存 token
		$token = $result['data']['token'];
		if (empty($token)) {
			return $this->returnError(false, '没有获取到请求token');
		}
		$jxlRequestModel->token = $token;

		//7 获取运营商或电商等数据
		$website = ArrayHelper::getValue($result, 'data.datasource.website');
		if ($website) {
			$jxlRequestModel->website = $website;
		}
		$res = $jxlRequestModel->save();

		//5 提交到采集接口
		$resobj = $this->postByDb($jxlRequestModel);
		$this->jxlErrorInfo($resobj->process_code);//聚信立错误信息
		return $resobj;
	}
	/**
	 * 重新发送验证信息
	 */
	public function postRetry($data) {
		$requestid = $data['user_id'];
		$m = new JxlRequestModel();
		$jxlRequestModel = $m->getById($requestid);
		//2 重新更新字段
		$jxlRequestModel->process_code = $data['process_code'];
		if($data['process_code'] == '10022'){
			$jxlRequestModel->query_pwd = $data['captcha'];
			$jxlRequestModel->type = 'SUBMIT_QUERY_PWD';
		}else{
			$jxlRequestModel->captcha = $data['captcha'];
			$jxlRequestModel->type = 'SUBMIT_CAPTCHA';
		}
		$res = $jxlRequestModel->save();
		// 是否保存成功
		if (!$res) {
			$this->dayLog(
				'juxinli',
				'actionPostretry',
				'提交数据', $data,
				'失败原因', $jxlRequestModel->errors
			);
		}
		// 重新提交请求
		$resobj = $this->postByDb($jxlRequestModel);
		$this->jxlErrorInfo($resobj->process_code);//聚信立错误信息
		return $resobj;
	}
	/**
	 * 提交采集接口
	 */
	public function postByDb($jxlRequestModel) {
		//4 组合提交采集接口数据
		$postData = [
			'token' => $jxlRequestModel->token,
			'account' => $jxlRequestModel->account,
			'password' => $jxlRequestModel->password,
			'type' => $jxlRequestModel->type,
			'website' => $jxlRequestModel->website,
		];
		if($jxlRequestModel->process_code == '10022') {
			$postData['query_pwd'] = $jxlRequestModel->query_pwd;
		}else{
			$postData['captcha'] = $jxlRequestModel->captcha;
		}
		$oJxlRequest = new JxlRequest($jxlRequestModel->source);
		$result = $oJxlRequest->postreq($postData);
		$isOk = is_array($result) && isset($result['success']) && $result['success'] == true;
		if (!$isOk) {
			if (is_array($result) && !$result['success'] && $result['message']) {

				return $this->returnError(null, $result['message']);
			}
			return $this->returnError(null, '采集失败');
		}
		$data = (array) $result['data'];
		$jxlRequestModel->response_type = $data['type'];
		$jxlRequestModel->process_code = $data['process_code'];

		$res = $jxlRequestModel->save();
        return $jxlRequestModel;
	}


	/**
	 * 返回错误信息
	 * @param  false | null $result 错误信息
	 * @param  str $errinfo 错误信息
	 * @return false | null 同参数$result
	 */
	public function returnError($result, $errinfo){
		$this->errorInfo = $errinfo;
		return $result;
	}

	//聚信立流程码集合
	public function processCode(){
		return [
				'10001'=>'请再次输入短信验证码',
				'10004'=>'短信验证码错误',
				'10006'=>'短信验证码失效已重新下发',
				'10017'=>'请用本机发送CXXD至10001获取查询详单的验证码',
				'10018'=>'短信码失效请用本机发送CXXD至10001获取查询详单的验证码',
				'30000'=>'网络异常、运营商异常或当天下发短信验证码超限',
				'10023'=>'查询密码错误',
		];
	}
	private  function jxlErrorInfo($process_code){//聚信立错误信息
		$codearr = $this->processCode();
		if(array_key_exists($process_code, $codearr)){
			return $this->returnError(false, $codearr[$process_code]);
		}
	}


	public function returnResdata($data){//登陆验证返回业务端数据
		if(empty($data)){
			return $this->returnError(false, '参数错误');
		}
		$requestRes = $this->postRequest($data);//请求数据
		if (!$requestRes) {
			return $this->returnError(false, $this->errorInfo);
		}

		if($requestRes->process_code == '10008'){//成功
			$result = $requestRes->clientNotify();//异步通知
			$callbackurl = $requestRes->clientBackurl();//get回调url
			Logger::dayLog('grab/getclientBack', 'url', $callbackurl);
			if($requestRes->from == 2){//app成功后请求一下业务端
                file_get_contents($callbackurl);
            }
			$res_data = ['res'=>'y','callbackurl' => $callbackurl];
			return ['res_code'=>0, 'res_data'=>$res_data];
		}elseif($this->isJumpRoute($requestRes->process_code)){//输入验证码跳转自定义页面
			$requestid = $this->opEncrypt($requestRes->id);//加密
			$url = Yii::$app->request->hostInfo.'/grab/route?id='.urlencode($requestid);
			$res_data = ['res'=>'n','callbackurl' => $url];
			return ['res_code'=>0, 'res_data'=>$res_data];
		}else{
			return ['res_code'=>25012, 'res_data'=>'返回数据异常 请重试'];
		}
	}

	public function returnAjaxData($data){//自定义页面发送验证码返回业务端结果（异步通知）
		if(empty($data)){
			return ['res_code'=>25088, 'res_data'=>'参数错误'];
		}
		$requestRes = $this->postRetry($data);
		$jxlModel = new JxlRequestModel();
        $jxlRequestModel = $jxlModel->getById($data['user_id']);
		$callbackurl = $jxlRequestModel->clientBackurl();//get回调url
		if (!$requestRes) {
			$res_data = ['msg'=>'数据异常请重试','callbackurl' => $callbackurl];
			return ['res_code'=>25089, 'res_data' => $res_data];
		}
		if($requestRes->process_code == '10008'){
			$result = $requestRes->clientNotify();//post异步通知
			$callbackurl = $requestRes->clientBackurl();//get回调url
			Logger::dayLog('grab/getclientBack', 'url', $callbackurl);
			if($requestRes->from == 2){//app成功后请求一下业务端
                file_get_contents($callbackurl);
            }
			$res_data = ['res'=>'y','callbackurl' => $callbackurl];
			return ['res_code'=>0, 'res_data'=>$res_data];
		}elseif($requestRes->process_code == '10002'){
			$res_data = ['res'=>'n','type' =>'1','msg' =>'再次输入验证码'];
			return ['res_code'=>0, 'res_data'=>$res_data];
		}elseif($requestRes->process_code == '10022'){
			$res_data = ['res'=>'n','type' =>'2','msg' =>'请输入查询密码'];
			return ['res_code'=>0, 'res_data'=>$res_data];
		}else{
            $callbackurl = $requestRes->clientBackurl();//get回调url
			if($requestRes->from == 2){//app成功后请求一下业务端
                file_get_contents($callbackurl);
            }
            $res_data = ['msg'=>$this->errorInfo,'callbackurl' => $callbackurl];
			$process_code = isset($requestRes->process_code)?$requestRes->process_code:'10023';
            return ['res_code'=>$process_code, 'res_data' => $res_data];
		}
	}

	public function opEncrypt($requestid){//加密
		$requestid = Crypt3Des::encrypt($requestid, Yii::$app->params['trideskey']);
		return $requestid;
	}

	private  function isJumpRoute($process_code){
		$jumpArr = ['10002','10022'];
		if(in_array($process_code,$jumpArr)){
			return true;
		}else{
			return false;
		}
	}
}
