<?php
/**
 * 畅捷协议支付页面方法
 */
namespace app\controllers;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\cjt\CjxyOrder;
use app\models\Payorder;
use app\modules\api\common\cjxy\CCjxy;
use app\modules\api\common\cjxy\CjxyApi;

class CjxyController extends BaseController {

	public $layout = false;
	private $oCCj;

	public function init() {
		parent::init();
		$this->oCCj = new CCjxy();
	}
	
	public function beforeAction($action) {
		if (in_array($action->id, ['backbind', 'backpay','payurl'])) {
			// 局部关闭csrf验证
			$action->controller->enableCsrfValidation = false;
		}
		return parent::beforeAction($action);
	}
	
	// 支付页面
	public function actionPayurl() {
		//1 验证参数是否正确
		$cryid = $this->get('xhhorderid', '');
		$order_id = (new CjxyOrder)->decryptId($cryid);
		if (!$order_id) {
			return $this->showMessage(140101, "订单不合法或信息不完整");
		}
		//2  获取是否存在该订单
		$oCjOrder = (new CjxyOrder)->getById($order_id);
		if (!$oCjOrder) {
			return $this->showMessage(140102, '此订单不存在');
		}
		//3 获取主订单
		$oPayorder = (new Payorder)->getByOrder($oCjOrder->orderid, $oCjOrder->aid);
		if (!$oPayorder) {
			return $this->showMessage(140204, "主订单异常,请联系相关人员");
		}
		//4 按状态进行处理
		if (in_array($oCjOrder->status,[Payorder::STATUS_INIT,Payorder::STATUS_PREDO])) {
			return $this->render('/pay/payurl', [
				'oPayorder' => $oCjOrder,
				'xhhorderid' => $cryid,
				'smsurl' => "/cjxy/getsmscode",
			]);
		} elseif ($oCjOrder->status == Payorder::STATUS_PAYOK) {
			return $this->showMessage(140103, '此订单已经处理完毕, 并且支付成功');
		} elseif ($oCjOrder->status == Payorder::STATUS_PAYFAIL) {
			return $this->showMessage(140104, '此订单已经处理完毕, 并且支付失败');
		} else {
			return $this->showMessage(140105, '此订单状态不合法');
		}
	}

	// 发送验证码
	public function actionGetsmscode() {
		//1 验证参数是否正确
		$cryid = $this->post('xhhorderid', '');
		$order_id = (new CjxyOrder)->decryptId($cryid);
		if (!$order_id) {
			return $this->showMessage(140201, "订单不合法或信息不完整");
		}
		//2  获取是否存在该订单
		$oCjOrder = (new CjxyOrder)->getById($order_id);
		if (!$oCjOrder) {
			return $this->showMessage(140202, '此订单不存在');
		}
		//3 获取主订单
		$oPayorder = (new Payorder)->getByOrder($oCjOrder->orderid, $oCjOrder->aid);
		if (!$oPayorder) {
			return $this->showMessage(140204, "主订单异常,请联系相关人员");
		}
		if(empty($oCjOrder->has_send)){
			//4 发送验证码
			$has_send = $oCjOrder->has_send+1;
			$oCjOrder->updateOrder(['has_send'=>$has_send]);
			$status = $this->oCCj->pay($oCjOrder);
			if($status != Payorder::STATUS_PREDO){
				return $this->showMessage($oCjOrder->error_code,$oCjOrder->error_msg);
			}
		}else{
			//4. 重发短信
			$has_send = $oCjOrder->has_send+1;
			$oCjOrder->updateOrder(['has_send'=>$has_send]);
			$msgresult = $this->oCCj->reSend($oCjOrder);
			if(!$msgresult){
				return $this->showMessage(140205,"该订单已失效，请重新发起请求！");
			}
		}
		//5. 返回结果
		return $this->showMessage(0, [
			'isbind' => false,
			'nexturl' => Yii::$app->request->hostInfo . '/cjxy/paycomfirm',
		]);
	}


	// 确认支付
	public function actionPaycomfirm() {
		//1 验证参数是否正确
		$cryid = $this->post('xhhorderid', '');
		$order_id = (new CjxyOrder)->decryptId($cryid);
		if (!$order_id) {
			return $this->showMessage(140301, "订单不合法或信息不完整");
		}
		$validatecode = $this->post('validatecode');
		if (empty($validatecode)) {
			return $this->showMessage(140303, "请输入手机验证码");
		}
		//2 获取是否存在该订单
		$oCjOrder = (new CjxyOrder)->getById($order_id);
		if (!$oCjOrder) {
			return $this->showMessage(140302, '此订单不存在');
		}
		//3 获取主订单
		$oPayorder = $oCjOrder->payorder;
		if (!$oPayorder) {
			return $this->showMessage(140204, "主订单异常,请联系相关人员");
		}
		if (in_array($oPayorder->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL, Payorder::STATUS_DOING])) {
			return $this->showMessage(140206, "订单已完成,请勿重复请求");
		}
		//4 调用支付接口
		$status = $this->oCCj->confirmPay($oCjOrder,$validatecode);
		//5 判断支付状态
		if (in_array($status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL, Payorder::STATUS_DOING])) {
			$url = $oPayorder->clientBackurl();
			return $this->showMessage(0, [
				'callbackurl' => $url,
			]);
		}else if(in_array($status, [Payorder::STATUS_PREDO])){
			return $this->showMessage($oCjOrder->error_code,$oCjOrder->error_msg);
		}else {
			return $this->showMessage(140308, "订单处理失败");
		}
	}

	/**
	 * 显示结果信息
	 * @param $res_code 错误码0 正确  | >0错误
	 * @param $res_data	  结果   | 错误原因
	 */
	protected function showMessage($res_code, $res_data, $type = 'json', $redirect = null) {
		switch ($type) {
		case 'json':
			return json_encode([
				'res_code' => $res_code,
				'res_data' => $res_data,
			],JSON_UNESCAPED_UNICODE);
			break;
		default:
			return $this->render('/pay/showmessage', [
				'res_code' => $res_code,
				'res_data' => $res_data,
			]);
			break;
		}
	}

	// 支付结果异步通知接口
	public function actionBackpay($cfg = 'dev') {
		// 数据获取
		$postdata = Yii::$app->request->post();
		Logger::dayLog('cjxy', 'backpay:异步通知数据', $cfg, $postdata);
		// 无响应时不处理
		if (empty($postdata)) {
			exit;
		}
		//验签
		$oCBack = new CjxyApi($cfg);
		$checkSign = $oCBack->verify($postdata);
		if(!$checkSign){
			exit;
		}
		//处理订单状态
		$cli_orderid = ArrayHelper::getValue($postdata,'outer_trade_no','');//商户平台订单号
		$other_orderid = ArrayHelper::getValue($postdata,'inner_trade_no','');//畅捷流水号
		$trade_status = ArrayHelper::getValue($postdata,'trade_status','');//交易状态
		$trade_amount = ArrayHelper::getValue($postdata,'trade_amount','');//交易金额
		if(empty($cli_orderid)){
			Logger::dayLog('cjxy', 'backpay:商户平台订单号为空');
			exit;
		}
		$oCjOrder = (new CjxyOrder)->getByCliOrderId($cli_orderid);
		if(empty($oCjOrder)){
			Logger::dayLog('cjxy', 'backpay:查询不到订单');
			exit;
		}
		if(bccomp((string)$oCjOrder->amount, (string)($trade_amount*100)) !== 0){
			Logger::dayLog('cjxy', "backpay:订单金额和交易金额不相符");
			exit;
		}
		if($oCjOrder->is_finished()){
			echo 'success';
			exit;
		}
		if(!in_array($trade_status, ['TRADE_SUCCESS','TRADE_FINISHED','TRADE_CLOSED'])){
			Logger::dayLog('cjxy', "backpay:异步通知客户端", '订单状态错误,等待下次回调');
			exit;
		}

		if($trade_status=='TRADE_SUCCESS' || $trade_status=='TRADE_FINISHED'){
			//成功时处理
			$result = $oCjOrder->savePaySuccess($other_orderid);
			if(!$result){
				Logger::dayLog('cjxy', "backpay:修改订单状态失败");
				exit;
			}
		}else{
			// 订单关闭时处理
			$result = $oCjOrder->savePayFail('9999', '订单已关闭');
			if(!$result){
				Logger::dayLog('cjxy', "backpay:修改订单状态失败");
				exit;
			}
		}
		//异步通知客户端
		$result = $oCjOrder->payorder->clientNotify();
		if (!$result) {
			Logger::dayLog('cjxy', 'backpay:异步通知客户端失败');
			exit;
		}
		Logger::dayLog('cjxy', 'backpay:异步通知修改订单状态成功');
		echo 'success';
		exit;
	}
}
