<?php
/**
 * 畅捷协议支付逻辑处理
 * @author 孙瑞
 */
namespace app\modules\api\common\cjxy;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\Payorder;
use app\models\cjt\CjxyOrder;

class CCjxy {
	//短信可以重新获取的错误码 分两类
	private static $smsSendCode = ['QT100025','QT100024','QT700001'];
	private static $smsSendCodes = ['FIELD_TYPE_ERROR'];

	public function init() {
		parent::init();
	}

	/**
	 * 获取此通道对应的配置
	 * @param  int $channel_id 通道
	 * @return str dev | prod177
	 */
	private function getCfg($channel_id) {
		$is_prod = SYSTEM_PROD ? true : false;
		$is_prod = true;
		$cfg = $is_prod ? "prod{$channel_id}" : 'dev';
		return $cfg;
	}

	/**
	 * 按aid取不同的配置
	 * @param  int $channel_id 用于区分不同的商编
	 * @return oApi
	 */
	private function getApi($channel_id) {
		static $map = [];
		if (!isset($map[$channel_id])) {
			$cfg = $this->getCfg($channel_id);
			$map[$channel_id] = new CjxyApi($cfg);
		}
		return $map[$channel_id];
	}

	/**
	 * 创建子订单生成支付链接地址
	 * @param $oPayorder 主订单对象
	 * @return array
	 */
	public function createOrder($oPayorder) {
		//1  基本参数检验
		if (!ArrayHelper::getValue($oPayorder, 'orderid', '')) {
			return ['res_code' => 2001, 'res_data' => '订单号不可为空'];
		}
		if (!ArrayHelper::getValue($oPayorder, 'identityid', '')) {
			return ['res_code' => 2002, 'res_data' => 'identityid不可为空'];
		}

		//2 保存子订单数据表
		$postData = $oPayorder->attributes;
		$postData['payorder_id'] = $postData['id'];
		$oCjxyOrder = new CjxyOrder();
		$result = $oCjxyOrder->saveOrder($postData);
		if (!$result) {
			Logger::dayLog('cjxy', 'CCjxy/createOrder:创建子订单失败', $oCjxyOrder->attributes, $oCjxyOrder->errors);
			return ['res_code' => 2003, 'res_data' => '订单保存失败'];
		}

		//3. 同步主订单状态
		$oPayorder->saveStatus($oCjxyOrder->status);
		$res = $oCjxyOrder->getPayUrls();
		Logger::dayLog('cjxy', 'CCjxy/createOrder:获取跳转支付链接地址', $res);
		return ['res_code' => 0, 'res_data' => $res];
	}

	/**
	 * 畅捷支付请求
	 * @param $oCjOrder 畅捷子订单对象
	 * @return boolean
	 */
	public function pay($oCjOrder){
		if(empty($oCjOrder)){
			return false;
		}
		// 调用畅捷直接支付接口传递支付信息
		$postData = $oCjOrder->attributes;
		$postData['notify_url'] = Yii::$app->request->hostInfo.'/cjxy/backpay/'.$this->getCfg($oCjOrder->channel_id);
		$result = $this->getApi($oCjOrder->channel_id)->pay($postData);
		Logger::dayLog('cjxy', 'CCjxy/pay:获取验证码',$result);
		if(empty($result)){
			return false;
		}
		$AcceptStatus = ArrayHelper::getValue($result, 'AcceptStatus', '');
		$RetCode = ArrayHelper::getValue($result, 'RetCode', '');
		$RetMsg = ArrayHelper::getValue($result, 'RetMsg', '');
		if($AcceptStatus=='S'){
			//成功时处理
			$result = $oCjOrder->saveStatus(Payorder::STATUS_PREDO);
		}else{
			// 失败时处理
			$result = $oCjOrder->savePayFail($RetCode, $RetMsg);
		}
		//返回当前状态
		return $oCjOrder->status;
	}

	/**
	 * 再次发送验证码
	 * @param $oCjOrder 畅捷子订单对象
	 * @return boolean
	 */
	public function reSend($oCjOrder){
		if($oCjOrder->status != Payorder::STATUS_PREDO){
			return false;
		}
		// 调用畅捷再次发送验证码接口
		$postData = $oCjOrder->attributes;
		$result = $this->getApi($oCjOrder->channel_id)->reSend($postData);
		Logger::dayLog('cjxy', 'CCjxy/reSend:再次发送验证码',$result);
		if(empty($result)){
			return false;
		}
		$AcceptStatus = ArrayHelper::getValue($result, 'AcceptStatus', '');
		$Status = ArrayHelper::getValue($result, 'Status', ''); // 业务状态
		if($AcceptStatus=='F'){
			return false;
		}
		if(empty($Status) || $Status=='F'){
			return false;
		}
		return true;
	}

	/**
	 * 畅捷订单确认
	 * @param $oCjOrder 畅捷子订单对象
	 * @param $smscode 验证码
	 * @return boolean
	 */
	public function confirmPay($oCjOrder,$smscode){
		if(empty($oCjOrder) || empty($smscode)){
			return false;
		}
		// 调用畅捷订单确认接口
		$postData = $oCjOrder->attributes;
		$postData['smscode'] = $smscode;
		$saveResult = $oCjOrder->saveStatus(Payorder::STATUS_DOING, '');
		if (!$saveResult) {
			Logger::dayLog('cjxy', 'CCjxy/confirmPay:修改订单状态失败',$saveResult->errors);
		}
		$result = $this->getApi($oCjOrder->channel_id)->confirmPay($postData);
		Logger::dayLog('cjxy', 'CCjxy/confirmPay:畅捷订单确认：',$result);
		if(empty($result)){
			// 超时
			$oCjOrder->saveStatus(Payorder::STATUS_DOING,'',['ERROR', '请求超时,订单锁定']);
			// 返回当前状态
			return $oCjOrder->status;
		}
		$AcceptStatus = ArrayHelper::getValue($result, 'AcceptStatus', '');// 应用状态
		$AppRetcode = isset($result['AppRetcode'])?$result['AppRetcode']:''; // 应用返回码
		$AppRetMsg = isset($result['AppRetMsg'])?$result['AppRetMsg']:''; // 应用返回描述
		$RetCode = ArrayHelper::getValue($result, 'RetCode', ''); // 业务返回码
		$RetMsg = ArrayHelper::getValue($result, 'RetMsg', ''); // 业务返回描述
		$Status = ArrayHelper::getValue($result, 'Status', ''); // 业务状态
		$OrderTrxid = ArrayHelper::getValue($result, 'OrderTrxid', ''); // 畅捷订单号
		// 请求的状态码
		if(!in_array($AcceptStatus,['F','S'])){
			$oCjOrder->saveStatus(Payorder::STATUS_DOING,'',['ERROR', '返回状态码错误,订单锁定']);
			return $oCjOrder->status;
		}
		// 应用失败时处理
		if($AcceptStatus =='F'){
			if(in_array($AppRetcode,self::$smsSendCode)){
				$oCjOrder->saveStatus(Payorder::STATUS_PREDO,'',[$AppRetcode, $AppRetMsg]);
			}else if(in_array($RetCode,self::$smsSendCodes)){
				$oCjOrder->saveStatus(Payorder::STATUS_PREDO,'',[$RetCode, '短信验证码错误！']);
			}else{
				$oCjOrder->saveStatus(Payorder::STATUS_DOING,$OrderTrxid,[$AppRetcode, $AppRetMsg]);
			}
			return $oCjOrder->status;
		}
		if($AcceptStatus =='S'){
			if($Status =='S'){
				// 成功时处理
				$oCjOrder->refresh();
				$result = $oCjOrder->savePaySuccess($OrderTrxid);
			}else if($Status =='F'){
				// 业务失败时处理
				$oCjOrder->refresh();
				$result = $oCjOrder->savePayFail($RetCode, $RetMsg);
			}else{
				// 处理中
				$oCjOrder->saveStatus(Payorder::STATUS_DOING,$OrderTrxid,[$AppRetcode, $AppRetMsg]);
				return $oCjOrder->status;
			}
		}

		// 异步通知客户端
		$oCjxyOrder = new CjxyOrder();
		$oCjOrder_info = $oCjxyOrder->getById($oCjOrder->id);
		$oCjOrder_info->payorder->clientNotify();
		return $oCjOrder->status;
	}

	/**
	 * 锁定订单补单查询
	 * @return int 查询成功条数
	 */
	public function runQuery($start_time, $end_time) {
		$model = new CjxyOrder();
		$dataList =$model->getAbnorList($start_time, $end_time);
		$success = 0;
		$total = count($dataList);
		Logger::dayLog('cjxy', 'command/runQuery', '畅捷协议支付待补单条数'.$total);
		if($total > 0){
			foreach ($dataList as $oCjOrder) {
				$result = $this->orderQuery($oCjOrder);
				if (isset($result['res_code']) && $result['res_code'] == 0){
					$success++;
				}
			}
		}
		return $success;
	}

	/**
	 * 逐条查询锁定订单
	 * @param $oCjOrder 畅捷子订单对象
	 * @return array
	 */
	private function orderQuery($oCjOrder){
		// 判断订单状态
		if($oCjOrder->status != Payorder::STATUS_DOING){
			return ['res_code'=>-1,'res_data'=>'订单不是锁定状态'];
		}
		$postData = $oCjOrder->attributes;
		Logger::dayLog('cjxy', 'command/runQuery', '畅捷协议支付补单数据', $postData);
		$result = $this->getApi($oCjOrder->channel_id)->queryOrder($postData);
		if(empty($result)){
			// 超时
			return ['res_code'=>-1,'res_data'=>"请求超时，稍后重试"];
		}
		$AcceptStatus = ArrayHelper::getValue($result, 'AcceptStatus', '');// 应用状态
		$RetCode = ArrayHelper::getValue($result, 'RetCode', ''); // 业务返回码
		$RetMsg = ArrayHelper::getValue($result, 'RetMsg', ''); // 业务返回描述
		$Status = ArrayHelper::getValue($result, 'Status', ''); // 业务状态
		$OrderTrxid = ArrayHelper::getValue($result, 'OrderTrxid', ''); // 畅捷订单号
		// 应用失败时处理
		if($AcceptStatus=='F'){
			return ['res_code'=>$RetCode,'res_data'=>$RetMsg];
		}
		$resultInfo = [];
		if($Status=='S'){
			// 成功时处理
			$result = $oCjOrder->savePaySuccess($OrderTrxid);
			$resultInfo = ['res_code'=>0,'res_data'=>'操作成功'];
		}else if($Status=='F'){
			// 业务失败时处理
			$result = $oCjOrder->savePayFail($RetCode, $RetMsg,$OrderTrxid);
			$resultInfo = ['res_code'=>$RetCode,'res_data'=>$RetMsg];
		}else{
			return ['res_code'=>-1,'res_data'=>'支付处理中'];
		}
		// 异步通知客户端
		$oCjOrder->payorder->clientNotify();
		return $resultInfo;
	}
}