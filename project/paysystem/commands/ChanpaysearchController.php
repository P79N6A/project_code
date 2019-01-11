<?php
namespace app\commands;
use app\modules\api\common\chanpay\ChanpayQuick;
use app\models\chanpay\ChanpayQuickOrder;
use app\models\chanpay\ChanpayClientNotify;
use app\models\Payorder;
use app\models\BindBank;
use app\common\Logger;


class ChanpaysearchController extends BaseController {
	
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
	 * 查询支付成功，支付失败，支付中的订单,默认查询前10分钟，如果是支付中的，则调用订单查询接口，去查询订单的状态
	 */
	public function quickorder(){
		$start_time = date('Y-m-d H:i:s', (time()-10*60));
		$end_time = date('Y-m-d H:i:s');
		
		//查询支付中的订单
		$where = [
				'AND',
				['>=', 'create_time', $start_time],
				['<', 'create_time', $end_time],
				['pay_status' => ChanpayQuickOrder::STATUS_PAYING],
				];
		
		$rows = ChanpayQuickOrder::find()->where($where)->orderBy("id ASC")->limit(1000)->all();
		Logger::dayLog('chanpaynotify','quickorder','条数', count($rows));
		if (!$rows) {
			return '';
		}
		
		foreach ($rows as $key=>$value) {
			//循环查询支付中的订单的状态
			$result = $this->tradequery($value->aid_orderid, 'INSTANT');
			if($result->trade_status == 'TRADE_SUCCESS' || $result->trade_status == 'TRADE_FINISHED'){
				$pay_status = ChanpayQuickOrder::STATUS_PAYOK;
			}else{
				$pay_status = ChanpayQuickOrder::STATUS_PAYFAIL;
			}
			
			$value->pay_status = $pay_status;
			$value->modify_time = date('Y-m-d H:i:s');
			$value->closetime = date('Y-m-d H:i:s');
			$value->chanpayborderid = isset($result->inner_trade_no) ? $result->inner_trade_no : '';
			
			$result = $value->save();
			if(!$result){
				Logger::dayLog('chanpaynotify/quickorder', '修改订单状态失败', $value->aid_orderid);
				return '';
			}
			
			$result_payorder = $this->setOrderInfo($value->orderid, $pay_status);
			if(!$result){
				Logger::dayLog('chanpaynotify/payorder', '修改订单状态失败', $value->orderid);
				return '';
			}
			
			//修改绑卡的状态
			$bankInfo = BindBank::find()->where(['cardno' => $value->payer_card_no])->one();
			if(empty($bankInfo)){
				Logger::dayLog('chanpaynotify/bankinfo', '银行卡不存在', $value->payer_card_no);
				return '';
			}
			if($bankInfo->status != 1){
				$bankInfo->status = ($pay_status == 2) ? 1 : 2;
				$bankInfo->modify_time = date('Y-m-d H:i:s');
				$bankInfo->save();
			}
			
			//知道支付结果后向推送通知表中添加一条推送记录
			$notify = ChanpayClientNotify::find()->where(['remit_id'=>$value->id])->one();
			if(empty($notify)){
				$condition_notify = array(
						'remit_id' => $value->id,
						'tip' => ($pay_status == 2) ? '支付成功' : '支付失败',
						'remit_status' => ($pay_status == 2) ? 6 : 11,
						'notify_num' => 0,
						'notify_status' => 0,
						'reason' => 'NULL'
				);
					
				$chanpaynotify = new ChanpayClientNotify();
				$result_notify = $chanpaynotify->saveNotify($condition_notify);
			}
		}
		
		return 'success';
	}
	
	/**
	 * 修改订单的状态
	 */
	private function setOrderInfo($order_id, $pay_status){
		$payOrder = Payorder::find()->where(['orderid'=>$order_id])->one();
		if(!empty($payOrder) && ($payOrder->status == 0)){
			$payOrder->status = $pay_status;
			$payOrder->modify_time = date('Y-m-d H:i:s');
			
			$result = $payOrder->save();
			return $result;
		}
	}
	
	/**
	 * 调用交易查询网关接口
	 */
	private function tradequery($orderid, $type){
		$result = $this->chanpay->tradequery($orderid, $type);
		$de_result = json_decode($result);
		return $de_result;
	}
}