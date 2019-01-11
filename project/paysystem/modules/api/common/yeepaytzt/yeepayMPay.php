<?php
use app\common\Logger;
// 接口类型
define('YEEPAY_PAY_API', 1);



class yeepayMPay {
	
// CURL 请求相关参数
	public $useragent = 'Yeepay MobilePay PHPSDK v1.1x';
	public $connecttimeout = 30;
	public $timeout = 30;
	public $ssl_verifypeer = FALSE;
	
// CURL 请求状态相关数据
	public $http_header = array();
	public $http_code;
	public $http_info;
	public $url;
// 相关配置参数
	protected $account;
	protected $merchantPublicKey;
	protected $merchantPrivateKey;
	protected $yeepayPublicKey;
// 请求AES密钥
	private $AESKey;
//	正式环境API请求基础地址
//测试地址
	//private $API_Pay_Base_Url = 'http://10.151.30.6:8006/tzt-api/api/';

	private $API_Pay_Base_Url = 'https://jinrong.yeepay.com/tzt-api/api/';
	/**
	 * - $account 商户账号
	 * - $merchantPublicKey 商户公钥
	 * - $merchantPrivateKey 商户私钥
	 * - $yeepayPublicKey 易宝公钥
	 * 
	 * @param string $account
	 * @param string $merchantPublicKey
	 * @param string $merchantPrivateKey
	 * @param string $yeepayPublicKey
	 */
	public function __construct($account,$merchantPublicKey,$merchantPrivateKey,$yeepayPublicKey){
		$this->account = $account;
		$this->merchantPublicKey = $merchantPublicKey;
		$this->merchantPrivateKey = $merchantPrivateKey;
		$this->yeepayPublicKey = $yeepayPublicKey;
	}
	
	/**
	 *有短验绑卡请求
	 */
	public function bindCardRequest($requestno,$identityid,$identitytype,$cardno,$idcardtype,$idcardno,$username,$phone,
	$advicesmstype,$customerenhancedtype,$avaliabletime,$callbackurl,$requesttime){
		$query = array(
			'requestno'	           =>	 $requestno,
			'identityid'	         =>	 $identityid,
			'identitytype'         =>	 $identitytype,
			'cardno'	             =>	 $cardno,
			'idcardtype'	         =>	 $idcardtype,
			'idcardno'		         =>	 $idcardno,
			'username'		         =>	 $username,
			'phone'		             =>	 $phone,
			'advicesmstype'        =>  $advicesmstype,
			'customerenhancedtype' =>  $customerenhancedtype,
			'avaliabletime'        =>  $avaliabletime,
			'callbackurl'          =>  $callbackurl,
			'requesttime'          =>  $requesttime
			);
		return $this->post(YEEPAY_PAY_API, 'bindcard/request', $query);
	}
	
	/**
	* 确认绑卡
	*/
	public function bindCardConfirm($requestno,$validatecode){
		$query = array(
			'requestno'	    =>	$requestno,
			'validatecode'	=>	$validatecode
		);
		return $this->post(YEEPAY_PAY_API, 'bindcard/confirm', $query);
	}

	/**
	* 绑卡短验重发
	*/
		public function bindCardSms($requestno){
		$query = array(
			'requestno'	=>	(string)$requestno
		);
		return $this->post(YEEPAY_PAY_API, 'bindcard/resendsms', $query);
	}
	
	
	/**
	 * 无短验充值接口
	 */
	 
		public function BindPayDirect($requestno,$identityid,$identitytype,$requesttime,$amount,$productname,$avaliabletime,$cardtop,
		$cardlast,$callbackurl,$invterm,$terminalid,$terminaltype,$registtime,$registip,$lastloginip,$lastlogintime,$lastloginterminalid,$lastloginterminaltype,
	$isthirdpartyuser,$issetpaypwd,$free1,$free2,$free3){
		$query = array(
				'requestno'	     =>	$requestno,
				'identityid'	   =>	$identityid,
				'identitytype'   =>	$identitytype,
				'requesttime'	   =>	$requesttime,
				'amount'	       =>	$amount,
				'productname'	   =>	$productname,
				'avaliabletime'  =>	$avaliabletime,
				'cardtop'        => $cardtop,
			  'cardlast'       => $cardlast,
				'callbackurl'	   =>	$callbackurl,
				'invterm'        => $invterm,
			  'terminalid'     => $terminalid,
			  'terminaltype'   => $terminaltype,
			  'registtime'     => $registtime,
			  'registip'       => $registip,
			  'lastloginip'    => $lastloginip,
			  'lastlogintime'  => $lastlogintime,
			  'lastloginterminalid'   => $lastloginterminalid,
			  'lastloginterminaltype' => $lastloginterminaltype,
			  'isthirdpartyuser'      => $isthirdpartyuser,
			  'issetpaypwd'    => $issetpaypwd,
			  'free1'          => $free1,
			  'free2'          => $free2,
			  'free3'          => $free3
 
			  
			  );
		return $this->post(YEEPAY_PAY_API, 'bindpay/direct', $query);
	}
	/**
	 * 有短验充值请求
	 */
	public function bindPayRequest($requestno,$identityid,$identitytype,$cardtop,$cardlast,$amount,$advicesmstype,$avaliabletime,
	$requesttime,$productname,$callbackurl,$invterm,$terminalid,$terminaltype,$registtime,$registip,$lastloginip,$lastlogintime,$lastloginterminalid,$lastloginterminaltype,
	$isthirdpartyuser,$issetpaypwd,$free1,$free2,$free3){
		$query = array(
				'requestno'	     =>	$requestno,
				'identityid'	   =>	$identityid,
				'identitytype'   =>	$identitytype,
				'requesttime'	   =>	$requesttime,
				'amount'	       =>	$amount,
				'advicesmstype'  => $advicesmstype,
				'avaliabletime'  =>	$avaliabletime,
				'productname'	   =>	$productname,
				'cardtop'        => $cardtop,
			  'cardlast'       => $cardlast,
				'callbackurl'	   =>	$callbackurl,
				'invterm'        => $invterm,
			  'terminalid'     => $terminalid,
			  'terminaltype'   => $terminaltype,
			  'registtime'     => $registtime,
			  'registip'       => $registip,
			  'lastloginip'    => $lastloginip,
			  'lastlogintime'  => $lastlogintime,
			  'lastloginterminalid'   => $lastloginterminalid,
			  'lastloginterminaltype' => $lastloginterminaltype,
			  'isthirdpartyuser'      => $isthirdpartyuser,
			  'issetpaypwd'    => $issetpaypwd,
			  'free1'          => $free1,
			  'free2'          => $free2,
			  'free3'          => $free3
		);
		return $this->post(YEEPAY_PAY_API, 'bindpay/request', $query);
	}
	
	/**
	* 有短验充值确认
	*/
		public function bindPayConfirm($requestno,$validatecode){
		$query = array(
			'requestno'	    =>	$requestno,
			'validatecode'	=>	$validatecode
		);
		return $this->post(YEEPAY_PAY_API, 'bindpay/confirm', $query);
	}
	
		/**
	* 充值短验重发
	*/
		public function bindPaySms($requestno){
		$query = array(
			'requestno'	=>	$requestno
		);
		return $this->post(YEEPAY_PAY_API, 'bindpay/resendsms', $query);
	}
	
	/**
	* 首次支付
	*/
	public function firstPayRequest($requestno,$identityid,$identitytype,$cardno,$idcardtype,$idcardno,$username,$phone,$amount,$advicesmstype,$avaliabletime,
	$requesttime,$productname,$callbackurl,$invterm,$terminalid,$terminaltype,$registtime,$registip,$isthirdpartyuser,$issetpaypwd){
		$query = array(
			  'requestno'	       =>	$requestno,
				'identityid'	     =>	$identityid,
				'identitytype'     =>	$identitytype,
				'cardno'	         =>	$cardno,
			  'idcardtype'	     =>	$idcardtype,
			  'idcardno'		     =>	$idcardno,
			  'username'		     =>	$username,
			  'phone'		         =>	$phone,
				'requesttime'	     =>	$requesttime,
				'amount'	         =>	$amount,
				'advicesmstype'    => $advicesmstype,
				'avaliabletime'    =>	$avaliabletime,
				'productname'	     =>	$productname,
				'callbackurl'	     =>	$callbackurl,
				'invterm'          => $invterm,
			  'terminalid'       => $terminalid,
			  'terminaltype'     => $terminaltype,
			  'registtime'       => $registtime,
			  'registip'         => $registip,
			  'isthirdpartyuser' => $isthirdpartyuser,
			  'issetpaypwd'      => $issetpaypwd
		);
		return $this->post(YEEPAY_PAY_API, 'firstpay/request', $query);
	}
	
		/**
	* 首次充值确认
	*/
		public function firstPayConfirm($requestno,$validatecode){
		$query = array(
			'requestno'	    =>	$requestno,
			'validatecode'	=>	$validatecode
		);
		return $this->post(YEEPAY_PAY_API, 'firstpay/confirm', $query);
	}
	
			/**
	* 首次充值短验重发
	*/
		public function firstPaySms($requestno){
		$query = array(
			'requestno'	=>	$requestno
		);
		return $this->post(YEEPAY_PAY_API, 'firstpay/resendsms', $query);
	}
	
	/**
	*  绑卡充值记录查询
	*/
		public function bindPayRecord($requestno,$yborderid){
		$query = array(
			'requestno'	=>	$requestno,
			'yborderid' =>  $yborderid
		);
		return $this->post(YEEPAY_PAY_API, 'bindpay/record', $query);
	}
	
		/**
	*  换卡记录查询
	*/
		public function changeCardRecord($requestno,$yborderid){
		$query = array(
			'requestno'	=>	$requestno,
			'yborderid' =>  $yborderid
		);
		return $this->post(YEEPAY_PAY_API, 'changecard/record', $query);
	}
	 
  /**
	 * 查询可提现余额
	 
	 */
	public function drawValidAmount(){
	$query = array();
		return $this->get(YEEPAY_PAY_API, 'withdraw/validamount', $query);
	}
	
	/**
	 * 提现
	 */
	public function withdraw($requestno,$identityid, $identitytype,$cardtop,$cardlast,$amount,$currency,$drawtype,
	$userip,$requesttime,$callbackurl,$free1,$free2,$free3) {
	
		$query = array(
		  'requestno'    => $requestno,
			'identityid'	 =>	$identityid,
			'identitytype' =>	$identitytype,
			'cardtop'      => $cardtop,
			'cardlast'     => $cardlast,
			'amount'       => $amount,
			'currency'     => $currency,
			'drawtype'     => $drawtype,
			'userip'       => $userip,
			'requesttime'  => $requesttime,
			'callbackurl'  => $callbackurl,
			'free1'        => $free1,
			'free2'        => $free2,
			'free3'        => $free3
			
		);

		return $this->post(YEEPAY_PAY_API,'withdraw/request',$query);
	}

		
		/**
	 * 提现查询
	 */
	public function drawRecord($requestno,$yborderid) {
		$query = array(
		  'requestno'    => $requestno,
		  'yborderid'    => $yborderid
		);

		return $this->get(YEEPAY_PAY_API,'withdraw/record',$query);
	}
	
	
	/**
	 * 返回绑卡信息列表
	 * 
	 * @param string $identity_id
	 * @param int $identity_type
	 * @return array
	 */
	public function getBinds($identitytype,$identityid){
		$query = array(
			'identityid'	  =>	$identityid,
			'identitytype'	=>	$identitytype,
		);
		return $this->get(YEEPAY_PAY_API, 'bindcard/list', $query);
	}
	
	/**
	 * 绑卡记录查询
	 */
	public function bindCardRecord($requestno,$yborderid) {
		$query = array(
		  'requestno'    => $requestno,
		  'yborderid'    => $yborderid
		);

		return $this->get(YEEPAY_PAY_API,'bindcard/record',$query);
	}
	
	/**
	 * 银行卡信息查询
	 * 
	 * @param string $cardno
	 * @return array
	 */
	public function bankcardCheck($cardno){
		$query = array(
			'cardno'  =>  $cardno
		);
		return $this->post(YEEPAY_PAY_API, 'bankcard/check', $query);
	}
	
		/**
	 * 首次充值记录查询
	 */
	public function firstPayRecord($requestno,$yborderid) {
		$query = array(
		  'requestno'    => $requestno,
		  'yborderid'    => $yborderid
		);

		return $this->get(YEEPAY_PAY_API,'firstpay/record',$query);
	}
	//退款查询
		public function refundRecord($requestno,$yborderid) {
		$query = array(
		  'requestno'    => $requestno,
		  'yborderid'    => $yborderid
		);

		return $this->get(YEEPAY_PAY_API,'refund/record',$query);
	}
	
		/**
	 * 补充鉴权打款请求
	 */
	public function remitRequest($requestno){
		$query = array(
			'requestno' => $requestno
		);
		return $this->post(YEEPAY_PAY_API, 'remit/request', $query);
	}
	
	/**
	 * 补充鉴权打款确认
	 */
	public function remitConfirm($requestno,$amount){
		$query = array(
			'requestno' => $requestno,
			'amount'    => $amount
		);
		return $this->post(YEEPAY_PAY_API, 'remit/confirm', $query);
	}
	
	/**
	 * 换卡请求
	 */
	public function changecardRequest($requestno,$identityid,$identitytype,$cardno,$idcardno,$idcardtype,$username,$phone,$oricardno,$advicesmstype,$avaliabletime,$callbackurl,$requesttime){
		$query = array(
			'requestno'     =>  $requestno,
			'identityid'	  =>	$identityid,
			'identitytype'	=>	$identitytype,
			'cardno'		    =>	$cardno,
			'idcardno'		  =>	$idcardno,
			'idcardtype'	  =>	$idcardtype,
			'username'	    =>	$username,
			'phone'	        =>	$phone,
			'oricardno'     =>	$oricardno,
			'advicesmstype' =>	$advicesmstype,
			'avaliabletime' =>  $avaliabletime,
			'callbackurl'   =>  $callbackurl,
			'requesttime'   =>  $requesttime
		);
		return $this->post(YEEPAY_PAY_API, 'changecard/request', $query);
	}
	
	/**
	 * 换卡请求短验确认
	 */
	public function changecardConfirm($requestno,$validatecode){
		$query = array(
			'requestno'       =>  $requestno,
			'validatecode'	  =>	$validatecode
		);
		return $this->post(YEEPAY_PAY_API, 'changecard/confirm', $query);
	}
	
	/**
	 * 换卡请求重发短验
	 */
	public function changecardResendsms($requestno){
		$query = array(
			'requestno'  =>  $requestno,
		);
		return $this->post(YEEPAY_PAY_API, 'changecard/resendsms', $query);
	}
	/**
	 * 软解绑接口：注意此接口解绑后只能绑回原卡，如需换卡，请调用换卡接口
	 */
	
	public function unbind($identity_id,$identity_type,$cardtop,$cardlast){
		$query = array(
			'identityid'	  =>	$identity_id,
			'identitytype'	=>	$identity_type,
			'cardtop'       =>  $cardtop,
			'cardlast'      =>  $cardlast
			
		);
		return $this->post(YEEPAY_PAY_API, 'unbind/request', $query);
	}
		/**
	 * 退款
	 */
	public function refund($requestno,$paymentyborderid,$amount,$callbackurl,$requesttime,$remark,$free1,$free2,$free3){
		$query = array(
			'requestno'         =>  $requestno,
			'paymentyborderid'  =>  $paymentyborderid,
			'amount'            =>  $amount,
			'callbackurl'       =>  $callbackurl,
			'requesttime'       =>  $requesttime,
			'remark'            =>  $remark,
			'free1'        => $free1,
			'free2'        => $free2,
			'free3'        => $free3					
		);
		return $this->post(YEEPAY_PAY_API, 'refund/request', $query);
	}
	
	
/**
	* 对账文件下载--鉴权
	*/
	public function getAuthData($startdate,$enddate){
		$query = array(
			'startdate'	=>	(string)$startdate,
			'enddate'	  =>	(string)$enddate,
		);
		return $this->getClearData(YEEPAY_PAY_API, 'accountcheck/auth', $query);
	}
/**
	* 对账文件下载--支付
	*/
	public function getPaymentData($startdate,$enddate){
		$query = array(
			'startdate'	=>	(string)$startdate,
			'enddate'	  =>	(string)$enddate,
		);
		return $this->getClearData(YEEPAY_PAY_API, 'accountcheck/payment', $query);
	}
	/**
	* 对账文件下载--提现
	*/
	public function getWithdrawData($startdate,$enddate){
		$query = array(
			'startdate'	=>	(string)$startdate,
			'enddate'	  =>	(string)$enddate,
		);
		return $this->getClearData(YEEPAY_PAY_API, 'accountcheck/withdraw', $query);
	}
	/**
	* 对账文件下载--换卡
	*/
	public function getChangCardData($startdate,$enddate){
		$query = array(
			'startdate'	=>	(string)$startdate,
			'enddate'	  =>	(string)$enddate,
		);
		return $this->getClearData(YEEPAY_PAY_API, 'accountcheck/changcard', $query);
	}	
	
	/**
	 * 回调返回数据解析函数
	 * $data = $_POST['data']
	 * $encryptkey = $_POST['encryptkey']
	 * 
	 * @param string $data
	 * @param string $encryptkey
	 * @return array
	 */
	public function callback($data,$encryptkey){
		return $this->parseReturn($data, $encryptkey);
	}
	protected function post($type,$method,$query){
		$request = $this->buildRequest($query);
		$url = $this->getAPIUrl($type,$method);
		$data = $this->http($url, 'POST',http_build_query($request));
		Logger::dayLog('yeepay/newtzt', 'post', 'url', $url,'提交数据',$request,'响应结果',$data);
		if($this->http_info['http_code'] == 405)
			throw new yeepayMPayException('此接口不支持使用POST方法请求',1004);
		return $this->parseReturnData($data);
	}
	/**
	 * 使用GET的模式发出API请求
	 * 
	 * @param string $type
	 * @param string $method
	 * @param array $query
	 * @return array
	 */
	protected function get($type,$method,$query){
		$request = $this->buildRequest($query);
		$url = $this->getAPIUrl($type,$method);
		$url .= '?'.http_build_query($request);
		$data = $this->http($url, 'GET');
		if($this->http_info['http_code'] == 405)
			throw new yeepayMPayException('此接口不支持使用GET方法请求',1003);
		return $this->parseReturnData($data);
	}
	
	/**
	* 请求获取清算对账单接口
	*/
	protected function getClearData($type,$method,$query){

		
		$request = $this->buildRequest($query);
		$url = $this->getAPIUrl($type,$method);
		$url .= '?'.http_build_query($request);
		$data = $this->http($url, 'GET');
		if($this->http_info['http_code'] == 405)
		{
			throw new yeepayMPayException('此接口不支持使用GET方法请求',1003);
		}
		return $this->parseReturnClearData($data);
	}
	
	/**
	 * 返回请求URL地址
	 * @param string $type
	 * @param string $method
	 * @param array $query
	 * @return string
	 */
	protected function getUrl($type,$method,$query){
		$request = $this->buildRequest($query);
		$url = $this->getAPIUrl($type,$method);
		$url .= '?'.http_build_query($request);
		return $url;
	}
	/**
	 * 创建提交到易宝的最终请求
	 * 
	 * @param array $query
	 * @return array
	 */
	protected function buildRequest(array $query){
		if(!array_key_exists('merchantno', $query))
			$query['merchantno'] = $this->account;
		$sign = $this->RSASign($query);
		$query['sign'] = $sign;
		$request = array();
		$request['merchantno'] = $this->account;
		$request['encryptkey'] = $this->getEncryptkey();
		$request['data'] = $this->AESEncryptRequest($query);
		return $request;
	}
	/**
	 * 根据请求类型不同，返回完整API请求地址
	 * 
	 * @param int $type
	 * @param string $method
	 * @return string
	 */
	protected function getAPIUrl($type,$method){

			return $this->API_Pay_Base_Url.$method;	
	}
	/**
	 * 
	 * @param string $url
	 * @param string $method
	 * @param string $postfields
	 * @return mixed
	 */
	protected function http($url, $method, $postfields = NULL) {
		$this->http_info = array();
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);
		$method = strtoupper($method);
		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields))
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields))
					$url = "{$url}?{$postfields}";
		}
		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;
		curl_close ($ci);
		return $response;
	}
	
	protected function parseReturnClearData($data){	
	try{
		if(strpos($data, 'data') == true )
		{
			$return = json_decode($data,true);
			if(array_key_exists('error_code', $return) && !array_key_exists('status', $return))
				throw new yeepayMPayException($return['error_msg'],$return['error_code']);
			return $this->parseReturn($return['data'], $return['encryptkey']);
		}else{
			return $data;
		}
	}catch (yeepayMPayException $e) {
          return $this->parseReturn($return['data'], $return['encryptkey']);
          }
}


		/**
	 * 解析返回数据
	 * @param type $data
	 * @return type
	 * @throws yeepayMPayException
	 */
	protected function parseReturnData($data)
	{ 
		$return = json_decode($data,true);
   	
   	 if(!array_key_exists('sign', $return)){
   	 	 try {	
   	 	 		if(array_key_exists('errorcode', $return) && !array_key_exists('status', $return))
			 {
				throw new yeepayMPayException($return['errormsg'],$return['errorcode']);
				}
		return $this->parseReturn($return['data'], $return['encryptkey']);
  	
	}catch (yeepayMPayException $e) {
		return  null;
}
}
}


/**
	 * 解析返回数据
	 * @param type $data
	 * @param type $encryptkey
	 * @return type
	 * @throws yeepayMPayException
	*/
	protected function parseReturn($data,$encryptkey){
	try
		{	
			$AESKey = $this->getYeepayAESKey($encryptkey);
		  $return = $this->AESDecryptData($data, $AESKey);
		  $return = json_decode($return,true);
		 Logger::dayLog('yeepay/newtzt','返回数据',$return);
		  if (!empty($return['amount']))
         {
             $return['amount']=sprintf("%.2f",$return['amount']);
         }
		 Logger::dayLog('yeepay/newtzt','返回数据',$return);
		if(!array_key_exists('sign', $return)){
			if(array_key_exists('errorcode', $return)){
				throw new yeepayMPayException($return['errormsg'],$return['errorcode']);
			}
			throw new yeepayMPayException('请求返回异常',1001);
		}else{
			if( !$this->RSAVerify($return, $return['sign']) )
			{
				throw new yeepayMPayException('请求返回签名验证失败',1002);
		  }
		}
		//unset($return['sign']);
		return $return;
		}catch (yeepayMPayException $e) 
		{
			return null;
		}
	}
	/**
	 * 生成一个随机的字符串作为AES密钥
	 * 
	 * @param number $length
	 * @return string
	 */
	protected function generateAESKey($length=16){
		$baseString = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$AESKey = '';
		$_len = strlen($baseString);
		for($i=1;$i<=$length;$i++){
			$AESKey .= $baseString[rand(0, $_len-1)];
		}
		$this->AESKey = $AESKey;
		return $AESKey;
	}
	/**
	 * 通过RSA，使用易宝公钥，加密本次请求的AESKey
	 * 
	 * @return string
	 */
	protected function getEncryptkey(){
        if(!$this->AESKey)
            $this->generateAESKey();
        $str        =chunk_split($this->yeepayPublicKey, 64, "\n");
        $key = "-----BEGIN PUBLIC KEY-----\n$str-----END PUBLIC KEY-----\n";
        openssl_public_encrypt($this->AESKey,$encrypted,$key,OPENSSL_PKCS1_PADDING);//公钥加密
        $encryptKey = base64_encode($encrypted);
        return $encryptKey;
	}
	/**
	 * 返回易宝返回数据的AESKey
	 * 
	 * @param unknown $encryptkey
	 * @return Ambigous <string, boolean, unknown>
	 */
	protected function getYeepayAESKey($encryptkey){
        $str        = chunk_split($this->merchantPrivateKey, 64, "\n");
        $private_key = "-----BEGIN PRIVATE KEY-----\n$str-----END PRIVATE KEY-----\n";
        openssl_private_decrypt(base64_decode($encryptkey),$yeepayAESKey,$private_key);
        return $yeepayAESKey;
	}
	/**
	 * 通过AES加密请求数据
	 * 
	 * @param array $query
	 * @return string
	 */
	protected function AESEncryptRequest(array $query){
        if(!$this->AESKey)
            $this->generateAESKey();
        $ciphertext = base64_encode(openssl_encrypt(json_encode($query),"aes-128-ecb",$this->AESKey,OPENSSL_PKCS1_PADDING));
        return $ciphertext;
	}
/**
	 * 通过AES解密易宝返回的数据
	 * 
	 * @param string $data
	 * @param string $AESKey
	 * @return Ambigous <boolean, string, unknown>
	 */
	protected function AESDecryptData($data,$AESKey){
        $json=openssl_decrypt(base64_decode($data),"aes-128-ecb",$AESKey,OPENSSL_PKCS1_PADDING);
        return preg_replace('/:(\d{11,})(\,|\})/', ':"$1"$2', $json);
	}

	/**
	 * 用RSA 签名请求
	 * 
	 * @param array $query
	 * @return string
	 */
	protected function RSASign(array $query){
        if(array_key_exists('sign', $query))
            unset($query['sign']);
        ksort($query);
        $str        = chunk_split($this->merchantPrivateKey, 64, "\n");
        $private_key = "-----BEGIN PRIVATE KEY-----\n$str-----END PRIVATE KEY-----\n";
        $signature = '';
        if (openssl_sign(join('',$query), $signature, $private_key, OPENSSL_PKCS1_PADDING))
        {
            $sign=base64_encode($signature);
        }
        return $sign;
	}
	/**
	 * 使用易宝公钥检测易宝返回数据签名是否正确
	 * 
	 * @param array $query
	 * @param string $sign
	 * @return boolean
	 */
	public function RSAVerify(array $return,$sign){		
        if(array_key_exists('sign', $return))
            unset($return['sign']);
        ksort($return);
        foreach ($return as $k=>$val){
            if( is_array($val) )
                $return[$k] = self::cn_json_encode($val);
        }
        $str        = chunk_split($this->yeepayPublicKey, 64, "\n");
        $private_key ="-----BEGIN PUBLIC KEY-----\n$str-----END PUBLIC KEY-----\n";
        $ok=openssl_verify(join('',$return), base64_decode($sign), $private_key, OPENSSL_PKCS1_PADDING);
		//var_dump($ok);
        return $ok;
	}




	public static function cn_json_encode($value){
		if (defined('JSON_UNESCAPED_UNICODE'))
			return json_encode($value,JSON_UNESCAPED_UNICODE);
		else{
			$encoded = urldecode(json_encode(self::array_urlencode($value)));
			return preg_replace(array('/\r/','/\n/'), array('\\r','\\n'), $encoded);
		}
	}
	public static function array_urlencode($value){
		if (is_array($value)) {
			return array_map(array('yeepayMPay','array_urlencode'),$value);
		}elseif (is_bool($value) || is_numeric($value)){
			return $value;
		}else{
			return urlencode(addslashes($value));
		}
	}
	/**
	 * Get the header info to store.
	 */
	public function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
//	易宝一键支付支持的卡号前缀（2013.6.10版）
	public static $carBins = array('544210','548943','370267','356879','356880','356881','356882','524374','528856','550213','622236','625330','625331','625332','622889','625900','625915','625916','622171','625017','625018','625019','625986','625925','625114','622158','625917','622159','625021','625022','625939','625914','370246','370248','370249','427010','427018','427019','427020','427029','427030','427039','370247','438125','438126','451804','451810','451811','45806','458071','489734','489735','489736','510529','427062','524091','427064','530970','53098','530990','558360','524047','525498','622230','622231','622232','622233','622234','622235','622237','622239','622240','622245','622238','62451804','62451810','62451811','6245806','62458071','6253098','628288','628286','622206','526836','513685','543098','458441','622246','622838','403361','404117','404118','404119','404120','404121','463758','519412','519413','520082','520083','552599','558730','514027','622836','622837','628268','625996','625998','625997','625908','625910','625909','356833','356835','409665','409666','409668','409669','409670','409671','409672','512315','512316','512411','512412','514957','409667','438088','552742','553131','514958','622760','628388','518377','622788','625140','622750','622751','625145','622346','622347','544887','557080','436718','436745','489592','532450','532458','436738','436748','552801','558895','559051','622168','628266','628366','622708','622166','531693','356895','356896','356899','625964','625965','625966','622381','622675','622676','622677','5453242','5491031','553242','5544033','622812','622810','622811','628310','376968','376969','400360','403391','403392','376966','404158','404159','404171','404172','404173','404174','404157','433667','433668','433669','514906','403393','520108','433666','558916','622678','622679','622680','622688','622689','628206','556617','628209','518212','628208','622687','625978','625979','625980','625981','356837','356838','356839','356840','406254','481699','486497','524090','543159','622161','622570','622650','425862','622658','406252','622655','628201','628202','622657','622685','622659','523959','528709','539867','539868','622637','622638','628318','528708','622636','625967','625968','625969','545392','545393','545431','545447','356859','356857','407405','421869','421870','421871','512466','356856','528948','552288','622600','622601','622602','517636','622621','628258','556610','622603','464580','464581','523952','545217','553161','356858','622623','625912','625913','625911','435744','435745','483536','622525','622526','998801','998802','622902','461982','486493','486494','486861','523036','451289','527414','528057','622901','622922','628212','451290','524070','625084','625085','625086','625087','548738','549633','552398','625082','625083','625960','625961','625962','625963','356851','356852','404738','404739','456418','498451','515672','356850','517650','525998','622177','622277','628222','622500','628221','622176','622276','622228','625993','625957','625958','625971','625970','531659','622157','528020','622155','622156','526855','356868','356869','406365','406366','428911','436768','436769','436770','487013','491032','491033','491034','491035','491036','491037','491038','436771','518364','520152','520382','541709','541710','548844','552794','493427','622555','622556','622557','622558','622559','622560','528931','685800','6858000','558894','625072','625071','628260','628259','522001','622163','622853','628203','622851','622852','356827','356828','356830','402673','402674','486466','519498','520131','524031','548838','622148','622149','622268','356829','622300','628230','622269','625099','356885','356886','356887','356888','356890','439188','439227','479228','479229','521302','356889','545620','545621','545947','545948','552534','552587','622575','622576','622577','622578','622579','545619','622581','622582','545623','370285','370286','370287','370289','439225','518710','518718','628362','439226','628262');
	/**
	 * 校验输入的信用卡卡号是否正确，是否属于支持的发卡行
	 * 返回值：
	 *		-1	:	卡号输入错误（不是数字）
	 *		-2	:	卡号填写错误（不符合银行标准卡号规则）
	 *		-3	:	一键支付不支持的银行卡
	 *		1	:	卡号校验成功
	 * @param string $cardno
	 * @return int
	 */
	public static function checkCardNo($cardno){
		if( !preg_match('/^\d+$/', $cardno) )
			return -1;
		$_len = strlen($cardno);
		$_x = $cardno[$_len-1];
		$_start = $_len - 2;
		$_sum = 0;
		for ($i = $_start; $i >= 0; $i--) {
			if( ($_start-$i) % 2 == 0){ // 奇数行
				$_v = $cardno[$i]*2;
				$_sum += intval($_v*0.1) + ($_v % 10);
			}else{
				$_sum += $cardno[$i];
			}
		}
		if($_x+$_sum % 10 != 10 && $_x+$_sum % 10 != 0)
			return -2;
		
		foreach (self::$carBins as $carBin) {
			if(substr($cardno,0,strlen($carBin)) == $carBin)
				return 1;
		}
		return -3;
	}
	/**
	 * 校验输入的有效期，并将常见的几种错误输入方式进行纠正
	 * 	- 01/14 模式，去掉 / 线
	 * 	- 1401、14/01 模式，判断是年月先后顺序输入错误，并去掉 / 线
	 *
	 * @param string $validthru
	 * @return boolean
	 */
	public static function checkValidthru(&$validthru){
		if( !preg_match('/^(\d{2})(\d{2})$/',$validthru,$matches)){
			if(!preg_match('/^(\d{2})\/(\d{2})$/', $validthru,$matches))
				return false;
			$validthru = $matches[1].$matches[2];
		}
		if($matches[1]<=12 && $matches[2]>=13)
			return true;
		if($matches[1] > 12 && $matches[2] < 13){
			$validthru = $matches[2].$matches[1];
			return true;
		}
		return false;
	}
	/**
	 * 校验CVV2有效性
	 * 	- 3位数字
	 *
	 * @param string $cvv2
	 * @return boolean
	 */
	public static function checkCvv2($cvv2){
		if(preg_match('/^\d{3}$/', $cvv2))
			return true;
		return false;
	}
}
 
class yeepayMPayException extends \Exception{

//重定义构造器使第一个参数 message 变为必须被指定的属性

     public function __construct($message, $code=0){
     //可以在这里定义一些自己的代码
        echo "错误码：" . $code;
   			 echo "<br>";
   			 echo "错误描述：" . $message;

      //建议同时调用 parent::construct()来检查所有的变量是否已被赋值
        //parent::__construct($message, $code);

       }

       //重写父类中继承过来的方法，自定义字符串输出的样式

        public function __toString() {
        return __CLASS__.":[".$this->code."]:".$this->message."<br>";

         }


 } 