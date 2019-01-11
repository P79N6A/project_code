<?php
namespace app\modules\api\controllers;

use Yii;
use app\modules\api\common\ApiController;
use app\modules\api\common\chanpay\ChanpayQuick;
use app\models\chanpay\ChanpayQuickOrder;
use app\common\Logger;
use app\common\Crypt3Des;


/**
 * 畅捷单笔订单快捷支付API
 * 内部错误码范围1000-1999
 */
class ChanpayquickController extends ApiController
{

	/**
	 * 服务id号
	 */
	protected $server_id = 103;
	
	/**
	 * 畅捷接口文档
	 */
	private $chanpay;
	
	/**
	 * 初始化
	 */
	public function init(){
		//parent::init();
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		$this->chanpay = new ChanpayQuick($env);
	}
	
	/**
	 * 单笔订单快捷支付API接口
	 */
	public function actionIndex(){
    	//$data = $this->reqData;
    	$data = [
    		    'aid' => 1, //应用ID
    			'orderid' => date('YmdHis').rand(10000,99999),  //姓名
    			'trade_amount' => '1', //付款金额
    			'buyer_mobile' => '13500000000',  //
    			'card_type' => 'CC',
    			'bank_code' => 'CMB',
    			'payer_name' => '高炼',
    			'productname' => '购买电子产品',
    			'expired_time' => '60m',
    			'payer_card_no' => '4392258318887967',
    			'id_number' => '429001198507070016',
    			'phone_number' => '13269311057',
    			'expiry_date' => '1220',
    			'cvv2' => '097',
    			'callbackurl' => 'http://open.xianhuahua.com/api/chanpayback/onlinenotify',
    			'userip' => '127.0.0.1',
    	];
    	
    	if( !isset($data['aid']) ){
    		return $this->resp(10001, "应用id不能为空");
    	}
    	if( !isset($data['orderid']) ){
    		return $this->resp(10002, "订单号不能为空");
    	}
    	if( !isset($data['trade_amount']) ){
    		return $this->resp(10003, "充值金额不能为空");
    	}
    	if( !isset($data['card_type']) ){
    		return $this->resp(10004, "银行卡类型不能为空");
    	}
    	if( !isset($data['bank_code']) ){
    		return $this->resp(10001, "银行编号不能为空");
    	}
    	if( !isset($data['payer_name']) ){
    		return $this->resp(10002, "付款方名称	不能为空");
    	}
    	if( !isset($data['payer_card_no']) ){
    		return $this->resp(10003, "付款方银行卡号不能为空");
    	}
    	if( !isset($data['id_number']) ){
    		return $this->resp(10004, "身份证号不能为空");
    	}
    	if( !isset($data['phone_number']) ){
    		return $this->resp(10004, "手机号不能为空");
    	}
    	
    	$condition = array(
    			'aid' => $data['aid'],
    			'orderid' => $data['orderid'],
    			'aid_orderid' => $data['aid'].'_'.$data['orderid'],
    			'currency' => 156,
    			'amount' => $data['trade_amount'],
    			'productname' => $data['productname'],
    			'payer_name' => $data['payer_name'],
    			'id_number' => $data['id_number'],
    			'buyer_mobile' => $data['buyer_mobile'],
    			'phone_number' => $data['phone_number'],
    			'payer_card_no' => $data['payer_card_no'],
    			'card_type' => $data['card_type'],
    			'bank_code' => $data['bank_code'],
    			'expiry_date' => isset($data['expiry_date']) ? Crypt3Des::encrypt($data['expiry_date'],chanpay_3des_key) : '',
    			'cvv2' => isset($data['cvv2']) ? Crypt3Des::encrypt($data['cvv2'],chanpay_3des_key) : '',
    			'orderexpdate' => $data['expired_time'],
    			'callbackurl' => $data['callbackurl'],
    			'userip' => $data['userip'],
    	);
    		
    	$chanpayQuickOrder = new ChanpayQuickOrder();
    	$result = $chanpayQuickOrder->saveOrder($condition);
    	
    	$result = $this->chanpay->quickpayment($data['aid'].'_'.$data['orderid'], $data['trade_amount']/100, $data['expired_time'], $data['buyer_mobile'], $data['card_type'], $data['bank_code'], $data['payer_name'], $data['payer_card_no'], $data['id_number'], $data['phone_number'], $data['expiry_date'], $data['cvv2']);
    	$de_result = json_decode($result); 
    	
    	return $this->resp(0, [
			'out_trade_no'	 => $de_result->outer_trade_no,
			'authenticate_status' => $de_result->authenticate_status,
    		'err_msg' => isset($de_result->err_msg) ? $de_result->err_msg : ''
		]);
	}
	
	/**
	 * 快捷支付交易确认接口
	 */
	public function actionConfirm(){
		$data = $this->reqData;
// 		$data = [
// 				'aid' => 1, //应用ID
// 				'orderid' => '1_2016110718445522510',  //订单号
// 				'code' => '123456'
// 				];
		
		if( !isset($data['aid']) ){
			return $this->resp(10001, "应用id不能为空");
		}
		if( !isset($data['orderid']) ){
			return $this->resp(10002, "订单号不能为空");
		}
		if( !isset($data['code']) ){
			return $this->resp(10002, "验证码不能为空");
		}
		
		//如果验证码是自己发送，则需要判断验证码是否正确
		
		$result = $this->chanpay->quickconfirm($data['orderid'], $data['code']);
		$de_result = json_decode($result); 
		
		//订单号
		$outer_trade_no = $de_result->outer_trade_no;
		//支付状态
		$trade_status = $de_result->trade_status;
		//失败原因
		$err_msg = isset($de_result->err_msg) ? $de_result->err_msg : '';
		
		if($trade_status == '0'){
			$pay_status = STATUS_PAYOK;
		}else if($trade_status == '2'){
			$pay_status = STATUS_PAYFAIL;
		}else{
			$pay_status = STATUS_PAYING;
		}
		
		
		//查询订单信息
		$quick_order = ChanpayQuickOrder::find()->where(['aid_orderid'=>$outer_trade_no])->one();
		if(empty($quick_order)){
			Logger::dayLog('chanpayquick/confirm', '订单不存在', $outer_trade_no);
			return '';
		}
		
		$quick_order->pay_status = $pay_status;
		$quick_order->modify_time = date('Y-m-d H:i:s');
		$quick_order->closetime = date('Y-m-d H:i:s');
		$quick_order->error_msg = $err_msg;
		
		$result = $quick_order->save();
		if(!$result){
			Logger::dayLog('chanpayquick/confirm', '修改订单状态失败', $outer_trade_no);
			return '';
		}
		
		return $this->resp(0, [
				'status'	 => $pay_status,
				'orderid' => $quick_order->orderid,
				'paymodel' => 103,
				'amount' => ($quick_order->amount)/100,
				'err_msg' => $err_msg
		]);	
	}
	
	/**
	 * 支付结果查询接口
	 */
	public function actionTradequery(){
		$data = $this->reqData;
		$data = [
				'aid' => 1, //应用ID
				'orderid' => '1_2016110718445522510',  //订单号
				'trade_type' => 'INSTANT'
				];
		
		$result = $this->chanpay->tradequery($data['orderid'], $data['trade_type']);
		print_r($result);exit;
	}
}
