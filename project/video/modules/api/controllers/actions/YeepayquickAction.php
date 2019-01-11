<?php
/**
 * 易宝一键支付接口
 * 内部错误码范围2000-2100
 */
namespace app\modules\api\controllers\actions;

use Yii;
use app\modules\api\common\ApiController;
use app\common\Func;
use app\modules\api\controllers\actions;  

use app\modules\api\common\yeepay\YeepayQuick;

use app\models\YpQuickOrder;
use app\models\Payorder;


use app\common\Logger;

class YeepayquickAction extends BaseAction
{
	private $yeepayQuick;
	
	public function init(){
		parent::init();
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		$this->yeepayQuick = new YeepayQuick($env);
	}

	/**
	 * 获取提交的数据
	 * @param array $reqData 
	 * @param int $aid
	 * 
	 * @return array
	 */
	private function getPostData($reqData){
		return [
			'aid' => $reqData['aid'],
			'orderid' 	=> (string)$reqData['orderid'],		   //客户订单号   √   string  商户生成的唯一订单号，最长50位
			'identityid'	=> (string)$reqData['identityid'], //用户标识    √   string  最长50位，商户生成的用户账号唯一标识
			'identitytype'	=> 2,	//用户标识类型  √   int     详见用户标识类型码表

			'cardno'	=> (string)$reqData['cardno'],		//银行卡序列号   在进行网页支付请求的时候，如果传此参数会把银行卡号直接在银行信息界面显示卡号，注意：P2P商户此参数须必填
			'idcardtype'=> '01',	//证件类型      01：身份证，注意：证件类型和证件号必须同时为空或者同时不为空
			'idcard'	=> (string)$reqData['idcard'],		//证件号     注意：P2P商户此参数须必填
			'owner'		=> (string)$reqData['username'],				//持卡人姓名      注意：P2P商户此参数须必填

			'productcatalog'=> (string)$reqData['productcatalog'],  //商品类别码   √   string  详见商品类别码表
			'productname'	=> (string)$reqData['productname'],		//商品名称    √   string  最长50位，出于风控考虑，请按下面的格式传递值：'应用商品名称，如“诛仙-3阶成品天琊”，此商品名在发送短信校验的时候会发给用户，所以描述内容不要加在此参数中，以提高用户的体验度。
			'productdesc'	=> (string)$reqData['productdesc'],		//商品描述     最长200位
			'orderexpdate'	=> intval($reqData['orderexpdate']),	//订单有效期时间       int     以分为单位
			'amount' 	=> intval($reqData['amount']), //交易金额    √   int     以"分"为单位的整型，必须大于零

			'userip' => (string)$reqData['userip'],	             //用户IP    √   string  用户支付时使用的网络终端IP
			'callbackurl'	=> (string)$reqData['callbackurl'],  //商户后台系统的回调地址       string  用来通知商户支付结果，前后台回调地址的回调内容相同
			'fcallbackurl'	=> (string)$reqData['callbackurl'],  //商户前台系统提供的回调地址     string  '用来通知商户支付结果，前后台回调地址的回调内容相同。用户在网页支付成功页面，点击“返回商户”时的回调地址

			'transtime' => time(),	//交易时间    √   int     时间戳，例如：1361324896，精确到秒
			'currency' 	=> 156,	    //交易币种      int     默认156人民币(当前仅支持人民币)
			
			'terminaltype'	=> 3,	//终端类型    √   int     0、IMEI；1、MAC；2、UUID；3、other
			'terminalid'	=> '05-16-DC-59-C2-14',		//终端ID    √ string  
			'userua' => '',		   //终端UA    √   string  用户使用的移动终端的UA信息
			'version'	=> 0,	   //网页收银台版本        int     商户可以使用此参数定制调用的网页收银台版本，目前只支持wap版本（参数传值“0”或不传值）
			'paytypes'	=> '1|2',  //支付方式      string  格式：1|2|3|41- 借记卡支付；2- 信用卡支付；3- 手机充值卡支付；4- 游戏点卡支付注：'该参数若不传此参数，则默认选择运营后台为该商户开通的支付方式。
		];
	}
	/**
	 * 获取请求链接地址
	 * 错误码 2000-2020
	 */
	public function payrequest(){
		//1  基本参数检验
		$this->reqData['aid'] = $this->appData['id'];
		
		$orderid  = $this->reqData['orderid'];
		if(!$orderid){
			return $this->resp(2001, "订单号不可为空");
		}
		$identityid = $this->reqData['identityid'];
		if(!$identityid){
			return $this->resp(2002, "identityid不可为空");
		}
		
		//2  保存到统一订单表纪录中
		/*$payData = $this->reqData;
		$payData['pay_type'] = Payorder::PAY_QUICK;
		
		$payOrderModel = new Payorder();
		$result = $payOrderModel -> saveOrder($payData);
		if(!$result){
			Logger::dayLog(
				'yeepayquick/error',
				'actionPayrequest',
				'Payorder 数据保存失败', 
				'提交数据', $payData,
				'易宝返回', $payOrderModel->errinfo
			);
			return $this->resp(2003, "订单保存失败");
		}*/
		
		//3 组合一键支付的数据形式
		$postData = $this->getPostData($this->reqData);
		$ypOrderid = Func::toYeepayCode($postData['orderid'] , $postData['aid']);
		
		//3  保存到一键支付数据表
		$quickData = $postData;
		$quickData['aid_orderid'] = $ypOrderid;
		$orderModel = new YpQuickOrder();
		$result = $orderModel -> saveOrder($quickData);
		if(!$result){
			Logger::dayLog(
				'yeepayquick/error',
				'actionPayrequest',
				'YpQuickOrder 数据保存失败', 
				'提交数据', $quickData,
				'错误原因', $orderModel->errinfo
			);
			return $this->resp(2004, $orderModel->errinfo);
		}
		
		//4  请求易宝接口:
		$ypData = $postData;
		$ypData['orderid'] = $ypOrderid;
		$ypData['identityid'] = Func::toYeepayCode($ypData['identityid'], $ypData['aid']);
		$ypData['callbackurl']  = Yii::$app->params['quickcallbackurl'];
		$ypData['fcallbackurl'] = Yii::$app->params['quickcallbackurl'];
    	$result = $this->yeepayQuick -> payRequest($ypData);

		//4.1 无响应时
		if( empty($result) ){
			Logger::dayLog(
				'yeepayquick/error',
				'actionPayrequest',
				'易宝无响应', 
				'提交数据', $postData,
				'易宝返回', $result
			);
			
			$orderModel-> error_code = 2003;
			$orderModel-> error_msg = '支付无响应';
			$r = $orderModel -> save();
			return $this->parseData($result);	
		}
		
		//4.2  有错误时
		if( is_array($result) && $result['error_code']){
			Logger::dayLog(
				'yeepayquick/error',
				'actionPayrequest',
				'易宝错误', 
				'提交数据', $postData,
				'错误原因', $result
			);
			
			$orderModel -> error_code= $result['error_code'];
			$orderModel -> error_msg = $result['error_msg'];
			$r = $orderModel -> save();
			return $this->parseData($result);	
		}
		
		//5  正确时
		$orderModel -> yeepay_url = $result;
		$r = $orderModel -> save();
    	
		return $this->parseData([
			'url'      => $result,
			'pay_type' => Payorder::PAY_QUICK,   // 一键支付
			'status'   => Payorder::STATUS_INIT, // 未处理订单
			'orderid'  => $orderid,
		]);	
	}
	/**
	 * 错误码,2020-2030
	 */
	public function getorder(){
		//1  参数
		$orderid   = $this->reqData['orderid'];
		$yborderid = $this->reqData['yborderid'];
		if( empty($orderid) ){
			return $this->resp( 2021, '订单不能为空！' );
		}
		$aid = $this->appData['id'];
		//@todo
		//$aid = 4;
		//$orderid = 'R20160513114716658qpZXb';

		//3 分表中是否存在
		$orderM = YpQuickOrder::model()->getByOrder($orderid, $aid);
		if(!$orderM){
			return $this->resp(2022, "未找到该订单");
		}
		
		//此处若已经支付成功，则无需要再查询易宝
		if($orderM -> pay_status == YpQuickOrder::STATUS_PAYOK){
			// 保存数据到总订单表状态
			$r = $orderM -> upPayorderStatus();
			return $this->resp(0, [
				'pay_type' => Payorder::PAY_QUICK,   // 一键支付
				'status' => YpQuickOrder::STATUS_PAYOK,
				'orderid'=> $orderM -> orderid,
				'yborderid' => $orderM->yborderid,
				'amount'    => $orderM->amount,
			]);
		}
		
		
		// 调用易宝接口
    		$ybResult = $this->yeepayQuick -> getOrder($orderM->aid_orderid,   $orderM->yborderid);
		// @todo 
		/*
		$ybResult = array (
		  'amount' => 2,
		  'bank' => '招商银行',
		  'bankcardtype' => 1,
		  'bankcode' => 'CMBCHINA',
		  'closetime' => 1446805499,
		  'currency' => 156,
		  'merchantaccount' => '10012537679',
		  'orderid' => '1_1446805421',
		  'ordertime' => 1446805459,
		  'productcatalog' => 7,
		  'productdesc' => '钻石',
		  'productname' => '诛仙-3 阶成品天琊',
		  'refundtotal' => 0,
		  'sourceamount' => 2,
		  'sourcefee' => 0,
		  'status' => 1,
		  'targetamount' => 2,
		  'targetfee' => 0,
		  'type' => 1,
		  'yborderid' => '411511066625995758',
		);*/
		
		$isError = is_array($ybResult) && $ybResult['error_code'];
		$resdb = false;
    	if( $isError ){
    			if($ybResult['error_code'] == '2000'){
    				// 超时需特殊处理
    				return $this->resp(2000, $ybResult['error_msg']);
    			}
	    		// 失败时处理逻辑
	    		$orderM -> error_code = $ybResult['error_code'];
	    		$orderM -> error_msg  = $ybResult['error_msg'];
			$orderM -> pay_status = YpQuickOrder::STATUS_PAYFAIL;//支付失败
	    		$resdb = $orderM -> save();
			
		}else{
			// 处理逻辑
			$orderM -> pay_status = $orderM -> syncStatus($ybResult['status']);
			$orderM -> yborderid = $ybResult['yborderid'];
			$orderM -> bankcardtype = $ybResult['bankcardtype'];
			$orderM -> bankcode = $ybResult['bankcode'];
			$resdb = $orderM -> save();
			//print_r($orderM);exit;
		}
		
		//7. 纪录数据库错误日志
		if( !$resdb ){
			$errors = $orderM->errors;
			Logger::dayLog(
				'yeepayquick/error',
				'actionGetorder',
				'保存到db失败', 
				'保存数据', $errors->attributes,
				'错误原因', $errors
			);
		}else{
			//8. 保存数据到总订单表状态
			$r = $orderM -> upPayorderStatus();
		}
		
		//9. 返回客户端失败结果
		if( $isError ){
			return $this-> parseData($ybResult);
		}
		
		//10. 返回客户端结果
		return $this->resp(0, [
			'pay_type' => Payorder::PAY_QUICK,   // 一键支付
			'status' => $orderM -> pay_status,
			'orderid'=> $orderM -> orderid,
			'yborderid' => $orderM->yborderid,
			'amount'    => $orderM->amount,
		]);
	}
}