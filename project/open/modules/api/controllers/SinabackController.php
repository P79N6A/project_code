<?php
/**
 * 易宝一键支付回调接口 内部错误码范围2800-2899
 * 易宝投资通回调接口 内部错误码范围2900-2999
 */
namespace app\modules\api\controllers;
use app\common\Http;
use app\common\Logger;
use app\models\App;
use app\models\sina\SinaRemit;
use app\models\sina\SinaUser;
use app\models\sina\SinaAutoRecharge;
use app\modules\api\common\ApiController;
use app\modules\api\common\sinapay\Sinapay;

class SinabackController extends ApiController {
	private $oSinapay;
	public function init() {
		//parent::init(); 千万不要执行父类的验证方法
		//$env = YII_ENV_DEV ? 'dev' : 'prod';
		$this->oSinapay = new Sinapay;
	}

	public function actionIndex() {
	}
	/**
	 * 出款: 回调地址
	 * @return [type] [description]
	 */
	public function actionRemitnotifyurl() {
		//1 检测数据合法性
		Logger::dayLog('sinaback/remitnotifyurl', 'get', $this->get(), 'post',$this->post());
		$data = $this->post();
		//$data = $this->testRemit();//@todo
		if(!is_array($data) || empty($data)){
			return '';
		}
		ksort($data);
		$weibopay = new \Weibopay();
		$sign_ok = $weibopay->checkSignMsg($data, $data["sign_type"], $data["_input_charset"]);
		if (!$sign_ok) {
			Logger::dayLog('sinaback/remitnotifyurl', '验签失败');
		}
		
		if (!isset($data['outer_trade_no']) || !isset($data['withdraw_status'])) {
			return '';
		}

		//2 获取出款纪录
		$req_id = $data['outer_trade_no'];
		$withdraw_status = $data['withdraw_status'];
		$client_id = $data['inner_trade_no'];
		$remit = new SinaRemit;
		$oSinaRemit = $remit->getByReqId($req_id);
		if (!$oSinaRemit) {
			Logger::dayLog('sinaback/remitnotifyurl', "req_id", $req_id, '不存在');
			return '';
		}

		//3 更新出款纪录 异步的响应肯定是ok的
		$message = '异步通知';
		if(isset($data['error_code'])){
			$message .= $data['error_code'];
		}
		if(isset($data['error_message'])){
			$message .= $data['error_message'];
		}
		
		$result = $oSinaRemit->saveRspStatus('APPLY_SUCCESS', $message, $withdraw_status, $client_id);
		if (!$result) {
			Logger::dayLog('sinaback/remitnotifyurl', "保存失败", $req_id, $withdraw_status);
		}

		//4 判断是否是终态
		$finalStatus = [SinaRemit::STATUS_SUCCESS, SinaRemit::STATUS_FAILURE];
		if (!in_array($oSinaRemit->remit_status, $finalStatus)) {
			Logger::dayLog('sinaback/remitnotifyurl', $req_id, '不是终态', $withdraw_status, $oSinaRemit->remit_status, $client_id);
			return '';
		}

		//5 发送请求通知客户端
		$res_data = [
			'req_id' => $oSinaRemit['req_id'],
			'client_id' => $oSinaRemit['client_id'],
			'settle_amount' => $oSinaRemit['settle_amount'],
			'remit_status' => $oSinaRemit['remit_status'],
			'rsp_status' => $oSinaRemit['rsp_status'],
			'rsp_status_text' => $oSinaRemit['rsp_status_text'],
		];
		$responseData = App::model()->encryptData($oSinaRemit->aid, $res_data);
		if (empty($responseData)) {
			Logger::dayLog('sinaback/remitnotifyurl', '无法加密', 'appid', $oSinaRemit->aid, 'res_data', $res_data);
			return '';
		}

		//4 获取给客户端的回调地址
		$result = $this->doPost($oSinaRemit->callbackurl, $responseData);
		Logger::dayLog( 'sinaback/post', "客户响应|{$result}|", $oSinaRemit['req_id'], $callbackurl, $responseData, $res_data);
		if (!$result) {
			return '';
		}

		//5 保存通知结果
		$oSinaRemit->refresh();
		$oSinaRemit->client_status = 1;
		$res = $oSinaRemit->save();
		return 'success';
	}
	/**
	 * 密码url
	 * 测试链接: http://testopen.xianhuahua.com/api/sinaback/passwordurl?identity_id=A1U1
	 */
	public function actionPasswordurl() {
		Logger::dayLog('sinaback/passwordurl', 'GET', $this->get());

		//1 数据获取
		$identity_id = $this->get('identity_id');
		$oUser = SinaUser::getByIdentityId($identity_id);
		if (!$oUser) {
			Logger::dayLog('sinaback/passwordurl', 'identity_id not found', $identity_id);
			return false;
		}

		//2  检测本地已经更新过了，则没必要再处理一次
		$error_msg = '';
		if ($oUser->password_valid != 1) {
			$sinapay = new Sinapay();
			$res = $sinapay->query_is_set_pay_password($identity_id);
			if ($res) {
				$oUser->password_valid = 1;
				$oUser->modify_time = date('Y-m-d H:i:s');
				$dbres = $oUser->save();
			} else {
				$err = json_decode($sinapay->errinfo, true);
				$error_msg = $err['response_message'];
			}
		}

		//3  加密响应结果
		$res_data = [
			'password_valid' => $oUser->password_valid,
			'user_id' => $oUser->user_id,
			'identity_id' => $oUser->identity_id,
			'error_msg' => $error_msg,
		];
		$responseData = App::model()->encryptData($oUser->aid, $res_data);
		if (empty($responseData)) {
			Logger::dayLog('sinaback/passwordurl', '无法加密', 'appid', $oUser->aid, 'res_data', $res_data);
			return false;
		}

		//4 获取给客户端的回调地址
		$this->doGet($oUser->passwordurl, $responseData);
		return true;
	}
	/**
	 * 充值: 公司内部充值到代收
	 * @return bool
	 */
	public function actionInnerpaynotify(){
		//1 验签
		Logger::dayLog('sinaback/innerpaynotify', 'get', $this->get(), 'post',$this->post());
		$data = $this->post();
		//$data = $this->testInnerpay();//@todo
		if(!is_array($data) || empty($data)){
			return '';
		}
		
		ksort($data);
		$weibopay = new \Weibopay();
		$sign_ok = $weibopay->checkSignMsg($data, $data["sign_type"], $data["_input_charset"]);
		if (!$sign_ok) {
			Logger::dayLog('sinaback/innerpaynotify', '验签失败');
		}

		//2 获取出款纪录
		$req_id = $data['outer_trade_no'];
		$trade_status = $data['trade_status'];
		$client_id = $data['inner_trade_no'];
		$remit = new SinaAutoRecharge;
		$oSinaAutoRecharge = $remit->getByReqId($req_id);
		if (!$oSinaAutoRecharge) {
			Logger::dayLog('sinaback/innerpaynotify', "req_id", $req_id, '不存在');
			return '';
		}

		//3 更新出款纪录: 异步的响应肯定是ok的
		$result = $oSinaAutoRecharge->saveRspStatus('APPLY_SUCCESS', '异步通知', $trade_status, $client_id);
		if (!$result) {
			Logger::dayLog('sinaback/innerpaynotify', "保存失败", $req_id, $trade_status);
		}

		//4 判断是否是终态
		$finalStatus = [SinaAutoRecharge::STATUS_SUCCESS, SinaAutoRecharge::STATUS_FAILURE];
		if (!in_array($oSinaAutoRecharge->status, $finalStatus)) {
			Logger::dayLog('sinaback/innerpaynotify', $req_id, '不是终态', $trade_status, $oSinaRemit->status);
			return '';
		}
		return 'success';
	}
	/**
	 * post后台异步通知
	 * @param $orderModel tzt和wrap均使用此方法,故传递这个用以区分
	 */
	private function doPost($callbackurl, $responseData) {
		$postData = ['res_data' => $responseData, 'res_code' => 0];
		$hostMap = null; //['www.test.com'=>'127.0.0.1'];// @todo
		$result = Http::curlByHost($callbackurl, $postData, $hostMap);
		return strtoupper($result) == 'SUCCESS';
	}
	/**
	 * get前台访问通知
	 * 表示新浪点击返回商户的链接
	 */
	private function doGet($fcallbackurl, $responseData) {
		// 跳转到客户端地址
		$link = strpos($fcallbackurl, "?") ===false  ? '?' : '&';
		$url = $fcallbackurl . $link.'res_data=' . rawurlencode($responseData);
		header("Location:{$url}");
		exit;
	}

	/**
	 * 测试数据: 出款响应
	 * @return [type] [description]
	 */
	/*private function testRemit() {
		return array (
		  'sign' => 'neMmooGkEwrSSiWttWq48zsseHYef2rki9naMESt4/4BrSHHNXIVcbAiK95EUpJD8+2vB/GvSLV7xxlsjBc87p+LISgKEsDOe1nJ0PLRufsg6MnJntY9NCiDNK3WQNlAb0SN7TQnqgF7QX/EOoxx+UBx9wLJj1CCY8j/ybEhCVo=',
		  'notify_time' => '20160809160045',
		  '_input_charset' => 'utf-8',
		  'outer_trade_no' => '14707289235065',
		  'sign_type' => 'RSA',
		  'withdraw_status' => 'SUCCESS',
		  'withdraw_amount' => '1.00',
		  'notify_type' => 'withdraw_status_sync',
		  'notify_id' => '201608091632753071',
		  'inner_trade_no' => '121470728983018074967',
		  'version' => '1.0',
		);
	}*/
	/**
	 * 测试数据: 公司充值代收操作
	 * @return [type] [description]
	 */
	/*private function testInnerpay() {
		return array (
		  'notify_time' => '20160809135958',
		  'sign_type' => 'RSA',
		  'notify_type' => 'trade_status_sync',
		  'gmt_payment' => '20160809113712',
		  'trade_status' => 'PAY_FINISHED',
		  'version' => '1.0',
		  'sign' => 'b/WWuu7ZngkSw8e9ob9uTn5OHI7i/e1fxGvoVn4swCYkMukJ8DU2SY0IYucQuNZGnJop9WrsTnRlv3CNz0AjhvxrAgHXmDbUIpKqqniw2aHuypHi7ZUCUiVVp2E0jDdBjLoCqpONkNyh2qp6OuqQ4syjbCYHq6Cr4WdPTgC3pbQ=',
		  'gmt_create' => '20160809113711',
		  '_input_charset' => 'utf-8',
		  'outer_trade_no' => '14707138321224',
		  'trade_amount' => '5.00',
		  'inner_trade_no' => '111470713831304960805',
		  'notify_id' => '201608091629233591',
		);
	}*/
}
