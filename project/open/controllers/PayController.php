<?php
/**
 * 易宝一键支付回调接口 内部错误码范围2800-2899
 * 易宝投资通回调接口 内部错误码范围2900-2999
 */
namespace app\controllers;

use Yii;
use app\controllers\BaseController;
use app\common\Func;
use app\common\Crypt3Des;

use app\models\YpBindbank;
use app\models\BindBank;
use app\models\Payorder;
use app\models\chanpay\ChanpayQuickOrder;
use app\models\chanpay\ChanpayClientNotify;
use app\modules\api\common\chanpay\ChanpayQuick;
use app\models\YpQuickOrder;
use app\models\YpTztOrder;
use app\models\App;


use app\common\Logger;

class PayController extends BaseController
{
	/**
	 * payOrderModel 数据处理类 
	 */
	private $payOrderModel;
	private $chanpay;
	
	public function init(){
		parent::init();
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		$this->chanpay = new ChanpayQuick($env);
	}
	
    public function actionIndex()
    {
    	
    }
	/**
	 * 显示结果信息
	 * @param $res_code 错误码0 正确  | >0错误
	 * @param $res_data      结果   | 错误原因
	 */
	protected function showMessage($res_code,$res_data,$type='json', $redirect=null){
		switch($type){
			case 'json':
				return json_encode([
					'res_code' => $res_code,
					'res_data' => $res_data,
				]);
				break;
			default:
				return $this->render('showmessage',[
					'res_code' => $res_code,
					'res_data' => $res_data,
				]);
				break;
		}
	}
	
	/**
	 * 获取订单
	 */
	private function getPayOrder($id){
		if( empty($id) ){
			return $this->returnError(null,"订单号不存在");
		}
		$payOrderModel = Payorder::model() -> getById($id);
		if( !$payOrderModel ){
			return $this->returnError(null,"未找到订单信息");
		}
		if($payOrderModel->status == Payorder::STATUS_PAYOK){
			return $this->returnError(null,"此订单已经完成，不必重复提交");
		}
		return $payOrderModel;
	}
	/**
	 * 显示易宝请求绑卡链接地址
	 * xhhorderid
	 */
	public function actionPayurl(){
		//1 验证参数是否正确 
		$cryid = $this->get('xhhorderid');
		$xhhorderid  = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
		
		//2  解析数据
		if( !isset($xhhorderid) || !$xhhorderid ){
			return $this->showMessage(2111,"订单不合法或信息不完整",'');
		}
		
		//3  获取是否存在该订单
		$payOrderModel = $this->getPayOrder($xhhorderid);
		if( !$payOrderModel ){
			return $this->showMessage(2112,$this->errinfo,'');
		}
		
		//4  获取应用信息
		$appData = App::model()->getById($payOrderModel->aid);
		if( empty($appData) ){
			return $this->showMessage(2113,"应用不存在，请求失效",'');
		}
		
		//5 渲染输出
		$this->layout=false;
		return $this->render('payurl',[
			'payOrderModel' => $payOrderModel,
			'xhhorderid' => $cryid,
		]);
	}
	
	/**
	 * 显示易宝请求绑卡链接地址
	 * xhhorderid
	 */
	public function actionChanpayurl(){
		//1 验证参数是否正确
		$cryid = $this->get('xhhorderid');
		$xhhorderid  = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
	
		//2  解析数据
		if( !isset($xhhorderid) || !$xhhorderid ){
			return $this->showMessage(2111,"订单不合法或信息不完整",'');
		}
	
		//3  获取是否存在该订单
		$payOrderModel = $this->getPayOrder($xhhorderid);
		if( !$payOrderModel ){
			return $this->showMessage(2112,$this->errinfo,'');
		}
	
		//4  获取应用信息
		$appData = App::model()->getById($payOrderModel->aid);
		if( empty($appData) ){
			return $this->showMessage(2113,"应用不存在，请求失效",'');
		}
	
		//5 查询银行卡的类型
		$bankInfo = BindBank::find()->where(['cardno'=>$payOrderModel->cardno])->one();
		
		//5 渲染输出
		$this->layout=false;
		return $this->render('chanpayurl',[
				'payOrderModel' => $payOrderModel,
				'xhhorderid' => $cryid,
				'bankInfo' => $bankInfo,
				]);
	}
	/**
	 * 判断 是绑定还是 支付
	 * 绑定：自己发短信
	 * 未绑定：请求绑定，易宝返回验证码
	 */
	public function actionGetsmscode(){
		//1 验证参数是否正确 
		$cryid = $this->post('xhhorderid');
		$xhhorderid  = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
		
		if( !isset($xhhorderid) || !$xhhorderid ){
			return $this->showMessage(2120,"信息不完整");
		}
		
		//2 获取是否存在该订单
		$payOrderModel = $this->getPayOrder($xhhorderid);
		if( !$payOrderModel ){
			return $this->showMessage(2121,$this->errinfo);
		}
		
		if( $payOrderModel->status ==  Payorder::STATUS_NOBIND ){
			return $this->requestBind($payOrderModel);
		}elseif( $payOrderModel->status ==  Payorder::STATUS_BIND ){
			return $this->requestSms($payOrderModel);
		}else{
			return $this->showMessage(2122,"此订单状态错误!无法完成操作");
		}
		
		/**
		 * 返回结果格式
		 * [
		 * 	xhhorderid,
		 *  nexturl,
		 *  requestid[可选]
		 * ]
		 */
	}
	/**
	 * 绑定：自己发短信
	 */
	public function actionGetchanpaysmscode(){
		//1 验证参数是否正确
		$cryid = $this->post('xhhorderid');
		$xhhorderid  = Crypt3Des::decrypt($cryid, Yii::$app->params['trideskey']);
	
		if( !isset($xhhorderid) || !$xhhorderid ){
			return $this->showMessage(2120,"信息不完整");
		}
	
		//2 获取是否存在该订单
		$payOrderModel = $this->getPayOrder($xhhorderid);
		if( !$payOrderModel ){
			return $this->showMessage(2121,$this->errinfo);
		}
	
		if( $payOrderModel->status ==  Payorder::STATUS_NOBIND ){
			return $this->requestChanpaySms($payOrderModel);
		}else{
			return $this->showMessage(2122,"此订单状态错误!无法完成操作");
		}
	
		/**
		 * 返回结果格式
		 * [
		 * 	xhhorderid,
		 *  nexturl,
		 *  requestid[可选]
		 * ]
		 */
	}
	/**
	 * 发送短信程序
	 */
	private function requestSms($payOrderModel){
		//1 保存短信验证码
		if($payOrderModel->status != Payorder::STATUS_BIND){
			return $this->showMessage(2123,"支付的银行卡必须是绑定的");
		}
		$smscode = rand(100000,999999);
		$payOrderModel -> smscode = (string)$smscode;
		$res = $payOrderModel -> save();
		if(!$res){
			Logger::dayLog(
				'pay/requestsms',
				'error','短信保存失败',
				'smscode',$smscode,
				'错误原因',$payOrderModel->errors
			);
		}
		
		//2 发送短信
		$res = YpBindbank::model() -> sendSms(
									$payOrderModel -> phone,
									$smscode,
									'MERCHANT',
									$payOrderModel -> amount,
									$payOrderModel -> aid
		);
		if(!$res){
			return $this->showMessage(2122,"系统故障!请稍后重试或您联系客服");
		}
		
		//3 返回结果
		return $this->showMessage(0,[
			'isbind'  => false,
			'nexturl' => Yii::$app->request->hostInfo.'/pay/directpay',
		]);
	}
	/**
	 * 发送短信程序
	 */
	private function requestChanpaySms($payOrderModel){
		//1 保存短信验证码
		$smscode = rand(100000,999999);
		$payOrderModel -> smscode = (string)$smscode;
		$res = $payOrderModel -> save();
		if(!$res){
			Logger::dayLog(
			'pay/requestsms',
			'error','短信保存失败',
			'smscode',$smscode,
			'错误原因',$payOrderModel->errors
			);
		}
	
		//2 发送短信
		$res = YpBindbank::model() -> sendSms(
				$payOrderModel -> phone,
				$smscode,
				'MERCHANT',
				$payOrderModel -> amount,
				$payOrderModel -> aid
		);
		if(!$res){
			return $this->showMessage(2122,"系统故障!请稍后重试或您联系客服");
		}
	
		//3 返回结果
		return $this->showMessage(0,[
				'isbind'  => false,
				'nexturl' => Yii::$app->request->hostInfo.'/pay/directchanpay',
				]);
	}
	/**
	 * 易宝支付绑卡操作
	 * json 结果
	 */
	private function requestBind($payOrderModel){
		//1 验证参数是否正确 
		// 检测是不是是未绑定状态，否则不允许绑定
		if($payOrderModel->status != Payorder::STATUS_NOBIND){
			return $this->showMessage(null,"已经完成绑定，无法重复操作");
		}
		
		//4  获取应用信息
		$appData = App::model()->getById($payOrderModel->aid);
		if( empty($appData) ){
			return $this->showMessage(2122,"请求不合法");
		}
		
		//5 组合绑定数据
		$userip = Func::get_client_ip();
		if( !$userip ){
			$userip = '127.0.0.1';
		}
		$reqData = [
			'payorderid'    => $payOrderModel->id,
			'identityid' 	=> $payOrderModel->identityid,//用户标识√string最长50位，商户生成的用户唯一标识
			'requestid' 	=> "payroute_".time().'_'.rand(10000,99999),//'xhh_13581524051_8', //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
			
			'cardno' 	    => $payOrderModel->cardno,    //银行卡号√string
			'idcardno' 		=> $payOrderModel->idcard,  //证件号√string
			'username' 		=> $payOrderModel->username,  //持卡人姓名√string
			'phone' 		=> $payOrderModel->phone,     //银行预留手机号√string
			
			'userip'		=> $userip,
    	];
		
		//6 检测是否已经绑定过
		// @todo ....
		
		//7  调用绑定接口
		$action = Yii::createObject([
			    'class' => '\app\modules\api\controllers\actions\YeepaytztAction',
		    	'reqData' => $reqData,
		    	'appData' => $appData,
		    	'reqType' => 'return',
			], 
			['invokebindbankcard', $this]);
			
		$result = $action->runWithParams([]);
		
		// @todo 
		/*
		$res_data = [
			'res_code' => 0,
			'res_data' => [
				'app_id' => '2810335722015',
				'requestid' => 'payroute_1448094096_96822',
				'codesender' => 'MERCHANT',
				'smscode' => '304185',
			]
		];
		*/
		
		//8  解析响应结果 
		if( $result['res_code'] ){
			return $this->showMessage($result['res_code'], $result['res_data']);
		}
		
		//7  发送短信成功
		$temp = $result['res_data'];
		$codesender = $temp['codesender'];
		$res_data = [
			'requestid'  => Crypt3Des::encrypt($temp['requestid'], Yii::$app->params['trideskey']),
			'isbind' => true,
			'nexturl' => Yii::$app->request->hostInfo.'/pay/comfirmbindpay',
		];
		if( $codesender == 'YEEPAY' ){
			// 易宝请求
			return $this->showMessage(0,$res_data);
			
		}elseif($codesender == 'MERCHANT'){
			// 商家发送
			return $this->showMessage(0,$res_data);
		}if($codesender == 'BANK'){
			// 银行发送，理论上跟易宝一样
			return $this->showMessage(0,$res_data);
			
		}else{
			// 非易宝发送验证码时
			return $this->showMessage(2123,"系统暂不支持这种支付方式");
		}
	}
	/**
	 * 输入验证码确定并支付
	 * requestid
	 * xhhorderid
	 * 
	 * html页面
	 */
	public function actionComfirmbindpay(){
		//1  参数验证
		$xhhorderid = $this->post('xhhorderid');
		$requestid  = $this->post('requestid');
		$validatecode = $this->post('validatecode');
		
		$xhhorderid = Crypt3Des::decrypt($xhhorderid, Yii::$app->params['trideskey']);
		$requestid  = Crypt3Des::decrypt($requestid, Yii::$app->params['trideskey']);
		
		// 请求绑定
		// @todo
		/*$requestid = 'payroute_1448100640_74907';
		$validatecode = 704427;
		*/

		if( empty($xhhorderid) ){
			return $this->showMessage(1,"$xhhorderid未找到");
		}
		if( empty($requestid) ){
			return $this->showMessage(1,"$requestid未找到");
		}
		if( empty($validatecode) ){
			return $this->showMessage(1,"smscode未找到");
		}
		
		
		//2  获取是否存在该订单
		$payOrderModel = $this->getPayOrder($xhhorderid);
		if( !$payOrderModel ){
			return $this->showMessage(1,$this->errinfo);
		}
		
		//3 检测是不是是未绑定状态，否则不允许绑定
		if($payOrderModel->status != Payorder::STATUS_NOBIND){
			return $this->showMessage(null,"支付订单状态必须是未绑定，无法操作");
		}
		
		//3 获取应用信息
		$appData = App::model()->getById($payOrderModel->aid);
		if( empty($appData) ){
			return $this->showMessage(1,"请求不合法");
		}
		
		//7  调用绑定接口
		$reqData = ['requestid'=> $requestid, 'validatecode'=> $validatecode,];
		$result = $this->confirmbindbankcard($appData, $reqData);
    	// @todo 
    	/*
    	$res_data = array (
		  'bankcode' => 'CMBCHINA',
		  'card_last' => '7653',
		  'card_top' => '621485',
		  'merchantaccount' => '10012471228',
		  'requestid' => $requestid,
		);
		*/

		//8  解析响应结果 
		if( $result['res_code'] ){
			return $this->showMessage($result['res_code'],$result['res_data']);
		}
		
		//9  组合支付操作
		$result = $this->directpay($appData, $payOrderModel);
    	// @todo 
		/*
		$res_data = [
		  'orderid' => $postData['orderid'],
		  'yborderid' => "123432142134",
		  'amount' => $postData['amount'],
		];
		*/
		if( $result['res_code'] ){
			return $this->showMessage($result['res_code'], $result['res_data']);
		}
		
		//这里其实可以直接返回的, 因为查询接口无法实时查询到
		$url = $this->encryptData($payOrderModel->callbackurl, $payOrderModel->aid, $result['res_data']);
		return $this->showMessage(0,[
			'callbackurl'   => $url,
		]);
	}
	/**
	 * 输入验证码确定并支付
	 * @param xhhorderid
	 * @param validatecode
	 * 
	 * 成功回调客户端
	 */
	public function actionDirectpay(){
		//1  参数验证
		$xhhorderid = $this->post('xhhorderid');
		$validatecode = $this->post('validatecode');
		$xhhorderid = Crypt3Des::decrypt($xhhorderid, Yii::$app->params['trideskey']);
		if( empty($xhhorderid) ){
			return $this->showMessage(1,"$xhhorderid未找到");
		}

		if( empty($validatecode) ){
			return $this->showMessage(1,"smscode未找到");
		}
		
		
		//2  获取是否存在该订单
		$payOrderModel = $this->getPayOrder($xhhorderid);
		if( !$payOrderModel ){
			return $this->showMessage(1,$this->errinfo);
		}
		
		//3 检测是不是是未绑定状态，否则不允许绑定
		if($payOrderModel->status != Payorder::STATUS_BIND){
			return $this->showMessage(null,"此卡未绑定，无法操作");
		}
				
		//4  短信验证码检测
		if( $validatecode != $payOrderModel->smscode ){
			return $this->showMessage(1,"验证码错误");
		}
		
		
		//5  获取应用信息
		$appData = App::model()->getById($payOrderModel->aid);
		if( empty($appData) ){
			return $this->showMessage(1,"请求不合法");
		}
		
		//7  组合支付操作
		$result = $this->directpay($appData, $payOrderModel);
    	// @todo 
		/*
		$ybResult = [
		  'orderid' => $postData['orderid'],
		  'yborderid' => "123432142134",
		  'amount' => $postData['amount'],
		];
		*/
		if( $result['res_code'] ){
			return $this->showMessage($result['res_code'], $result['res_data']);
		}
		
		//这里其实可以直接返回的, 因为查询接口无法实时查询到
		$url = $this->encryptData($payOrderModel->callbackurl, $payOrderModel->aid, $result['res_data']);
		return $this->showMessage(0,[
			'callbackurl'   => $url,
		]);
	}
	/**
	 * 输入验证码确定并支付
	 * @param xhhorderid
	 * @param validatecode
	 *
	 * 成功回调客户端
	 */
	public function actionDirectchanpay(){
		//1  参数验证
		$xhhorderid = $this->post('xhhorderid');
		$validatecode = $this->post('validatecode');
		$validate = $this->post('validate');
		$cvv2     = $this->post('cvv2');
		$xhhorderid = Crypt3Des::decrypt($xhhorderid, Yii::$app->params['trideskey']);
		if( empty($xhhorderid) ){
			return $this->showMessage(1,"$xhhorderid未找到");
		}
	
		if( empty($validatecode) ){
			return $this->showMessage(1,"smscode未找到");
		}
	
	
		//2  获取是否存在该订单
		$payOrderModel = $this->getPayOrder($xhhorderid);
		if( !$payOrderModel ){
			return $this->showMessage(1,$this->errinfo);
		}
	
		//4  短信验证码检测
		if( $validatecode != $payOrderModel->smscode ){
			return $this->showMessage(1,"验证码错误");
		}
	
	
		//5  获取应用信息
		$appData = App::model()->getById($payOrderModel->aid);
		if( empty($appData) ){
			return $this->showMessage(1,"请求不合法");
		}
		
		//6 获取银行卡信息
		$bankinfo = BindBank::find()->where(['cardno' => $payOrderModel->cardno])->one();
		if( empty($bankinfo) ){
			return $this->showMessage(1,"银行卡信息不存在");
		}
		
		if(!empty($validate) && !empty($cvv2)){
			$bankinfo->validate = Crypt3Des::encrypt($validate,chanpay_3des_key);
			$bankinfo->cvv2 = Crypt3Des::encrypt($cvv2,chanpay_3des_key);
			
			$bankinfo->save();
			$bankinfo->refresh();
		}
		
		//7  组合支付操作
		$result = $this->directchanpay($appData, $payOrderModel, $bankinfo);
		// @todo
		/*
			$ybResult = [
			  'orderid' => $postData['orderid'],
			  'yborderid' => "123432142134",
			  'amount' => $postData['amount'],
					];
		*/
		if( $result['res_code']){
			return $this->showMessage($result['res_code'], $result['res_data']);
		}
	
		//这里其实可以直接返回的, 因为查询接口无法实时查询到
		$url = $this->encryptData($payOrderModel->callbackurl, $payOrderModel->aid, $result['res_data']);
		return $this->showMessage(0,[
				'callbackurl'   => $url,
				]);
	}
	/**
	 * 确定绑卡接口
	 */
	private function confirmbindbankcard($appData,$reqData){
		$action = Yii::createObject([
			    'class' => '\app\modules\api\controllers\actions\YeepaytztAction',
		    	'reqData' => $reqData,
		    	'appData' => $appData,
		    	'reqType' => 'return',
			], 
			['confirmbindbankcard', $this]);
			
		return $action->runWithParams([]);
	}
	/**
	 * 直接支付接口
	 */
	private function directpay($appData,$payOrderModel){
		//1 参数组合
		$card_top = substr($payOrderModel->cardno,0,6);
		$card_last= substr($payOrderModel->cardno,-4);
		$reqData = [
            'orderid' 		=>$payOrderModel->orderid, //     商户订单号√string商户生成的唯一订单号，最长50位
            'transtime'		=>time(), //   交易时间√int时间戳，例如：1361324896，精确到秒
            'amount'		=>$payOrderModel->amount, //  交易金额√int以"分"为单位的整型
            'productname'	=>$payOrderModel->productname, //     商品名称√string最长50位
            'productdesc'	=>$payOrderModel->productdesc, //     商品描述string最长200位
            'identityid'	=>$payOrderModel->identityid, //      用户标识√string最长50位，商户生成的用户唯一标识
            'card_top'		=>$card_top,  //    卡号前6位√string
            'card_last'		=>$card_last, //   卡号后4位√string
            'orderexpdate'	=>$payOrderModel->orderexpdate, //    订单有效期int单位：分钟，例如：60，表示订单有效期为60分钟
            'callbackurl'	=>$payOrderModel->callbackurl,//     回调地址√string用来通知商户支付结果
            'userip'		=>$payOrderModel->userip, //  用户请求ip√string用户支付时使用的网络终端IP
            'source_type' => 'route',
    	];
		
		
		//2 调用支付接口
		$action = Yii::createObject([
			    'class' => '\app\modules\api\controllers\actions\YeepaytztAction',
		    	'reqData' => $reqData,
		    	'appData' => $appData,
		    	'reqType' => 'return',
			], 
			['directbindpay', $this]);
			
		return $action->runWithParams([]);
	}
	/**
	 * 畅捷直接支付接口
	 */
	private function directchanpay($appData,$payOrderModel,$bankInfo){
		//1 参数组合
		$card_type = ($bankInfo->card_type == 1) ? 'DC' : 'CC';
		
		$condition = array(
				'aid' => $appData->user_id,   //应用ID
				'orderid' => $payOrderModel->orderid,  //订单ID
				'aid_orderid' => $appData->user_id.'_'.$payOrderModel->orderid,
				'currency' => 156,
				'amount' => $payOrderModel->amount, //支付金额
				'productname' => $payOrderModel->productname,
				'payer_name' => $payOrderModel->username,
				'id_number' => $payOrderModel->idcard,
				'buyer_mobile' => $payOrderModel->phone,
				'phone_number' => $payOrderModel->phone,
				'payer_card_no' => $payOrderModel->cardno,
				'card_type' => $card_type,
				'bank_code' => $bankInfo->bank_code,
				'expiry_date' => !empty($bankInfo->validate) ? $bankInfo->validate : '0',
				'cvv2' => !empty($bankInfo->cvv2) ? $bankInfo->cvv2 : '0',
				'orderexpdate' => '60m',
				'callbackurl' => $payOrderModel->callbackurl,
				'userip' => $payOrderModel->userip,
		);
		
		$quickorderinfo = ChanpayQuickOrder::find()->where(['orderid'=>$payOrderModel->orderid])->one();
		if(!empty($quickorderinfo)){
			return $this->resp( 1001, '订单信息错误，请返回重试' );
		}
		$chanpayQuickOrder = new ChanpayQuickOrder();
		$result_order = $chanpayQuickOrder->saveOrder($condition);
		
		if($card_type == 'CC'){
			$validate = Crypt3Des::decrypt($bankInfo->validate,chanpay_3des_key);
			$cvv2 = Crypt3Des::decrypt($bankInfo->cvv2,chanpay_3des_key);
		}else{
			$validate = '';
			$cvv2 = '';
		}
		 
		$result = $this->chanpay->quickpayment($appData->user_id.'_'.$payOrderModel->orderid, $payOrderModel->amount/100, '60m', $payOrderModel->phone, $card_type, $bankInfo->bank_code, $payOrderModel->username, $payOrderModel->cardno, $payOrderModel->idcard, $payOrderModel->phone, $validate, $cvv2);
		$de_result = json_decode($result);
		if($de_result->authenticate_status != '0' || $de_result->is_success != 'T'){
			//把订单改为支付失败状态
			$result = $this->chanpaybackerror($payOrderModel->id, $payOrderModel->orderid, 'fail');
			return $result;
		}
		
		//单笔订单快捷支付API接口受理成功，下一步调用交易确认接口
		$outer_trade_no = $de_result->outer_trade_no;
		//测试环境的验证码默认为123456，上生产环境需要修改
		$code = '123456';
		$result_confirm = $this->chanpay->quickconfirm($outer_trade_no, $code);
		$de_result_confirm = json_decode($result_confirm);
		if($de_result_confirm->is_success != 'T'){
			//把订单改为支付失败状态
			$result = $this->chanpaybackerror($payOrderModel->id, $payOrderModel->orderid, 'fail');
			return $result;
		}
		
		if($de_result_confirm->trade_status == '0'){
			//支付成功
			$pay_status = 'success';
		}else if($de_result_confirm->trade_status == '2'){
			//支付失败
			$pay_status = 'fail';
		}else{
			//处理中
			$pay_status = 'init';
		}
		
		//根据支付结果修改支付订单的状态，如果是支付成功，还得修改绑卡表中的绑卡状态
		$result = $this->chanpaybackerror($payOrderModel->id, $payOrderModel->orderid, $pay_status, 2);
		if($pay_status == 'success' || $pay_status == 'fail')
		{
			//知道支付结果后向推送通知表中添加一条推送记录
			$condition_notify = array(
				'remit_id' => $result_order->id,
				'tip' => ($pay_status == 'success') ? '支付成功' : '支付失败',
				'remit_status' => ($pay_status == 'success') ? 6 : 11,
				'notify_num' => 0,
				'notify_status' => 0,
				'reason' => 'NULL'
			);
			
			$chanpaynotify = new ChanpayClientNotify();
			$result_notify = $chanpaynotify->saveNotify($condition_notify);
			
			if($bankInfo->status != 1){
				$bankInfo->status = ($pay_status == 'success') ? 1 : 2;
				$bankInfo->modify_time = date('Y-m-d H:i:s');
				$bankInfo->save();
			}
		}
		return $result;
	}
	/**
	 * 畅捷支付扣款失败
	 */
	private function chanpaybackerror($id, $orderid, $status, $type=1){
		$result_xhh = $this->modifyXhhorder($id,$status);
		if(!$result_xhh){
			return $this->resp( 1002, '系统错误，请稍后再试' );
		}
		//修改畅捷支付的订单状态
		$result_chanpay = $this->modifyChanpayorder($orderid,$status);
		if(!$result_chanpay){
			return $this->resp( 1002, '系统错误，请稍后再试' );
		}
		if($type == 1){	
			return $this->resp( 1003, '鉴权失败' );
		}else{
			$quickorderinfo = ChanpayQuickOrder::find()->where(['orderid'=>$orderid])->one();
			$resData = [
			  'pay_type' => Payorder::PAY_CHANPAY,
			  'status'   => $quickorderinfo -> pay_status,
			  'orderid'  => $orderid,
			  'amount'   => $quickorderinfo -> amount,
			];
			return $this->resp(0, $resData);
		}
	}
	/**
	 * 修改主订单的状态
	 */
	private function modifyXhhorder($orderid,$status){
		if($status == 'fail'){
			$pay_status = 11;
		}else if($status == 'success'){
			$pay_status = 2;
		}else{
			$pay_status = 0;
		}
		$orderinfo = $this->getPayOrder($orderid);
		$orderinfo->status = $pay_status;
		$orderinfo->modify_time = time();
		
		if($orderinfo->save()){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 修改畅捷子订单状态
	 */
	private function modifyChanpayorder($orderid,$status){
		if($status == 'fail'){
			$pay_status = 11;
		}else if($status == 'success'){
			$pay_status = 2;
		}else{
			$pay_status = 4;
		}
		$chanpayorderinfo = ChanpayQuickOrder::find()->where(['orderid'=>$orderid])->one();
		$chanpayorderinfo->pay_status = $pay_status;
		if($status == 'fail'){
			$chanpayorderinfo->error_msg = '鉴权失败';
		}
		$chanpayorderinfo->modify_time = date('Y-m-d H:i:s');
		$chanpayorderinfo->closetime = date('Y-m-d H:i:s');
		
		if($chanpayorderinfo->save()){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * get前台访问通知
	 * 表示易宝前台点击返回商户的链接
	 */
	private function encryptData($fcallbackurl, $aid, $responseData){
		$responseData = App::model() -> encryptData($aid, $responseData);
		// 跳转到客户端地址
		$url = $fcallbackurl.'?res_code=0&res_data='.rawurlencode($responseData);
		return $url;
	}
	
	private function resp($res_code, $res_data) {
		return ['res_code' => $res_code, 'res_data' => $res_data];
	}
}
