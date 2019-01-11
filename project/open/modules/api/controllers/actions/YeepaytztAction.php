<?php
/**
 * 易宝API投资通服务
 * 内部错误码范围2600-2699
 * @author lijin
 */
namespace app\modules\api\controllers\actions;
use Yii;
use app\common\Func;
use app\modules\api\controllers\actions;  

use app\modules\api\common\yeepay\YeepayTzt;


use app\models\Payorder;
use app\models\YpBindbank;
//use app\models\YpBindbankConfrim;
use app\models\YpTztOrder;


use app\common\Logger;

class YeepaytztAction extends BaseAction
{
	/**
	 * 易宝类
	 */
	private $yeepay;
	/**
	 * 绑定银行卡请求类
	 */
	private $bindbankModel;
	/**
	 * 绑定银行卡确认类
	 */
	//private $bindConfrimModel;
	/**
	 * 支付处理类
	 */
	private $orderModel;
	
	/**
	 * 初始化
	 */
	public function init(){
		parent::init();
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		$aid = $this->appData['id'];
		$this->yeepay = new YeepayTzt($env, $aid);
		
		$this->bindbankModel = new YpBindbank();
		//$this->bindConfrimModel = new YpBindbankConfrim();
		$this->orderModel = new YpTztOrder();
	}
	
	// 1. 调用绑卡
	// 错误码 2600-2620
	public function invokebindbankcard(){
		//1. 数据检测
		$postData = $this->reqData;
		if( !is_array($postData) || empty($postData) ){
			return $this->resp( 2601, '没有提交数据！' );
		}
		
		//2. 检测requestid是否存在
		$requestid = $postData['requestid'];
		if( empty($requestid) ){
			return $this->resp(2602,"请求requestid不能为空");
		}
		
		//3. 组合数据
		$postData['aid'] = $aid = $this->appData['id'];
		$postData['idcardtype'] = '01';
		$postData['identitytype'] = 2;
		$postData['create_time'] = $postData['modify_time'] = time();
		
		// 检测是否已经成功绑定过该卡@todo 此处易宝是不建议校验的
		$isBind = $this->bindbankModel -> chkSameUserCard(
									$postData['aid'],
									$postData['identityid'],
									$postData['cardno']
								);
		if($isBind){
			return $this->resp(2605, "此卡已经绑定过了");
		}
		
		//4. 字段检查是否正确
		if( $errors = $this->bindbankModel -> chkAttributes($postData) ){
			Logger::dayLog(
				'yeepaytzt/error',
				'actionInvokebindbankcard',
				'验证失败', 
				'提交数据',$postData,
				'失败原因', $errors
			);
			return $this->resp(2603,implode('|',$errors));
		}
		
		//5. 从易宝api中绑定卡
		// 加上前缀，以免不同的app重复
		$ybData = $postData;
		$ybData['requestid']  = Func::toYeepayCode($ybData['requestid'],$aid);
		$ybData['identityid'] = Func::toYeepayCode($ybData['identityid'],$aid);
    	$ybResult = $this->yeepay -> invokebindbankcard($ybData);
		// @todo
		/*$ybResult = array (
		  'codesender' => 'MERCHANT',
		  'merchantaccount' => '10012471228',
		  'requestid' => $postData['requestid'],
		  'smscode' => '627561',
		);*/
		
    	//6. 保存结果信息
    	$this->bindbankModel -> modify_time = time();
		$isError = is_array($ybResult) && $ybResult['error_code'];
    	if( $isError ){
    		// 失败时处理逻辑
    		$this->bindbankModel -> error_code = $ybResult['error_code'];
    		$this->bindbankModel -> error_msg = $ybResult['error_msg'];
			$this->bindbankModel -> status = YpBindbank::STATUS_REQNO;//请求失败
    		$res = $this->bindbankModel -> save();
		}else{
			// 成功时处理逻辑
			$this->bindbankModel -> status = YpBindbank::STATUS_REQOK;//请求成功
			
			$this->bindbankModel -> codesender = $ybResult['codesender'];
			$this->bindbankModel -> smscode    = $ybResult['smscode'] ? $ybResult['smscode'] : '';
			
			$res = $this->bindbankModel -> save();
		}
		
		//7. 纪录数据库错误日志
		if( !$res ){
			$errors = $this->bindbankModel->errors;
			Logger::dayLog(
				'yeepaytzt/error',
				'actionInvokebindbankcard',
				'保存到db失败', 
				'保存数据', $this->bindbankModel->attributes,
				'错误原因', $errors
			);
		}
		
		//8. 返回客户端失败结果
		if( $isError ){
			return $this-> parseData($ybResult);
		}
		
		//9. 保存到确认绑定表中
		/*$confirmData = [
			'aid' => $aid,
			'requestid'  => $postData['requestid'],
			'codesender' => $ybResult['codesender'],
			'smscode'    => $ybResult['smscode'] ? $ybResult['smscode'] : '',
		];
		$res = $this->bindConfirmSave($confirmData);
		if(!$res){
			return $this->resp(2604, "短信确认信息保存失败(内部dberror)");
		}*/

		//10. 返回客户端结果
		if($postData['payorderid']){
			$payOrderModel = Payorder::model()->getById($postData['payorderid']);
			if($payOrderModel){
				$res = $this->bindbankModel->sendSms(
					$this->bindbankModel -> phone,
					$this->bindbankModel -> smscode,
					$this->bindbankModel -> codesender,
					$payOrderModel->amount,
					$payOrderModel->aid
				);
			}
		}

		
		$resData = [
		  'app_id' => $this->appData['app_id'],
		  'requestid' => $postData['requestid'],
		  'codesender' => $ybResult['codesender'],
		  'smscode' => $ybResult['smscode'],
		];
		return $this->resp(0, $resData);
	}
	/**
	 * 保存到确认表中
	 */
	/*private function bindConfirmSave($postData){
		//1. 验证短信信息
		$postData['create_time'] = $postData['modify_time'] = time();
		if( $errors = $this->bindConfrimModel -> chkAttributes($postData) ){
			Logger::dayLog(
				'yeepaytzt/error',
				'bindConfirmSave',
				'短信-字段验证失败', $postData,
				'错误原因', $errors
			);
			
			return false;
		}
		
		//2. 保存短信信息
		$res = $this->bindConfrimModel -> save();
		if( !$res ){
			$errors = $this->bindConfrimModel->errors;
			Logger::dayLog(
				'yeepaytzt/error',
				'bindConfirmSave',
				'短信-db保存失败', $this->bindConfrimModel->attributes,
				'错误原因', $errors
			);
			return false;
		}
		return true;
	}*/
	// 2. 确认绑卡
	//错误码 2620-2630
	public function confirmbindbankcard(){
		//1. 数据检测
		$postData = $this->reqData;
		if( !is_array($postData) || empty($postData) ){
			return $this->resp( 2621, '没有提交数据！' );
		}
		$aid = $this->appData['id'];
		
		$requestid = $postData['requestid'];
		if( empty($requestid) ){
			return $this->resp( 2622, '请求requestid不能为空！' );
		}
		$validatecode = $postData['validatecode'];
		if( empty($validatecode) ){
			return $this->resp( 2623, '短信验证码不能为空！' );
		}
		$validatecode = (string)$validatecode;
		
		//2. 从数据库中校验
		$bindModel = $this->bindbankModel -> getByRequest($requestid,$aid);
		if( empty($bindModel) ){
			Logger::dayLog(
				'yeepaytzt/error',
				'actionConfirmbindbankcard',
				'没有找到指定的requestid', $requestid
			);
			
			return $this->resp( 2624, '没有找到指定的requestid！' );
		}
		
		if( $bindModel -> status == YpBindbank::STATUS_BINDOK ){
			return $this->resp( 2000, '已经确认绑定成功，不必重复！' );
		}
		
		//3. 校验验证码是否正确
		if( $bindModel->codesender == 'MERCHANT' && $validatecode != $bindModel->smscode ){			
			Logger::dayLog(
				'yeepaytzt/error',
				'actionConfirmbindbankcard',
				'短信验证不正确', 
				'requestid',$requestid,
				'validatecode', $errors
			);
			
			return $this->resp( 2625, '短信验证不正确！' );
		}
		
		// 检测是否已经完成绑卡操作. @todo
		$ypRequestid = Func::toYeepayCode($requestid, $aid);
    	$ybResult =  $this->yeepay -> confirmbindbankcard( $ypRequestid, $validatecode );
    	// @todo 
    	/*
    	$ybResult = array (
		  'bankcode' => 'CMBCHINA',
		  'card_last' => '7653',
		  'card_top' => '621485',
		  'merchantaccount' => '10012471228',
		  'requestid' => $requestid,
		);*/
		
    	$bindModel -> modify_time = time();
		$isError = is_array($ybResult) && $ybResult['error_code'];
    	if( $isError ){
    		// 失败时处理逻辑
    		$bindModel -> error_code = $ybResult['error_code'];
    		$bindModel -> error_msg = $ybResult['error_msg'];
			$bindModel -> status = YpBindbank::STATUS_BINDFAIL;//绑定失败
    		$res = $bindModel -> save();
			
		}else{
			// 成功时处理逻辑
			$bindModel -> status = YpBindbank::STATUS_BINDOK;//确认绑定成功
			$bindModel -> modify_time = time();
			$res = $bindModel -> save();
		}
		
		// 纪录错误日志
		if( !$res ){
			$errors = $bindModel->errors;			
			Logger::dayLog(
				'yeepaytzt/error',
				'actionConfirmbindbankcard',
				'短信保存到db失败', 
				'保存数据', $bindModel->attributes,
				'失败失败', $errors
			);
		}
		
		// 返回失败结果
		if( $isError ){
			return $this-> parseData($ybResult);
		}
		
    	
		// 返回最终成功结果
		$resData = [
		  'app_id' => $this->appData['app_id'],
		  'requestid' => $requestid,
		  'bankcode' => $ybResult['bankcode'],
		  'card_last' => $ybResult['card_last'],
		  'card_top' => $ybResult['card_top'],
		  'pay_type' => Payorder::PAY_TZT,
		];
		return $this->resp(0, $resData);
	}
	
	// 直接支付
	// 错误码范围 2630-2650
	public function directbindpay(){
		//1. 数据检测
		$postData = $this->reqData;
		$aid  = $this->appData['id'];
		if( !is_array($postData) || empty($postData) ){
			return $this->resp( 2631, '没有提交数据！' );
		}
		$orderid = $postData['orderid'];
		if( empty($orderid) ){
			return $this->resp( 2632, '订单号orderid不能为空！' );
		}
		
		//2. 从数据库中校验是否已经存在此数据了
		$dbData = YpTztOrder::model() -> getByOrder($orderid, $aid);
		if( $dbData ){
			if( $dbData -> pay_status == YpTztOrder::STATUS_REQOK ){
				//@todo 这里应该不算错误
				return $this->resp( 2633, '此订单已经在处理中，不要重复提交！' );
			
			}elseif( $dbData -> pay_status == YpTztOrder::STATUS_PAYOK ){
				//@todo 这里应该不算错误
				return $this->resp( 2634, '此订单已经支付成功了，不要重复提交！' );
			
			}else{
				return $this->resp( 2635, '此订单已经存在了！' );
			}
		}

		
		//3. 组合数据
		$aid_orderid = Func::toYeepayCode($postData['orderid'],$aid);
		$postData['aid'] = $aid;
		$postData['aid_orderid'] = $aid_orderid;
		$postData['transtime'] = intval($postData['transtime']);
		$postData['currency'] = 156;
		$postData['amount'] = intval($postData['amount']);
		$postData['identitytype'] = 2;
		$postData['orderexpdate'] = intval($postData['orderexpdate']);
		$postData['create_time'] = $postData['modify_time'] = time();
		$postData['source_type'] = isset($postData['source_type']) ? $postData['source_type'] : 'tzt';
		
		//4. 字段检查是否正确
		$orderModel = new YpTztOrder();
		if( $errors = $orderModel -> chkAttributes($postData) ){
			Logger::dayLog(
				'yeepaytzt/error',
				'actionDirectbindpay',
				'验证失败', 
				'提交数据',$postData,
				'失败原因', $errors
			);
			return $this->resp(2636, implode('|',$errors));
		}
		
		//5. 查看该用户绑定状态, 此处易宝是不建议验证的
		/*$authStatus = $this->bindbankModel -> isBindByIdentity($postData['identitytype'],$postData['identityid']);
		if(!$authStatus){
			return $this->resp(2637,"没有找到该用户的绑定关系");
		}*/
		
		// 修改实际回调地址
		$ypData = $postData;
		$ypData['orderid'] = $aid_orderid;
		$ypData['identityid'] = Func::toYeepayCode($ypData['identityid'],$aid);
		$ypData['callbackurl'] =  Yii::$app->params['tztpaycallbackurl'];
    	$ybResult = $this->yeepay -> directbindpay($ypData);
    	// @todo 
		/*
		$ybResult = [
		  'orderid' => $postData['orderid'],
		  'yborderid' => "123432142134",
		  'amount' => $postData['amount'],
		];
		*/
		Logger::dayLog(
			'yeepaytzt/directbindpay',
			'提交数据',$ypData,
			'易宝结果',$ybResult
		);
		
    	//6. 保存结果信息
    	$orderModel -> modify_time = time();
		$isError = is_array($ybResult) && $ybResult['error_code'];
    	if( $isError ){
    		// 失败时处理逻辑
    		$orderModel -> error_code = $ybResult['error_code'];
    		$orderModel -> error_msg = $ybResult['error_msg'];
			$orderModel -> pay_status = YpTztOrder::STATUS_REQNO;//请求失败
    		$res = $orderModel -> save();
		}else{
			// 成功时处理逻辑
			$orderModel -> pay_status = YpTztOrder::STATUS_REQOK;//请求成功
			$orderModel -> yborderid = $ybResult['yborderid'];//请求成功
			$res = $orderModel -> save();
		}
		
		//7. 纪录数据库错误日志
		if( !$res ){
			$errors = $orderModel->errors;
			Logger::dayLog(
				'yeepaytzt/error',
				'actionDirectbindpay',
				'保存到db失败', 
				'保存数据', $orderModel->attributes,
				'错误原因', $errors
			);
		}
		
		//8. 返回客户端失败结果
		if( $isError ){
			return $this-> parseData($ybResult);
		}
		
		$r = $orderModel -> upPayorderStatus();

		//9. 返回客户端结果
		$resData = [
		  'app_id' => $aid,
		  'status' => YpTztOrder::STATUS_DOING, // 直接支付返回的都是进行中
		  'orderid' => $orderid,
		  'yborderid' => $ybResult['yborderid'],
		  'amount' => $ybResult['amount'],
		  'pay_type' => Payorder::PAY_TZT,
		];
		return $this->resp(0, $resData);
	}

	
	/**
	 * 查询订单支付结果
	 * 错误码 2650 - 2670
	 * 查询状态 0:支付失败; 1:成功; 2:未处理; 3:处理中; 4:已撤消;
	 * 
	 * 对应本表状态：基本上是查询接口返回状态码+1
	 * 支付状态 12:支付失败; 2:成功; 3:未处理; 4:处理中; 5:已撤消;  
	 * 请求状态 1:请求成功 11请求失败	    
	 */
	public function getorder(){
		//1. 数据检测
		$postData = $this->reqData;
		if( !is_array($postData) || empty($postData) ){
			return $this->resp( 2651, '没有提交数据！' );
		}
		$orderid = $postData['orderid'];
		if( empty($orderid) ){
			return $this->resp( 2652, '订单号orderid不能为空！' );
		}
		$aid = $this->appData['id'];
		

		//2. 确认是否存在该订单
		$orderModel = $this->orderModel -> getByOrder($orderid, $aid);
		if( !$orderModel ){
			return $this->resp( 2653, '没有找到该订单' );
		}

		//4. 订单状态是否已经完成
		if( $orderModel -> pay_status ==  YpTztOrder::STATUS_PAYOK  ){
			$r = $orderModel -> upPayorderStatus();
			return $this->resp( 0, [
				'pay_type' => Payorder::PAY_TZT,   // 一键支付
				'status' => $orderModel->pay_status,
				'orderid'=> $orderModel->orderid,
				'yborderid' => $orderModel->yborderid,
				'amount'    => $orderModel->amount,
			]);
		}
		
		//5. 检测请求是否失败
		if( YpTztOrder::STATUS_PAYFAIL == $orderModel -> pay_status ){
			return $this->resp( 2654, '该订单没有发送成功，请尝试发起新订单!' );
		}
		
		//6. 订单状态不是请求成功时
		/*if( YpTztOrder::STATUS_REQOK != $orderModel -> pay_status ){
			return $this->resp( 2655, '订单状态错误，无法查询!' );
		}*/
		
		//7 pay_status=1,即请求成功，那么这时可获取结果
		$yporderid = Func::toYeepayCode($orderid, $aid);
		$ybResult = $this->yeepay -> queryorder($yporderid);
		//@todo
		/*
		$ybResult = array (
		  'amount' => 2,
		  'bank' => '招商银行',
		  'bankcardtype' => 1,
		  'bankcode' => 'CMBCHINA',
		  'bindid' => '10649796',
		  'bindvalidthru' => 1478169903,
		  'closetime' => 1446633903,
		  'identityid' => '1_132',
		  'identitytype' => 2,
		  'lastno' => '7653',
		  'merchantaccount' => '10012471228',
		  'orderid' => '1_100',
		  'status' => 1,
		  'yborderid' => '411511046749871448',
		);
		*/
		
    	//8. 保存结果信息
    	$orderModel -> modify_time = time();
		$isError = is_array($ybResult) && $ybResult['error_code'];
		$resdb = false;
    	if( $isError ){
    		// 失败时处理逻辑
    		$orderModel -> error_code = $ybResult['error_code'];
    		$orderModel -> error_msg = $ybResult['error_msg'];
		
    		$resdb = $orderModel -> save();
			
		}else{
			// 请求成功时处理逻辑
			// 转换成投资通需要的状态
			$pay_status = $orderModel-> syncStatus($ybResult['status']);
			$orderModel -> pay_status = $pay_status;
			$orderModel -> closetime = $ybResult['closetime'];
			$orderModel -> yborderid = $ybResult['yborderid'];
			$resdb = $orderModel -> save();
			
		}
		
		//7. 纪录数据库错误日志
		if( !$resdb ){
			$errors = $orderModel->errors;
			Logger::dayLog(
				'yeepaytzt/error',
				'actionDirectbindpay',
				'保存到db失败', 
				'保存数据', $orderModel->attributes,
				'错误原因', $errors
			);
		}else{
			//8. 保存数据到总订单表状态
			$r = $orderModel -> upPayorderStatus();
		}
		
		//8. 返回客户端结果
		if( $isError ){
			return $this-> parseData($ybResult);
		}else{
			$resData = [
			  'pay_type' => Payorder::PAY_TZT,
			  'status'   => $orderModel -> getPayorderStatus( $orderModel -> pay_status ),
			  'orderid'  => $orderModel -> orderid,
			  'yborderid'=> $orderModel -> yborderid,
			  'amount'   => $orderModel -> amount,
			];
			return $this->resp(0, $resData);
		}
	}

	/**
	 * 银行卡号查询
	 * 错误码 2670 - 2680
	 */
	public function bankcardcheck(){
		$cardno = $this->reqData['cardno'];
		if( empty($cardno) ){
			return $this->resp( 2671, 'cardno不能为空！' );
		}
    	$result = $this->yeepay -> bankcardCheck($cardno);
		
		return $this->parseData($result);
	}
	//****************************易宝api接口使用**************************/
}
