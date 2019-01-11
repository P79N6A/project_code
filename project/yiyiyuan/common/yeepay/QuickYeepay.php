<?php 
/**
 * 一键支付接口文档
 * 官方文档 http://mobiletest.yeepay.com/file/doc/pubshow?doc_id=19#ha_6
 * @author lijin
 */
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
if (!class_exists('yeepayMPay')){
	include __DIR__.'/yeepayMPay.php';
}

class QuickYeepay extends \yeepayMPay{
	public function __construct(){
		$config = include __DIR__.'/config.quick.php'; // @todo 先使用测试的
		parent::__construct(
			$config['merchantaccount'],
			$config['merchantPublicKey'],
			$config['merchantPrivateKey'],
			$config['yeepayPublicKey']
		);
	}
	
	
	//************************支付流程 start ****************************/
	/**
	 * 移动支付
	 */
	public function payRequest($postData){
		if( !is_array( $postData ) ){
			return $this->error(1000,"提交的数据不能为空");
		}
		
		try{
			$data = [
				'orderid' 	=> $postData['orderid'],		//客户订单号   √   string  商户生成的唯一订单号，最长50位
				'transtime' => intval($postData['transtime']),	//交易时间    √   int     时间戳，例如：1361324896，精确到秒
				'currency' 	=> intval($postData['currency']),	//交易币种      int     默认156人民币(当前仅支持人民币)
				'amount' 	=> intval($postData['amount']),		//交易金额    √   int     以"分"为单位的整型，必须大于零
				'productcatalog'=> $postData['productcatalog'],//商品类别码   √   string  详见商品类别码表
				'productname'	=> $postData['productname'],		//商品名称    √   string  最长50位，出于风控考虑，请按下面的格式传递值：'应用商品名称，如“诛仙-3阶成品天琊”，此商品名在发送短信校验的时候会发给用户，所以描述内容不要加在此参数中，以提高用户的体验度。
				'productdesc'	=> $postData['productdesc'],		//商品描述     最长200位
				'identityid'	=> $postData['identityid'],		//用户标识    √   string  最长50位，商户生成的用户账号唯一标识
				'identitytype'	=> intval($postData['identitytype']),	//用户标识类型  √   int     详见用户标识类型码表
				'terminaltype'	=> intval($postData['terminaltype']),	//终端类型    √   int     0、IMEI；1、MAC；2、UUID；3、other
				'terminalid'	=> $postData['terminalid'],		//终端ID    √ string  
				'orderexpdate'	=> intval($postData['orderexpdate']),	//订单有效期时间       int     以分为单位
				'userip' => $postData['userip'],		//用户IP    √   string  用户支付时使用的网络终端IP
				'userua' => $postData['userua'],		//终端UA    √   string  用户使用的移动终端的UA信息
				'callbackurl'	=> $postData['callbackurl'],  //商户后台系统的回调地址       string  用来通知商户支付结果，前后台回调地址的回调内容相同
				'fcallbackurl'	=> $postData['fcallbackurl'],//商户前台系统提供的回调地址     string  '用来通知商户支付结果，前后台回调地址的回调内容相同。用户在网页支付成功页面，点击“返回商户”时的回调地址
				'version'	=> intval($postData['version']),		    //网页收银台版本        int     商户可以使用此参数定制调用的网页收银台版本，目前只支持wap版本（参数传值“0”或不传值）
				'paytypes'	=> $postData['paytypes'],		//支付方式      string  格式：1|2|3|41- 借记卡支付；2- 信用卡支付；3- 手机充值卡支付；4- 游戏点卡支付注：'该参数若不传此参数，则默认选择运营后台为该商户开通的支付方式。
				'cardno'	=> $postData['cardno'],			//银行卡序列号   在进行网页支付请求的时候，如果传此参数会把银行卡号直接在银行信息界面显示卡号，注意：P2P商户此参数须必填
				'idcardtype'=> $postData['idcardtype'],	//证件类型      01：身份证，注意：证件类型和证件号必须同时为空或者同时不为空
				'idcard'	=> $postData['idcard'],			//证件号     注意：P2P商户此参数须必填
				'owner'		=> $postData['owner'],				//持卡人姓名      注意：P2P商户此参数须必填
			];
			return $this->getUrl(YEEPAY_MOBILE_API, 'api/pay/request', $data);
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
	 * 支付查询: 一键支付不可使用此接口，可以用getOrder方法
	 */
	public function getOrder($order_id='',$yborder_id=''){
		if(empty($order_id)){
			return $this->error(1000,"order_id不能为空");
		}
		try{
    		return parent::getOrder($order_id);
		}catch(\yeepayMPayException $e){
			$this->loge('getOrder', $e, func_get_args());
			return $this->errore($e);
		}

	}
	
	// 回调数据解析
	public function callback($data,$encryptkey){
		try{
    		return parent::callback($data, $encryptkey);
		}catch(\yeepayMPayException $e){
			$this->loge('getOrder', $e, func_get_args());
			return $this->errore($e);
		}
	}
	
	/**
	 * 银行卡信息查询
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
	 * 不可用：查询绑卡信息列表，获取对应支付身份的绑卡id
	 * 
	 * @param string $identity_id
	 * @param int $identity_type
	 * @return array
	 */
	public function getBinds($identity_type,$identity_id){
		try{
			$identity_type = intval($identity_type);
    		return parent::getBinds($identity_type,$identity_id);
		}catch(\yeepayMPayException $e){
			$this->loge('getBinds', $e, func_get_args());
			return $this->errore($e);
		}
		/**
		{
		    "cardlist": [
		        {
		            "bindid": "890",
		            "card_last": "5420",
		            "card_name": "华夏银行贷记卡",
		            "card_top": "622638",
		            "bindvalidthru": 1377987201,
		            "merchantaccount": "YB01000000144",
		            "phone": "13400003065"
		        }
		    ],
		    "identityid": "493002407599521",
		    "identitytype": 0,
		    "merchantaccount": "YB01000000144",
		    "sign": "DHSgQCLxaBoLaFIYSgO8ofLS7+1YpGXa/LRfpovOwlO9xWpBEdKAx+SKwUQuFMJze1oWXL964lRzEhfSDqRqBfU8+5/3NtyPQbJyhs9WGcfEB/BJNlTA5dpl2W0V6Qn+O0UydqN6hWsZXPqGBZYsVB7t5QtXRawB4OblTXwDFWM="
		}
		 */
	}
	//************************支付流程 end ****************************/
	
	
	/**
	 * 获取异常的错误原因和错误码，除此与logger函数功能同
	 */
	private function errore($e){
		return $this->error($e->getCode(), $e->getMessage());
	}
	private function error($error_code, $error_msg){
		return [
			'error_code' => $error_code,
			'error_msg'  => $error_msg
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