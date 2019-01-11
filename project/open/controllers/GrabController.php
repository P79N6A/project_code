<?php
/**
 * 运营商自定义路由
 */

namespace app\controllers;

use app\common\Logger;
use app\models\RongRequest;
use app\modules\api\common\ApiController;
use app\models\JxlRequestModel;
use app\models\YidunRequest;
use app\models\SjtRequest;
use app\models\JxlStat;
use app\modules\api\common\juxinli\Clientjxl;
use app\modules\api\common\yidun\ClientYd;
use app\modules\api\common\sjt\ClientSjt;
use app\common\Crypt3Des;
use app\modules\api\common\rong\RongApi;
use Yii;

class GrabController extends ApiController {
	private $env;

	public function init() {
		//parent::init(); 千万不要执行父类的验证方法
		$this->env = YII_ENV_DEV ? 'dev' : 'prod';
	}

	public function actionIndex() {

	}
	/**
	 * 自定义页面
	 * 聚信立、融360
	 */
	public function actionRegister(){
		$getData = $this->get();
		if(empty($getData)){
			$this->resp('25022', '无效的请求');
		}
		if(!isset($getData['id']) || !$getData['id']){
			$this->resp('25023', '请求不合法');
		}
		$requestid = $this->opDecrypt($getData['id']);
		$request = new JxlRequestModel();
		$request = $request->getById($requestid);
		if(empty($request)){
			$this->resp('25024', '无效的请求');
		}
		//埋点
		\app\common\PLogger::getInstance('webs');
		
		$this->layout = false;
			return $this->render('/grabroute/register', [
			'phone' => $request->phone,
			'userId' => $getData['id'],
			'from' => $request->from,
			'css' => $this->getCssfile($request->aid),
			'aid' => $request->aid,
			'commiturl'=>"/api/opsub/servicepwd"
		]);

	}


	public function actionRoute(){
		$getData = $this->get();
		if(empty($getData)){
			$this->resp('25022', '无效的请求');
		}
		if(!isset($getData['id']) || !$getData['id']){
			$this->resp('25023', '请求不合法');
		}
		$requestid = $this->opDecrypt($getData['id']);
		$request = new JxlRequestModel();
		$request = $request->getById($requestid);
		$rongobj = new RongApi($this->env );
		$method = $rongobj->getMethod($requestid,$request->source);
		if(empty($request)){
			$this->resp('25024', '无效的请求');
		}

		$this->layout = false;
			return $this->render('/grabroute/route', [
			'phone' => $request->phone,
			'user_id' => $getData['id'],
			'source' => $request->source,
			'process_code'=> $request->process_code,
			'from' => $request->from,
			'css' => $this->getCssfile($request->aid),
			'method' =>$method,
			'aid' => $request->aid,
			'refreshurl' => "/grab/refreshcode",
			'commiturl'=>"/grab/commitcode"
		]);

	}
	/**
	 * 自定义页面
	 * 蚁盾-上数
	 */
	public function actionYdroute(){
		$getData = $this->get();
		if(empty($getData)){
			$this->resp('25022', '无效的请求');
		}
		if(!isset($getData['id']) || !$getData['id']){
			$this->resp('25023', '请求不合法');
		}
		$requestid = $this->opDecrypt($getData['id']);
		$request = new YidunRequest();
		$request = $request->getOneRequest($requestid);
		if(empty($request)){
			$this->resp('25024', '无效的请求');
		}

		$this->layout = false;
		return $this->render('/grabroute/ydroute', [
			'phone' => $request->phone,
			'user_id' => $getData['id'],
			'bizno' => $request->bizno,
			'source' => $request->source,
			'from' => $request->from,
			'css' => $this->getCssfile($request->aid),
			'process_code'=>$request->process_code,
			'is_imgcode'=>$request->is_imgcode,
			'is_smscode'=>$request->is_smscode,
			'is_smscodejldx'=>$request->is_smscodejldx,
			'captcha_path'=>$request->captcha_path,
			'aid' => $request->aid,
			'refreshurl' => "/grab/refreshcode",
			'rhimgurl' => "/grab/rhimgcode",
			'commiturl'=>"/grab/commitcode"
		]);

	}
	/**
	 * 自定义页面
	 * 数聚魔盒
	 */
	public function actionSjtroute(){
		$getData = $this->get();
		if(empty($getData)){
			$this->resp('25022', '无效的请求');
		}
		if(!isset($getData['id']) || !$getData['id']){
			$this->resp('25023', '请求不合法');
		}
		$requestid = $this->opDecrypt($getData['id']);
		$request = new SjtRequest();
		$request = $request->getSjtData($requestid);
		if(empty($request)){
			$this->resp('25024', '无效的请求');
		}

		$this->layout = false;
		return $this->render('/grabroute/sjtroute', [
			'phone' => $request->phone,
			'user_id' => $getData['id'],
			'source' => $request->source,
			'from' => $request->from,
			'css' => $this->getCssfile($request->aid),
			'is_imgcode'=>$request->is_authcode,
			'is_smscode'=>$request->is_smscode,
			'captcha_path'=>$request->auth_code_path,
			'aid' => $request->aid,
			'refreshurl' => "/grab/refreshcode",
			'rhimgurl' => "/grab/rhimgcode",
			'commiturl'=>"/grab/commitcode"
		]);

	}

	/**
	 * 刷新验证码
	 */
	public function actionRefreshcode(){
		$postdata = $this->post();
		if(empty($postdata)){
			$this->resp('25013', '无效的请求');
		}
		if(!$postdata['source']){
			$this->resp('25014', '无效的通道');
		}
		$cryid = $postdata['requestid'];
		$requestid = $this->opDecrypt($cryid);
		switch ($postdata['source']) {
			case '1':
			case '2':
				break;
			case '3':
				$crawler = new RongApi($this->env);
				$requestRes = $crawler->rmsgCode($postdata['phone'],$requestid);
				break;
			case '4':
				$crawler = new ClientYd($this->env);
				$requestRes = $crawler->sendSmsCode($postdata['bizno']);
				if(empty($requestRes) || !isset($requestRes->success) || $requestRes->success==false){
					$errorMsg = $crawler->getErrorMessage($requestRes);
					Logger::dayLog('yidun','returnResdata:'.$errorMsg);
					$this->resp('25042', $errorMsg);
				}
				break;
			case '6':
				$crawler = new ClientSjt;
				$requestRes = $crawler->rsendCode($requestid);
				break;
			default:
				$requestRes = null;
				break;
		}
		if(!$requestRes){//发送成功
			$this->resp('25011', $crawler->errorInfo);
		}
		$this->resp(0, '发送成功');
	}

	/**
	 * 刷新图片验证码
	 */
	public function actionRhimgcode(){
		$postdata = $this->post();
		if(empty($postdata)){
			$this->resp('25013', '无效的请求');
		}
		if(!$postdata['source']){
			$this->resp('25014', '无效的通道');
		}
		$cryid = $postdata['requestid'];
		$requestid = $this->opDecrypt($cryid);
		switch ($postdata['source']) {
			case '1':
			case '2':
				break;
			case '3':
				break;
			case '4':
				$crawler = new ClientYd($this->env);
				$imgurl = $crawler->sendImgCode($postdata['bizno'],$requestid);
				break;
			case '6':
				$crawler = new ClientSjt;
				$imgurl = $crawler->sendImgCode($requestid);
				break;
			default:
				$imgurl = '';
				break;
		}
		$this->resp(0, ['imgurl'=>$imgurl]);
	}
	/**
	 * 再次提交
	 *
	 */
	public function actionCommitcode(){
		$postdata = $this->post();
		if(empty($postdata)){
			$this->resp('25015', '无效的请求');
		}
		$cryid = $postdata['requestid'];
		$requestid = $this->opDecrypt($cryid);
		//解析数据
		if (!isset($requestid) || !$requestid) {
			$this->resp('25016', '请求不合法');
		}
		if(!$postdata['source']){
			$this->resp('25017', '无效的通道');
		}
		if(isset($postdata['captcha']) && !$postdata['captcha']){
			$this->resp('25018', '验证码不能为空');
		}
		if(isset($postdata['imgcode']) && !$postdata['imgcode']){
			$this->resp('25045', '验证码不能为空');
		}
		$method = isset($postdata['method']) ? $postdata['method'] : "";
		$bizno = isset($postdata['bizno']) ? $postdata['bizno'] :'';
		$captcha = isset($postdata['captcha']) ? $postdata['captcha'] :'';
		$imgcode = isset($postdata['imgcode']) ? $postdata['imgcode'] :'';
		$processCode = isset($postdata['process_code']) ? $postdata['process_code'] :'';
		$data = [
				'user_id'   => $requestid,
				'captcha'   => $captcha,
				'imgcode' => $imgcode,
				'process_code'=> $processCode,
				'method'    => $method,
				'bizno' => $bizno,
				'source' => $postdata['source']
		];
		$resData = $this->requestSendCode($data);//通道发送验证码   并返回结果
		if(empty($resData)){
			$this->resp(25027, '数据异常');
		}
		$this->resp($resData['res_code'], $resData['res_data']);

	}


	/**
	 * 运营商各个通道请求发送验证码
	 * 融、聚信立
	 * @return requestRes
	 */
	private function requestSendCode($data){
		switch ($data['source']) {
			case '1':
			case '2':
				$crawler = new Clientjxl();
				break;
			case '3':
				$crawler = new RongApi($this->env);
				break;
			case '4':
				$crawler = new ClientYd($this->env);
				break;
			case '6':
				$crawler = new ClientSjt();
				break;
			default:
				$crawler = null;
				break;
		}
		if(!$crawler){
			return $this->resp('25027', 'source参数错误');
		}
		$resData = $crawler->returnAjaxData($data);//ajax返回给业务端
		return $resData;
	}

	private function opDecrypt($requestid){//解密
		$requestid = Crypt3Des::decrypt($requestid, Yii::$app->params['trideskey']);
		return $requestid;
	}

	private function getCssfile($aid){
		if(!isset($aid)){
			$css = 'index';
		}
		if($aid == 8){
			$css = 'index_9';
		}else if($aid == 10){
			$css = 'index_10';
		}else{
			$css = 'index';
		}
		return $css;
	}

}
