<?php
/**
 * 畅捷支付回调地址
 * 
 */
namespace app\modules\api\controllers;
use app\common\Logger;
use app\common\Http;
use app\models\App;
use app\models\chanpay\ChanpayOnlineOrder;
use app\modules\api\common\ApiController;
use app\modules\api\common\chanpay\ChanpayOnline;

class ChanpaybackController extends ApiController {
	private $chanpay;
	public function init() {
		//parent::init(); 千万不要执行父类的验证方法
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		$this->chanpay = new ChanpayOnline($env);
	}

	public function actionIndex() {

	}
	
	public function actionOnlinereturn() {
		$array_notify = $this->getParamArr();
		Logger::dayLog('chanpayback/onlinereturn', 'get', $this->get(), 'post',$_POST, 'post_2', $array_notify);
		$data = $this->post();
		//$data = $this->testRemit();//@todo
		if(!is_array($data) || empty($data)){
			return '';
		}
		
		$sign   =   $data['sign'];
		unset($data['sign']);
		unset($data['sign_type']);
		$flag   =   $this->chanpayonline->rsaVerify($data,$sign);
		if(!$flag){
			//签名验证失败
			
		}
		
		//查询业务方提交的同步跳转地址，并带上支付结果跳转至相关地址
	}
	
	/**
	 * 畅捷网银支付: 异步回调地址
	 * @return [type] [description]
	 */
	public function actionOnlinenotify() {
		//1 检测数据合法性
		Logger::dayLog('chanpayback/onlinenotify', 'post',$this->post());
		$data = $this->post();
// 		$data = [
// 				'notify_time' => '20161114154704',
// 				'sign_type' => 'RSA',
// 				'notify_type' => 'trade_status_sync',
// 				'gmt_payment' => '20161114154240',
// 				'trade_status' => 'TRADE_SUCCESS',
// 				'version' => '1.0',
// 				'sign' => 'fYbm6t715ImT9+Gqo4GHigg4H4it3Y6vWUnxiqW9Ne0cGc002+13c5lYwWGBDcXPf3ZDvoND66acMWyOr7AaoEnyUdx4b/IRQIsEFhMBuyhXokOHZU1v7xDnjfrLVlwhTCAXxytcm3JLj271rq1No85v/fgPP3FPU68Ied6/cTQ=',
// 				'extension' => '{}',
// 				'gmt_create' => '20161114154240',
// 				'_input_charset' => 'UTF-8',
// 				'outer_trade_no' => '1_2016111415403411939',
// 				'trade_amount' => '10.00',
// 				'inner_trade_no' => '101147910923422794873',
// 				'notify_id' => '3385298d8b5c45c3b40d5bad32761439',
// 		];
		
		//$data = $this->testRemit();//@todo
		if(!is_array($data) || empty($data)){
			return '';
		}
		
		$sign   =   $data['sign'];
		unset($data['sign']);
		unset($data['sign_type']);
		$flag   = $this->chanpay->checksign($data,$sign);
		if(!$flag){
			//签名验证失败
			Logger::dayLog('chanpayback/onlinenotify', '验签失败');
		}

		//修改网银充值记录的状态
		//畅捷订单号
		$order_id = $data['outer_trade_no'];
		//支付平台交易订单号
		$req_id = $data['inner_trade_no'];
		//交易状态
		$trade_status = $data['trade_status'];
		if($trade_status == 'TRADE_SUCCESS'){
			//支付成功
			$chanpayorder = ChanpayOnlineOrder::find()->where(['aid_orderid' => $order_id])->one();
			if(empty($chanpayorder)){
				Logger::dayLog('chanpayback/onlinenotify', '订单不存在', $order_id);
				return '';
			}
			$chanpayorder->pay_status = 2;
			$chanpayorder->chanpayborderid = $req_id;
			$chanpayorder->modify_time = date('Y-m-d H:i:s');
			
			$result = $chanpayorder->save();
			if(!$result){
				Logger::dayLog('chanpayback/onlinenotify', '修改订单状态失败', $order_id);
				return '';
			}
			//推送相关结果给业务端
// 			$res_data = [
// 					'status' => $chanpayorder->pay_status, // 此时只有成功的状态. 因为成功才通知
// 					'orderid' => $chanpayorder->orderid, //订单号
//					'paymodel' => 103,
// 					'chanpayborderid' => $chanpayorder->chanpayborderid, //第三方支付流水号
// 					'amount' => $chanpayorder->amount/100, //交易金额
// 					];
// 			$responseData = App::model()->encryptData($chanpayorder->aid, $res_data);
// 			if (empty($responseData)) {
// 				Logger::dayLog('chanpayback/onlinenotify', '无法加密', 'appid', $chanpayorder->aid, 'res_data', $res_data);
// 				return '';
// 			}
			
// 			$result = $this->doPost($chanpayorder->callbackurl, $responseData);
// 			Logger::dayLog( 'chanpayback/post', "客户响应|{$result}|", $chanpayorder->orderid, $chanpayorder->callbackurl, $responseData, $res_data);
// 			if (!$result) {
// 				return '';
// 			}
			//保存推送的结果
			$chanpayorder->refresh();
			$chanpayorder->client_status = 1;
			$res = $chanpayorder->save();
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
}
