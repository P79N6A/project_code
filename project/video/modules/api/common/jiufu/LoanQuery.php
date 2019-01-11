<?php
namespace app\modules\api\common\jiufu;

use app\common\Http;
use app\common\RSA;
use app\common\Logger;
use app\models\jiufu\JFRemit;
use yii\helpers\ArrayHelper;
/**
 * 订单操作接口
 * 1 查询
 * 2 结束
 */
class LoanQuery
{
	private $rsa;
	private $aes;
	private $config;
	private $env;

	public function __construct()
	{
		$this->env = $env;
		$this->config = JFConfig::model()->getConfig();
		$this->rsa = new RSA();
		$this->rsa->pad0 = false;
		$this->aes = new AES9F;
	}
	/**
	 * 4. rsa: 查询合同接口
	 * 响应需解密
	 * @param string $appId
	 * @param string $productId
	 * @return [res_code, res_data]
	 */
	public function queryContract($appId, $productId)
	{
		//1. 加密传输
		$data = $this->encryptData($appId, $productId);

		$url = $this->config['query_contract_url'];
		$post_data = http_build_query($data);
		$res = Http::interface_post($url, $post_data);
		
		//2. 解析响应
		$response = $this->parseQueryResponse($res);
		Logger::dayLog('9f', '4.queryContract', 'response', $appId, $res, "解密后", $response);
		return $response;
		/**
		 * resp_code 0000为成功，其余失败
		 * resp_msg
		 * 
		 * 0001	无该工单		
		 * 0002	此工单无合同	
		 * 1000	请求参数有误	
		 * 1001	工单号不能为空	
		 * 1002	渠道号不能为空	
		 * 1003	产品ID不能为空	
		 * 9999	系统异常		
		 * 0000	成功查询
		 */
	}
	/**
	 * 6. rsa: 确认电子签章
	 * 响应json
	 * @param string $appId
	 * @return [res_code, res_data]
	 */
	public function getSealCommon($appId)
	{
		//1 加密传输
		$url = $this->config['get_seal_common_url'];
		$url = $url . "?appId={$appId}&isCompanyStamps=1";
		$res = Http::getCurl($url);
		//
		Logger::dayLog('9f', '6.getSealCommon', $url, 'response', $appId, $res);
		
		//2 响应结果
		if (!$res) {
			return ['res_code' => "_ERROR", 'res_data' => '结果为空'];
		}
		$response = json_decode($res, true);
		if (!is_array($response)) {
			return ['res_code' => "_ERROR", 'res_data' => '结果为空'];
		}
		$res_code = $response['returnCode'] == '0000' ? 0 : $response['returnCode'];
		return ['res_code' => $res_code, 'res_data' => $response['returnMsg']];
		/**
		 * returnCode 0000为成功，其余失败
		 * returnMsg
		 * 
		 * 0000  该工单对应全部合同盖章成功！
		 * 0001  合同状态为未读！
		 * 0003  用户已经处于已盖章状态.
		 * 0008  未获取到用户基本信息！！
		 * 0009  签章失败！
		 * 0011  系统异常
		 * 0012  工单下合同为0.
		 * 0020  更新用户签章状态失败！
		 * 0021  写入签署时间失败！！
		 * 0022  更新工单表状态失败！
		 * 02  上传合同文件失败！
		 * 9000  调用公章方法签章失败！
		 * 8000  合同数量与已签约合同数量不符，不能签公章！
		 */
	}

	/**
	 * 7. rsa: 订单查询接口
	 * 响应需解密
	 * @param int $appId
	 * @param string $productId
	 * @return [res_code, res_data]
	 */
	public function query($appId, $productId)
	{
		//1. 加密查询数据; 提交请求
		$data = $this->encryptData($appId, $productId);

		$url = $this->config['loan_query_url'];
		$post_data = http_build_query($data);
		$res = Http::interface_post($url, $post_data);

		//2. 解析响应
		$response = $this->parseQueryResponse($res);
		Logger::dayLog('9f', '7.loanquery', 'response', $appId, $res, "解密后", $response);
		return $response;
		/**
		 * resp_code 0000为成功，其余失败
		 * resp_msg
		 * 
		 * 0001	无该工单		
		 * 1001	工单号不能为空	
		 * 1002	渠道号不能为空	
		 * 1003	产品ID不能为空	
		 * 1004	渠道唯一码不能为空	
		 * 1005	产品唯一码不能为空	
		 * 9999	系统异常	
		 * 0000	成功查询
		 */
	}
	/**
	 * 8. rsa: 结束订单接口
	 * 响应json
	 * @param int $appId
	 * @param string $productId
	 * @return [res_code, res_data]
	 */
	public function loanend($appId, $productId)
	{
		//1 加密传输
		$data = $this->encryptData($appId, $productId);

		$url = $this->config['loan_end_url'];
		$post_data = http_build_query($data);
		Logger::dayLog('9f', '8.loanend', 'request', $appId, $url, $post_data);
		$response = Http::interface_post($url, $post_data);
		Logger::dayLog('9f', '8.loanend', 'response', $appId, $response);
		
		//2 返回结果
		$response = json_decode($response, true);
		$res_code = is_array($response) && $response['resp_code'] == '0000' ? 0 : $response['resp_code'];
		return ['res_code' => $res_code, 'res_data' => $response['resp_msg']];
		/**
		 * resp_code 0000为成功，其余失败
		 * resp_msg
		 * 
		 * 0001	无该工单
		 * 0002	该工单状态不能结束
		 * 1001	工单号不能为空
		 * 1002	渠道号不能为空
		 * 1003	产品ID不能为空
		 * 1004	渠道唯一码不能为空
		 * 1005	产品唯一码不能为空
		 * 9999	系统异常
		 * 0000	成功
		 */
	}
	public function queryPay($oRemit)
	{
		if (empty($oRemit) || !\is_object($oRemit)) {
			return ['res_code' => "error", 'res_data' => '订单参数不能为空'];
		}
		if (empty($oRemit->order_id)) {
			return ['res_code' => "error", 'res_data' => '订单号为空，无法查询'];
		}

		// 组装数据
		$data = [];
		$data['body'] = [
			'appId' => $oRemit['order_id'],
			'channelId' => '1037',
			'productId' => $oRemit['product_id'],
		];
		$data['head'] = [
			'channel' => '',
			'extFiled1' => '',
			'extFiled2' => '',
			'extFiled3' => '',
			'secretKey' => '',
			'sysCode' => '81',
			"transCode" => 'payRes',
			'transDate' => date('Y-m-d', strtotime($oRemit['create_time'])), // String  日期（yyyy-MM-dd）
			'transSerialNo' => $oRemit['product_id'],
			'transTime' => date('H:i:s', strtotime($oRemit['create_time'])), // String  时间（HH:mm:ss）
			'transType' => 'T',
		];

		$post_data = http_build_query($data);
		$post_data = \json_encode($data);

		$url = $this->config['query_pay_url'];
		$res = Http::interface_post($url, $post_data);
		Logger::dayLog('9f/payresult', 'response', 'id', $oRemit['id'], 'orderid', $oRemit['order_id'], $res, "request", $url, $post_data);

		//解析响应结果
		//$res = '{"data":{"appId":10803430,"cashDetail":[{"arrivelDate":1507045509000,"transAmt":1800.00,"transactionType":"B6608","txSts":"S"}],"txCode":"0000"},"msg":"","sts":"000000"}';
		return $this->parsePayResult($res);
	}
	/**
	 *解析支付响应结果
	 */
	private function parsePayResult($res)
	{
		//1.  解析数据为空时，下次轮询
		try {
			$res = json_decode($res, true);
		} catch (\Exception $e) {
			$res = [];
		}
		if (empty($res)) {
			$response = ['status' => 'PAYING'];
			return $response;
		}

		//2.  解析状态
		$status = ArrayHelper::getValue($res, 'sts');
		$code = ArrayHelper::getValue($res, 'data.txCode','');
		$txSts = ArrayHelper::getValue($res, 'data.cashDetail.0.txSts','');
        $txMsg = ArrayHelper::getValue($res, 'data.cashDetail.0.txMsg', '');
		
		// 确定为成功时
		if ($status === '000000' && $code === '0000' && $txSts==='S') {
				return ['status' => 'SUCCESS'];
		}

		// 确定为失败时
		if (in_array($code, ["0003"]) && $txSts==='E') {
				return ['status' => "FAIL", 'rsp_status' => $code, 'rsp_status_text' => $txMsg];
		}

		// 不确定时查询中
		$response = ['status' => 'PAYING']; 
		return $response;
	}
	/*private function queryPayOld($appId)
	{
		//1. 加密传输
		$jsonContent = ['appIds' => $appId];
		$data = $this->encrypt($jsonContent);

		$url = $this->config['query_pay_url'];
		$post_data = http_build_query($data);
		$res = Http::interface_post($url, $post_data);
		
		//2. 解析响应
		$response = $this->parseQueryResponse($res);
		Logger::dayLog('9f', '9.queryPay', 'response', $appId, $res, "解密后", $response);
		return $response;
		**
		 * resp_code 0000为成功，其余失败
		 * resp_msg
		 * 
		 * 0001	无该工单		
		 * 0002	此工单无合同	
		 * 1000	请求参数有误	
		 * 1001	工单号不能为空	
		 * 1002	渠道号不能为空	
		 * 1003	产品ID不能为空	
		 * 9999	系统异常		
		 * 0000	成功查询
		 *
	}*/
	/**
	 * 加密数据
	 * @param  int $appId 玖富订单号
	 * @param string $productId
	 * @return []
	 */
	private function encryptData($appId, $productId)
	{
		$jsonContent = [
			'appId' => $appId, //String	*必选，工单编号
			'saleChannel' => '1037', //String	*必选 渠道号
			'productId' => $productId, //String	*必选 产品ID
			'saleChannelKey' => '1', //String	*必选 渠道唯一码 （未定义时不为空就可）
			'productIdKey' => '1', //String	*必选 产品唯一码 （未定义是不为空就可）
		];
		return $this->encrypt($jsonContent);
	}
	/**
	 * 加密内容并组合成head, content格式
	 * @param  [] $content
	 * @return []    
	 */
	private function encrypt($content)
	{
		//1. aes-key标准化
		$aes_key = $this->generateAesKey();
		$std_key = $this->aesStdKey($aes_key);
		if (!$std_key) {
			Logger::dayLog('9f', 'loanquery', 'aesstdkey', '加密', $aes_key, 'is fail');
			return null;
		}

		//2. 组合请求头
		$jsonHead = [
			'secretKey' => $this->rsa_encrypt($aes_key),
			'isSecret' => 1,
		];

		$jsonHead = json_encode($jsonHead);
		$jsonContent = json_encode($content);

		//3. aes加密
		$data = [];
		$data['jsonHead'] = $jsonHead;
		$data['jsonContent'] = $this->aes_encrypt($std_key, $jsonContent);
		return $data;
	}
	/**
	 * 解析响应结果
	 * @param  String $response json串
	 * @return [res_code, res_data]
	 */
	private function parseQueryResponse($response)
	{
		//$response = $this->getTestResponse();//@todo;
		//1 参数验证
		$response = json_decode($response, true);
		if (!is_array($response) || $response['resp_code'] != '0000') {
			return ['res_code' => $response['resp_code'], 'res_data' => $response['resp_msg']];
		}

		//2 查询成功时处理
		$rsa = $response['resp_aesKey'];
		$content = $response['resp_result'];

		//3 获取标准化aes Key
		$aes_key = $this->rsa_decrypt($rsa);
		$std_key = $this->aesStdKey($aes_key);

		//4 解密
		//$content2 = $this->aes_encrypt($std_key, '[]');
		$json_str = $this->aes_decrypt($std_key, $content);
		$data = json_decode($json_str, true);
		return ['res_code' => 0, 'res_data' => $data];
	}
	// RSA 加解密
	private function rsa_encrypt($paraStr)
	{
		return $this->rsa->encrypt128ByPublic($paraStr, $this->config['rsa_encrypt']);
	}
	private function rsa_decrypt($paramByteArr)
	{
		return $this->rsa->decrypt128ByPrivate($paramByteArr, $this->config['rsa_decrypt']);
	}
	// AES 加解密
	private function aes_encrypt($key, $str)
	{
		$this->aes->set_key($key);
		return $this->aes->encrypt($str);
	}
	private function aes_decrypt($key, $str)
	{
		$this->aes->set_key($key);
		return $this->aes->decrypt($str);
	}
	/**
	 * aes密钥标准化
	 * @param  str $key
	 * @return str 
	 */
	private function aesStdKey($key)
	{
		$soap = new Soap9f($this->env);
		$obj = $soap->localStdKey(['key' => $key]);
		if (!$obj || !is_object($obj) || !isset($obj->return)) {
			return '';
		}
		//print_r($obj->return);exit;
		$str = base64_decode($obj->return);
		return $str;
	}
	/**
	 * 生成随机key
	 * @return string
	 */
	private function generateAesKey()
	{
		return date('ymd') . rand(1000, 9999);
	}
	/**
	 * 测试响应
	 * @return string
	 */
	private function getTestResponse()
	{
		return '{"resp_aesKey":"DYhmYbefwQjvyDZNq1maXwMHeTdmJk7I+biDimcnqVp3Rx3TcOI3Va/r0N8AdrZ4v56mTKu1C6reQUF6HPE7zyIZVSq+Ji9SRwVIWu1tukJ4QfLrWebxvUPUkDjzkwy+mlGqurBf6GOZ16f8VDccarHPghx+ghWmCJWT0dxjDqg=","resp_msg":"成功查询","resp_result":"F/SjbrekAYhrtcGnkvJydQqFBvRh/4jWYDNDTtT1YMLHNdb/Rp+5a0T82LRHIHa8kbRuwM5XSynNZB3a5923EjBEddW/GCC+GbPe1leA9V/Fg8enV8Kge4wfOratQwVooqx0JYFS/JHBZLO5AJntCA0vrsCjtg6waMg7XBmmkbl4b35ZqS+OwDjwQJlM6EpS+L0D2ybtRWIC+h1jF/kNAGZW0baKaW9aux17zAVYAYkpoPcjMi2iXawIA5SUxG4eETVLTmMnxNu0qRHdY/OWjoEYRedgwzgXc7wQNc8r58ZAM8Uhxo+8TJL0o6KsDCtudOhxdCaZDdLgTzlBskqNiSMZmJ6PFtHlefdlmtnLuTPR7iZD0nSvx0nl9bapK/4L7Wqjf+CEK77En08U+FiShEv4CXn3toEDvXnnpixIsxA=","resp_code":"0000"}';
	}
}