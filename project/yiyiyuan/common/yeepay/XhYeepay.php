<?php 
namespace app\common\yeepay;
 /*
identitytype 用户标识类型
√int0：IMEI
1：MAC地址
2：用户ID
3：用户Email
4：用户手机号
5：用户身份证号
6：用户纸质订单协议号
  * */
include __DIR__.'/yeepayMPay.php';

class XhYeepay extends \yeepayMPay{
	public function __construct(){
		$config = include __DIR__.'/config.test.php'; // @todo 先使用测试的
		parent::__construct(
			$config['merchantaccount'],
			$config['merchantPublicKey'],
			$config['merchantPrivateKey'],
			$config['yeepayPublicKey']
		);
	}
	
	
	//************************支付流程 start ****************************/
	/**
	 * 绑定卡请求
	 */
	public function invokebindbankcard($postData){
		if( !is_array( $postData ) ){
			return $this->error(1000,"提交的数据不能为空");
		}
		//$postData['idcardtype'] = '01';
		//$postData['registeridcardtype'] = '01';
		$data = array(
			'identityid' 	=> $postData['identityid'],//用户标识√string最长50位，商户生成的用户唯一标识
			'identitytype'  => intval($postData['identitytype']),      /*用户标识类型*/
			'requestid' 	=> $postData['requestid'], //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
			'cardno' 	    => $postData['cardno'],    //银行卡号√string
			'idcardtype' 	=> $postData['idcardtype'],//证件类型√string固定值:01
			'idcardno' 		=> $postData['idcardno'],  //证件号√string
			'username' 		=> $postData['username'],  //持卡人姓名√string
			'phone' 		=> $postData['phone'],     //银行预留手机号√string
			'registerphone' => $postData['registerphone'], //用户注册手机号string  用户在商户的系统注册的手机号
			'registerdate' 	=> $postData['registerdate'],  //用户注册日期string用户在商户的系统注册的日期，格式：yyyy-mm-dd hh:mm:ss精确到秒
			'registerip' 	=> $postData['registerip'],    // 用户注册ipstring
			'registeridcardtype'=> $postData['registeridcardtype'],//用户注册证件类型string固定值:01
			'registeridcardno' 	=> $postData['registeridcardno'],  //用户注册证件号 string
			'registercontact'	=> $postData['registercontact'],   //用户注册联系方式string手机号
			'os'	=> $postData['os'],    //用户使用的操作系统
			'imei'	=> $postData['imei'],  //设备唯一标识
			'userip'=> $postData['userip'],//用户请求ip√string用户支付时使用的网络终端IP
			'ua'	=> $postData['ua'],    //用户使用的浏览器信息
		);
		//print_r($data);exit;
		try{
    		return $this->post(YEEPAY_PAY_API, 'tzt/invokebindbankcard', $data);
		}catch(\yeepayMPayException $e){
			$this->loge('invokebindbankcard', $e, func_get_args());
			return $this->errore($e);
		}
		
		/*返回结果数据格式如下
		merchantaccount 商户编号string
		requestid 绑卡请求号string
		codesender 短信验证码发送方string YEEPAY：易宝发送   BANK：银行发送    MERCHANT：商户发送
		smscode 短信验证码string为商户发送短验时会返回易宝生成的验证码
		sign  签名
		*/
	}
	/**
	 * 确定绑卡接口
	 */
	public function confirmbindbankcard($requestid, $validatecode){
		$data = array(
			'requestid'   => $requestid,      //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
			'validatecode'=> $validatecode,   //短信验证码√string短信验证码6位数字
		);
		try{
    		return $this->post(YEEPAY_PAY_API, 'tzt/confirmbindbankcard', $data);
		}catch(\yeepayMPayException $e){
			$this->loge('confirmbindbankcard', $e, func_get_args());
			return $this->errore($e);
		}
		/**返回结果数据格式如下
		merchantaccount     商户编号string商户编号string
		requestid       绑卡请求号string原样返回商户所传
		bankcode    银行编码string详见银行编码列表
		card_top    卡号前6位string
		card_last   卡号后4位string
		sign            签名string
		 */
	}
	
	/**
	 * 支付请求接口
	 */
	public function payrequest($postData){
		if( !is_array( $postData ) ){
			return $this->error(1000,"提交的数据不能为空");
		}
		$data = array(
            'orderid' 		=>$postData['orderid'], //     商户订单号√string商户生成的唯一订单号，最长50位
            'transtime'		=>$postData['transtime'], //   交易时间√int时间戳，例如：1361324896，精确到秒
            'currency'		=>intval($postData['currency']), //    交易币种int默认156人民币（当前仅支持人民币）
            'amount'		=>intval($postData['amount']), //  交易金额√int以"分"为单位的整型
            'productname'	=>$postData['productname'], //     商品名称√string最长50位
            'productdesc'	=>$postData['productdesc'], //     商品描述string最长200位
            'identityid'	=>$postData['identityid'], //      用户标识√string最长50位，商户生成的用户唯一标识
            'identitytype'	=>intval($postData['identitytype']), //    
            'card_top'		=>$postData['card_top'], //    卡号前6位√string
            'card_last'		=>$postData['card_last'], //   卡号后4位√string
            'orderexpdate'	=>intval($postData['orderexpdate']), //    订单有效期int单位：分钟，例如：60，表示订单有效期为60分钟
            'callbackurl'	=>$postData['callbackurl'], //     回调地址√string用来通知商户支付结果
            'imei'			=>$postData['imei'], //        设备唯一标识string国际移动设备身份码的缩写，国际移动装备辨识码，是由15位数字组成的"电子串号"，它与每台手机一一对应
            'userip'		=>$postData['userip'], //  用户请求ip√string用户支付时使用的网络终端IP
            'ua'			=>$postData['ua'], //  用户使用的浏览器信息
		);
		try{
    		return $this->post(YEEPAY_PAY_API, 'tzt/pay/bind/request', $data);
		}catch(\yeepayMPayException $e){
			$this->loge('payrequest', $e, func_get_args());
			return $this->errore($e);
		}
		/**返回结果数据格式如下
			merchantaccount     商户账号编号string
			orderid     商户订单号string原样返回商户所传
			phone   手机号string
			smsconfirm  短信确认int 0：建议不需要进行短信校验  1：建议需要进行短信校验
			codesender   短信验证码发送方YEEPAY：易宝发送     BANK：银行发送
			sign
		 */
	}
	
	/**
	 * 请求发送短信验证码接口
	 */
	public function validatecodesend($orderid){
		if( !empty( $orderid ) ){
			return $this->error(1000,"订单号不能为空");
		}
		$data = array(
			'orderid'=> $orderid, //   客户订单号
		);
		try{
    		return $this->post(YEEPAY_PAY_API, 'tzt/pay/validatecode/send', $data);
		}catch(\yeepayMPayException $e){
			$this->loge('validatecodesend', $e, func_get_args());
			return $this->errore($e);
		}
		/**返回结果数据格式如下
			merchantaccount     商户账号编号string
			orderid     商户订单号string
			phone   手机号string
			sendtime    短信发生时间int精确到秒的时间戳，如1361324896
			sign    签名
		 */
	}	
	/**
	 * 确认短信校验码支付: 此时会发生回调操作
	 */
	public function confirmvalidatecode($orderid, $validatecode){
		$data = array(
			'orderid' => $orderid, //客户订单号
			'validatecode' => $validatecode, //    短信校验码string测试环境下不会真实发送短信验证码，默认为“123456”。
		);
		try{
    		return $this->post(YEEPAY_PAY_API, 'tzt/pay/confirm/validatecode', $data);
		}catch(\yeepayMPayException $e){
			$this->loge('confirmvalidatecode', $e, func_get_args());
			return $this->errore($e);
		}
		/**返回结果数据格式如下
			orderid     商户订单号string原样返回商户所传
			yborderid   易宝交易流水号string
			amount  交易金额int以分为单位
		 */
	}	
	/**
	 * 异步通知接口
	 *	易宝异步通知商户支付请求传过来的callbackurl地址,每2秒通知一次，共通知3次。
	 *	商户收到通知后需要回写，需要返回字符串大写的”SUCCESS”，否则会一直通知多次。
	 */
	public function payCallback($data,$encryptkey){
		$data =  $this->parseReturn($data,$encryptkey);
		//print_r($data);
	    if( $data['status'] == 1 ){
	    	return true;
	    }else{
	    	return false;
	    }
		/*返回结果数据格式如下
		$data = array(
			'orderid' => $postData['orderid'], //      商户订单号string商户生成的唯一订单号，最长50位
			'yborderid' => $postData['yborderid'], //    易宝交易流水号string
			'amount' => $postData['amount'], //   交易金额int以"分"为单位的整型
			'identityid' => $postData['identityid'], //   用户标识string最长50位，商户生成的用户唯一标识
			'card_top' => $postData['card_top'], //     卡号前6位string
			'card_last' => $postData['card_last'], //    卡号后4位string
			'status' => $postData['status'], //   支付状态 int0：失败1：成功 2：撤销
		);
		*/
	}	
		
	/**
	 * 直接支付， 不发生短信检验
	 */
	public function directbindpay($postData){
		if( !is_array( $postData ) ){
			return $this->error(1000,"提交的数据不能为空");
		}
		$data = array(
		  'orderid'		=> $postData['orderid'], //     商户订单号string原样返回商户所传商户订单号√string商户生成的唯一订单号，最长50位
		  'transtime'	=> $postData['transtime'], //   交易时间√int时间戳，例如：1361324896，精确到秒
		  'currency'	=> intval($postData['currency']), //    交易币种int默认156人民币（当前仅支持人民币）
		  'amount'		=> intval($postData['amount']), //  交易金额√int以"分"为单位的整型
		  'productname'	=> $postData['productname'], //     商品名称√string最长50位
		  'productdesc'	=> $postData['productdesc'], //     商品描述string最长200位
		  'identityid'	=> $postData['identityid'], //  用户标识√string最长50位，商户生成的用户唯一标识
		  'identitytype'=> intval($postData['identitytype']), //    用户标识类型
		  'card_top'	=> $postData['card_top'], //    卡号前6位√string
		  'card_last'	=> $postData['card_last'], //   卡号后4位√string
		  'orderexpdate'=> intval($postData['orderexpdate']), //    订单有效期int单位：分钟，例如：60，表示订单有效期为60分钟
		  'callbackurl'	=> $postData['callbackurl'], //     回调地址√string用来通知商户支付结果
		  'imei'		=> $postData['imei'], //    设备唯一标识
		  'userip'		=> $postData['userip'], // 用户请求√string用户支付时使用的网络终端IP
		  'ua'			=> $postData['ua'], //  用户使用的浏览器信息
		);
		try{
    		return $this->post(YEEPAY_PAY_API, 'tzt/directbindpay', $data);
		}catch(\yeepayMPayException $e){
			$this->loge('directbindpay', $e, func_get_args());
			return $this->errore($e);
		}
		return null;
		/**返回结果数据格式如下
			merchantaccount     商户账号编号string
			orderid     商户订单号string原样返回商户所传
			yborderid   易宝交易流水号string
			amount  交易金额int以分为单位
			sign    签名
		 */
	}

	/**
	 * 支付查询
	 */
	public function queryorder($order_id){
		try{
    		return parent::getPaymentResult($order_id);
		}catch(\yeepayMPayException $e){
			$this->loge('queryorder', $e, func_get_args());
			return $this->errore($e);
		}
		/**返回结果数据格式如下
			merchantaccount     商户账户string
			orderid     客户订单号string
			yborderid 易宝交易流水号string
			amount  支付金额int以“分”为单位的整型
			bindid  绑卡IDstring两种情况：
			bindvalidthru   绑卡有效期int最后期限，时间戳，例如：1361324896，精确到秒
			bank    银行信息string支付卡所属银行的名称
			bankcode    银行缩写string银行缩写，如ICBC
			closetime   支付时间int返回支付时间为，交易变成当前状态的时间：closetime
			bankcardtype    银行卡类型int1：储蓄卡 2：信用卡
			lastno  卡号后4位string支付卡卡号后4位
			identityid  用户标识string
			identitytype   用户标识类型int详见用户标识类型码表
			status  状态int0：失败  1：成功 2：未处理 3：处理中 4：已撤销
		 */
	}
	//************************支付流程 start ****************************/
	
	
	
	//************************解绑卡相关 ******************************/
	/**
	 * 查询绑卡信息列表，获取对应支付身份的绑卡id
	 * 
	 * @param string $identity_id
	 * @param int $identity_type
	 * @return array
	 */
	public function authbindlist($identity_type,$identity_id){
		$query = array(
			'identityid'	=>	(string)$identity_id,
			'identitytype'	=>	intval($identity_type),
		);
		try{
    		return $this->get(YEEPAY_PAY_API, 'bankcard/authbind/list', $query);
		}catch(\yeepayMPayException $e){
			$this->loge('authbindlist', $e, func_get_args());
			return $this->errore($e);
		}
		return null;
		/**返回结果数据格式如下
		merchantaccount 商户账户string 原值返回
		identityid 用户标识string 原值返回
		identitytype 用户标识类型int 详见用户标识类型码表
		cardlist 绑卡列表json
		bindid 绑卡ID string
		card_top 卡号前6 位string
		card_last 卡号后4 位string
		card_name 卡名称string 例如“中国银行信用卡“
		cardtype 借贷类型int1：储蓄卡2：信用卡
		bindvalidthru 绑卡有效期int最后期限，时间戳，例如：1361324896，精确到秒
		phone 银行预留手机号string 中间4 位屏蔽，例如“138****1234“
		bankcode 银行缩写String 银行缩写，如ICBC
		sign 签名
		 */
	}

	/**
	 * 有：银行卡信息查询
	 * 
	 * @param string $cardno
	 * @return array
	 */
	public function bankcardCheck($cardno){
		try{
    		return parent::bankcardCheck($cardno);
		}catch(\yeepayMPayException $e){
			$this->loge('bankcardCheck', $e, func_get_args());
			return $this->errore($e);
		}
		return null;
		
		/**
		 * 返回结果数据格式如下
			merchantaccount 商户账户string 原值返回
			cardno 卡号string 原值返回
			cardtype 借贷类型int1：储蓄卡2：信用卡-1 未知银行卡
			bankname 银行名称string 例如：中国工商银行
			bank_code 银行编码string 例如：中国工商银行编码“ICBC”
			isvalid 卡号是否有效int  0：无效卡号  1：有效的银行卡号（但不表示是一键支付支持的银行卡，一键支付支持的银行列表请见附录）
			sign 签名
		 */
	}
	/**
	 * 有：解除银行卡绑定
	 * 
	 * @param string $bind_id
	 * @param string $identity_id
	 * @param int $identity_type
	 * @return array
	 */
	public function unbind($bind_id,$identity_id,$identity_type){
		try{
    		return parent::unbind($bind_id,$identity_id,$identity_type);
		}catch(\yeepayMPayException $e){
			$this->loge('unbind', $e, func_get_args());
			return $this->errore($e);
		}
		return null;
		/**
		 * 返回结果
		merchantaccount 商户账户string 原值返回
		bindid 绑卡ID string 原值返回
		identityid 用户标识string 原值返回
		identitytype 用户标识类型
		int 详见用户标识类型码表
		sign 签名
		*/
	}
	//************************解绑卡相关 ******************************/

	
	
	/************************ 以下接口为通用接口，还未完善 ******************************/
	/**
	 * 有 退货/退款
	 * @param int $amount
	 * @param string $order_id
	 * @param string $origyborder_id
	 * @param int $currency
	 * @param string $cause
	 * @return mixed
	 */
	public function refund($amount,$order_id,$origyborder_id,$currency=156,$cause=''){
		try{
    		return parent::refund($amount,$order_id,$origyborder_id,$currency,$cause);
		}catch(\yeepayMPayException $e){
			$this->loge('refund', $e, func_get_args());
			return $this->errore($e);
		}
		return null;
	}
	/**
	 * 有 交易记录查询
	 * 
	 * @param string $order_id
	 * @param string $yborder_id
	 * @return array
	 */
	public function getOrder($order_id='',$yborder_id=''){
		try{
    		return parent::getOrder($order_id,$yborder_id);
		}catch(\yeepayMPayException $e){
			$this->loge('getOrder', $e, func_get_args());
			return $this->errore($e);
		}
		return null;
	}
	
	/**
	* 有 获取消费清算对账单
	*/
	public function getClearPayData($startdate,$enddate){
		try{
    		return parent::getClearPayData($startdate,$enddate);
		}catch(\yeepayMPayException $e){
			$this->loge('getClearPayData', $e, func_get_args());
		}
		return null;
	}
	
	/**
	* 有 获取退款清算对账单
	*/
	public function getClearRefundData($startdate,$enddate){
		try{
    		return parent::getClearRefundData($startdate,$enddate);
		}catch(\yeepayMPayException $e){
			$this->loge('getClearRefundData', $e, func_get_args());
			return $this->errore($e);
		}
		return null;
	}
	/**
	 * 有 退货记录查询
	 * 
	 * @param string $order_id
	 * @param string $yborder_id
	 * @return array
	 */
	public function getRefund($order_id='',$yborder_id=''){
		try{
    		return parent::getRefund($order_id,$yborder_id);
		}catch(\yeepayMPayException $e){
			$this->loge('getRefund', $e, func_get_args());
			return $this->errore($e);
		}
		return null;
	}
	// **************************未完善的接口 end *****************************/
	
	/**
	 * 获取异常的错误原因和错误码，除此与logger函数功能同
	 */
	private function errore($e){
		return $this->error($e->getCode(), $e->getMessage());
	}
	private function error($error_code, $error_msg){
		return [
			'error_code' => $error_code,
			'error_msg'   => $error_msg
		];
	}

	/**
	 * 获取异常的错误原因和错误码，除此与logger函数功能同
	 */
	private function loge($tag, $e, $data){
		$this->logger($tag, $e->getCode(), $e->getMessage(), $data);
	}
	/**
	 * @param $tag 分类
	 * @param $error_code 错误码
	 * @param $error_msg 错误原因
	 * @param $data 以后可使用 call_user_func_array 进行恢复
	 */
	private function logger($tag, $error_code, $error_msg, $data){
		// @todo 这个纪录到数据库里面
		$content =  "\n\nerror : {$tag} : {$error_code} : {$error_msg} : ".var_export($data,true);
		file_put_contents(__DIR__ . '/yeepay.log', $content);
	}
}