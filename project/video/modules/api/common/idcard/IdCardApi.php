<?php 
/**
 * 马上金融身份验证
 * @author lijin
 */
namespace app\modules\api\common\idcard;
use app\common\RSA;

class IdCardApi{
	private $rsa;
	public $errinfo;// 错误结果
	private $config;
	
	public function __construct($env){
		/**
		 * 账号配置文件
		 */
		$configPath = __DIR__ . "/config.{$env}.php";
		if( !file_exists($configPath) ){
			throw new \Exception($configPath."配置文件不存在",6000);
		}
		$this->config = include( $configPath );

		$this->rsa = new RSA();
		$this->rsa -> pad0 = false; // 分段加密时不足128位时不补\0
	}
	
	
	///////// start RSA 加解密  /////////
	public function encrypt($paraStr){
		return $this->rsa -> encrypt128ByPublic($paraStr, $this->config['msxf_credit_public_key'], 'bin');
	}
	/**
	 * 从request的字节流中取出加密后的字节数组，解密后返回参数字符串
	 * 用于处理回调notify_url的请求
	 * @param binary $paramByteArr 返回结果的二进制流
	 * @return string
	 */
	public function decrypt($paramByteArr){
		return $this -> rsa -> decrypt128ByPrivate($paramByteArr, $this->config['private_key'], 'bin');
	}
	// 验签与签名
	public function sign( $data ){
		return $this->rsa ->  sign($this->toHex($data), $this->config['private_key']);
	}
	public function verify( $data, $sign ){
		return $this->rsa -> verify( $this->toHex($data), $sign, $this->config['msxf_credit_public_key']);
	}
	//end RSA
	
	
	/**
	 * 根据姓名，身份证获取学籍信息
	 * @param $name 姓名
	 * @param $idcard 身份证
	 * @return []
	 */
	public function get($username, $idCard, $partnerTradeNo){
		//1 加密参数
		$content = $this ->encryptParams($this->config['msxf_credit_product_pf_antifo'], 
														$username, 
														$idCard,
														$partnerTradeNo, 
														$this->config['notify_url']);
		if( empty($content) ){
			return $this->returnError(null, "加密失败");
		}
		
		//2 获取响应 @todo
		//$content = base64_decode("BR5XghaWI90GAeKYkCKdSE3pw09ZWv1myxdxiIz0a4DPi0cXhpzU2pw1N85PHkfDasmIAQzPJB1s2Kf3lW3Qyysy9GDkB62lPOplUX10Sm7p5ZBHtw3Fof4mTjPjnQ0CRT2pBx9pR9EGqWhU/FsQhkls92YseQ+nzG4d0/ddziNbj58r0eBmwVqOVWWYQSQSwDz7wbtrjIcanqrx/nVz02k57qaFl3XYLl7e7fAjlMzbHn2ZvjfvTCuv5hyXvHJR11xzSztuFpe1r1EplrC34W30kWqhmvhSqYU8srdPtjwwYlIaMAwp66NWK+Jkrrx9kvArb6ROhydhWL6jw3I3YhPOdedhHECEp7MajDMxfxCjrWu2IR6EAnSKXxSymoJtC5SeL5c+2/ELY1yC7LY5fP4gzOo+tXaGk/jgXnEo5ma27Zf9hFtzThk6pcQff5VJC1T/nKjRC9oidUgdB4kbWLWjtTrbtoXVkKP9/bEK8EdWNUjY6W07kBg5tiRp3rScKhOotfcP0Z+rR3qMQ0GMkkInwq7KuJYiZ7HoCqJGPHBxRAkyxb/6BKvmThR8q6BSVYrM8vy+soqEE6CVjXEL5PNPPPcZuzIR/fKF4aYaafpF1W64FlZbuFfQX9iURCIS/hNSql19mm7QYNE3Ja4G0EMTWlZWGXx0Czfr8BbF58UKaNjjGMu8LBwwF1ANAH+BPo1kUNIjCaRPqz0rR0vhlDNFE10FirPGf/RQRwhlx5JM0+DFj1a9sF60w1uAXbyYIZkeom4yMmXNX7jYRpz1T790PUpHZxadDZDmP8RZXjs3ZF7Chem0Aeidd/jKTY7Y0OG4z0IxMhXTSFhL9jhjEw==");
		$resContent = $this->post($this->config['msxf_gateway_url'], $content);
		//$resContent = "iGjHyIFUOqZBu55IHy5US1SamItj80p4kxlxQ8krU6ZlDUwDPq9v+1jfiaLBOQfyzJzFo5Kasi0iQhuboeGB/oMkrNEDJZEJCgZ97qeTbqJt30kayn9b1Us5+QSyGXkK0tFGCPZ8s/McmFwHjJvQWxxQiAbVBfi/hltPzeNtuUV3men3ZpcN9z/XMsZ+UnB3PlK6MQovtU2RY7hAo4q5VO2osNlyxPnuzz3kiV0Q2UskTmQzLRETiYVdIfviNcJDB09LzGJEYwMVwT21LzqBIY7Sy0NP5DVJbBzTzs8Xty89B1XC2VHj9tSC7t3a2ylSgHtkXZva03m7YwJOZcqVWXlkOE0BoeIwi+gWLLd2sRieQ36BuPM3KCM/3aLBsjfb2iCyhGJV6O0dCudy0we7lymgY1YaG1HFwKfIoIkl9nXjJP3So02JgP2K4bbewXKaoFLa3iXDAoh5hy0thauGTW/be0P1l0FmrPaR5nCMRCI6uw9M3/EqGTZkMcUSInqS";
		//$resContent = base64_decode($resContent);
		
		//3 解析响应 : 解密 验签 
		$arr =  $this->parseResult( $resContent );
		return $arr;
	}
	/**
	 * 返回错误信息
	 */
	public function returnError($result, $errinfo){
		$this->errinfo = $errinfo;
		return $result;
	}

	/**
	 * 处理请求参数：添加协议字段，签名并加密
	 * @param $productCode 商品编码
	 * @param $username 身份证
	 * @param $idCard 身份证
	 * @param $partnerTradeNo 客户订单号
	 * @param $notifyURL 回调地址
	 * @return 二进制内容
	 */
	private function encryptParams($productCode, 
														$username, 
														$idCard,
														$partnerTradeNo, 
														$notifyURL){
		// 难道这个是字典序,其它的不是
	    $data = [
			'id_card' => $idCard,
			'notify_url' => $notifyURL,
			'partner_trade_no' => $partnerTradeNo,
			'product_code' =>$productCode,
			'username' => $username,
	    ];
		$business_data = json_encode($data);

		// 组合参数 , 这他妈的也不是字典序啊
		$paraStr = "method=" .$this->config['method'] .
						  "&version=" .$this->config['msxf_credit_version'] .
						  "&partner=".$this->config['partner'].
						  "&sign_type=".$this->config['sign_type'].
						  "&charset=".$this->config['charset'].
						  "&business_data=".$business_data;
		
		// 进行签名操作 
		$sign = $this ->  sign($paraStr);
		$paraStr = $paraStr . "&sign=". $sign;

		// 进行加密操作
		return $this->encrypt($paraStr);
	}											 
	/**
	 * 向服务端发送请求
	 * @param $content
	 * @return ILLEGAL_PARTNER |  缺少partner参数 | is_success=F
	 */
	private function post($url, $content, $timeout=15){
		// @todo
//		return "ILLEGAL_PARTNER";
//		return "缺少partner参数";
//		return "is_success=F";
		//return 'is_success=T&sign_type=RSA&business_data={"trade_no":" R2015110142342342323"," partner_trade_no":" 20151010XXXX"}&sign=b23ffwe4efsdv2342sdfsr32hg676861qewqweq';
		
		$header = [];
		$header[]="Content-Type: application/x-www-form-urlencoded";
		$header[]="Connection: keep-alive";
		$header[]="Content-Length: ".strlen($content);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}
	/**
	 * 解析响应结果
	 * @param $str
	 * @return null | []
	 */
	private function parseResult($resContent){
		//1 解密返回数据
		if($resContent===false){
			return $this->returnError(null, "timeout"); // 这个表示超时引起的
		}
		$resStr = $this->decrypt($resContent);
		
		//2 验签
		$arr = explode("&sign=", $resStr);
		$ok = $this-> verify( $arr[0], $arr[1] );
		if(!$ok){
			return $this->returnError(null, "验名失败");
		}

		//3 解析结果
		$res = [];
		parse_str($resStr, $res);
		$is_success = isset($res['is_success']) ? $res['is_success'] : null;
		if( !$is_success ){
			return $this->returnError(null, "响应参数不正确");
		}
		
		//4 判断是否有错误信息
		if( $is_success == 'T' ){
			if( isset($res['error']) ){
				return $this->returnError(null,  $res['error']);
			}
			// 正确结果
			$business_data = json_decode($res['business_data'],true);
			/*[trade_no;  //征信平台的交易号
				partner_trade_no;  //客户的交易号]*/
			return $business_data;
		}else{
			// 错误结果
			$error = isset($res['error']) ? $res['error'] : "申请征信数据失败";
			return $this->returnError(null, $error);
		}
	}
	/**
	 * 转成16进制格式并且大写
	 * @param $data
	 * @return string 16进制大写数字串
	 */
	public function toHex($data){
		return strtoupper(bin2hex($data));
	}
}