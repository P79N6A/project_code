<?php 
/**
 * 同盾验证
 * @author gaolian
 */
namespace app\modules\api\common\fraudmetrix;
use app\common\Curl;
use app\common\Logger;
use app\common\Func;
use Yii;

class FraudmetrixApi{
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
	}
	
	
	/**
	 * 同盾接口调用
	 * @param $type 1为借款同盾；2为注册同盾
	 * @param $account_name 用户姓名
	 * @param $account_mobile 手机号码
	 * @param $id_number 身份证号
	 * @param $seq_id 业务订单号
	 * @param $ip_address IP地址
	 * @param $token_id 设备信息的会话标识
	 * @param $ext_school 学校
	 * @param $ext_diploma 学历
	 * @param $ext_start_year 入学年份
	 * @param $card_number 银行卡号
	 * @param $pay_amount 申请提现金额
	 * @param $event_occur_time 申请提现时间
	 * @param $ext_birth_year 出生年
	 * @param $organization 公司
	 * @param $ext_position 职位
	 * 
	 */
	public function riskloan($type, $account_name, $account_mobile, $id_number, $seq_id, $ip_address, $token_id, $ext_school, $ext_diploma, $ext_start_year, $card_number, $pay_amount, $event_occur_time, $ext_birth_year, $organization, $ext_position)
	{	 
		//同盾分配的合作方标示
		$partner_code = $this->config['fraudmetrix_partner_code'];
		//同盾分配的API秘钥
		$secret_key = $this->config['fraudmetrix_secret_key'];
		//事件ID
		if($type == 1){
			$event_id = $this->config['fraudmetrix_loan_event_id'];
		}else{
			$event_id = $this->config['fraudmetrix_register_event_id'];
		}
		//接口请求URL
		$api_url = $this->config['fraudmetrix_api_url'];
		 
		$data = array(
				"partner_code" => $partner_code,
				"secret_key" => $secret_key,
				"event_id" => $event_id,
				"account_name" => $account_name,
				"account_mobile" => $account_mobile,
				"id_number" => $id_number,
				"seq_id" => $seq_id,
				"ip_address" => $ip_address,
				"token_id" => $token_id,//此处填写设备指纹服务的会话标识，和部署设备脚本的token一致
				"ext_school" => $ext_school,
				"ext_diploma" => $ext_diploma,
				"ext_start_year" => $ext_start_year,
				"card_number" => $card_number,
				"pay_amount" => $pay_amount,
				"event_occur_time" => $event_occur_time,
				"ext_birth_year" => $ext_birth_year,
				"organization" => $organization,
				"ext_position" => $ext_position
		);
		
		$result = $this->invoke_fraud_api($data, $api_url);
		$requestid = $account_mobile.'-'.$seq_id;
		$jsonString = json_encode($result);
		$url = $this->saveJson($requestid, $jsonString);
		// 换行符， 如果是通过http访问本页面，则换行为<br/>,
		//         如果是通过命令行执行此脚本，换行符为\n
		$seperator = PHP_SAPI == "cli" ? "\n" : "<br/>";
		 
		//判断返回的结果是够为空，判断seq_id参数是否为空,如果都不为空，则调用命中规则详情接口
		if(!empty($result) && !empty($result['seq_id'])){
			sleep(2);
			//合作方代码，在用户接入时由同盾分配，用于校验
			$hitruledetail_partner_code = $this->config['fraudmetrix_partner_code'];
			//合作方密钥
			$hitruledetail_partner_key = $this->config['hitruledetail_secret_key'];
			//时间戳
			$timestamp = time();
			//事件ID
			$sequence_id = $result['seq_id'];
			$hitruledetail_api_url = $this->config['hitruledetail_api_url'];
			$data = [
					'partner_code'   => $hitruledetail_partner_code,
					'partner_key' => $hitruledetail_partner_key,
					'sequence_id' => $sequence_id
					];
			$ret = $this->curlGet($hitruledetail_api_url, $data);
			$detailurl = str_replace(".json", "_detail.json", $url);
			$this->saveDetail($detailurl, $ret);
			$result['rules'] = json_decode($ret);
			return $result;
		}else{
			return $result;
		}
	
	}
	
	
	/**
	 * 获取数据
	 * @param array $data
	 * @param str2json
	 * @return null
	 */
	private function curlGet($url, $params = array()){
		$curl = new Curl();
		$curl -> setOption(CURLOPT_CONNECTTIMEOUT,10);
		$curl -> setOption(CURLOPT_TIMEOUT,10);
		$content = $curl -> get($url, $params);
		$status  = $curl -> getStatus();
		if( $status == 200 ){
			return $content;
		}else{
			Logger::dayLog(
			"risk",
			"请求信息",$url,$params,
			"http状态",$status,
			"响应内容",$content
			);
			return null;
		}
	
	}
	
	/**
	 * 同盾接口调用
	 * @param unknown $url
	 * @param unknown $data
	 * @param string $hostMap
	 */
	private function invoke_fraud_api(array $params, $api_url, $timeout = 500, $connection_timeout = 500) {
		 
		$options = array(
				CURLOPT_POST => 1,            // 请求方式为POST
				CURLOPT_URL => $api_url,      // 请求URL
				CURLOPT_RETURNTRANSFER => 1,  // 获取请求结果
				// -----------请确保启用以下两行配置------------
				CURLOPT_SSL_VERIFYPEER => 1,  // 验证证书
				CURLOPT_SSL_VERIFYHOST => 2,  // 验证主机名
				// -----------否则会存在被窃听的风险------------
				CURLOPT_POSTFIELDS => http_build_query($params) // 注入接口参数
		);
		$fraudmetrix_url = $this->config['fraudmetrix_cacert_url'];
		if (defined("CURLOPT_TIMEOUT_MS")) {
			$options[CURLOPT_NOSIGNAL] = 1;
			$options[CURLOPT_TIMEOUT_MS] = $timeout;
		} else {
			$options[CURLOPT_TIMEOUT] = ceil($timeout / 1000);
		}
		if (defined("CURLOPT_CONNECTTIMEOUT_MS")) {
			$options[CURLOPT_CONNECTTIMEOUT_MS] = $connection_timeout;
		} else {
			$options[CURLOPT_CONNECTTIMEOUT] = ceil($connection_timeout / 1000);
		}
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		curl_setopt ($ch, CURLOPT_CAINFO, $fraudmetrix_url);
		if(!($response = curl_exec($ch))) {
			// 错误处理，按照同盾接口格式fake调用结果
			return array(
					"success" => false,
					"reason_code" => "000:调用API时发生错误[".curl_error($ch)."]"
			);
		}
		curl_close($ch);
		return json_decode($response, true);
	}
	
	/**
	 * 纪录错误日志
	 * 按月分组
	 * 保存同盾日志
	 */
	public function saveJson($phone, $content) {
		$path = '/ofiles/fraud/' . date('Ym/d/') . $phone . '.json';
		$filePath = Yii::$app->basePath . '/web' . $path;
		Func::makedir(dirname($filePath));
		file_put_contents($filePath, $content);
		return $path;
	}
	
	/**
	 * 纪录错误日志
	 * 按月分组
	 */
	public function saveDetail($path, &$content) {
		$filePath = Yii::$app->basePath . '/web' . $path;
		Func::makedir(dirname($filePath));
		file_put_contents($filePath, $content);
		return $path;
	}
}