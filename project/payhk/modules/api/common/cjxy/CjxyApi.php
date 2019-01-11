<?php
/**
 * 畅捷协议支付接口请求类
 */

namespace app\modules\api\common\cjxy;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Curl;
use app\common\Logger;

class CjxyApi {
	private $config;
	private $object;
	
	public function __construct($cfg) {
		// 获取配置文件
		$this->config = $this->getConfig($cfg);
		$this->object = new Util($this->config);
	}

	/**
	 * 获取配置文件
	 * @param string $cfg 配置文件文件名
	 * @return array
	 */
	private function getConfig($cfg) {
		$configPath = __DIR__ . "/config/{$cfg}.php";
		if (!file_exists($configPath)) {
			throw new \Exception($configPath . "配置文件不存在", 98);
		}
		$config = include $configPath;
		return $config;
	}

	/**
	 * 获取公共请求参数
	 * @return array
	 */
	private function getCommonParams(){
		$commonParams = [
			'Version' => $this->config['Version'],
			'PartnerId' => $this->config['PartnerId'],
			'InputCharset' => $this->config['InputCharset'],
			'TradeDate' => date('Ymd'),
			'TradeTime' => date('Hms'),
			'ReturnUrl' => '',
			'Memo' => '',
			'SignType' => $this->config['SignType'],
		];
		return $commonParams;
	}

	/**
	 * 畅捷支付直接支付请求接口
	 * @param array $data
	 * @return array
	 */
	public function pay($data){
		$postdata = [
			'Service' => $this->config['service_url']['quick_payment'], // 请求的服务项目
			'TrxId' => ArrayHelper::getValue($data, 'cli_orderid', ''), // 商户唯一订单号
			'SellerId' => $this->config['PartnerId'], // 商户号
			'SubMerchantNo' => '', // 子商户号
			'ExpiredTime' => '20m', // 订单有效期
			'MerUserId' => ArrayHelper::getValue($data, 'identityid', ''), // 用户标识
			'BkAcctTp' => ArrayHelper::getValue($data, 'bankcardtype', 0) == 2 ? '00' : '01', // 00信用卡01借记卡
			'BkAcctNo' => $this->object->rsaPublicEncrypt(ArrayHelper::getValue($data, 'cardno', '')), // 银行卡号
			'IDTp' => '01',// 身份证
			'IDNo' => $this->object->rsaPublicEncrypt(ArrayHelper::getValue($data, 'idcard', '')), // 身份证号
			'CstmrNm' => $this->object->rsaPublicEncrypt(ArrayHelper::getValue($data, 'name', '')), // 持卡人姓名
			'MobNo' => $this->object->rsaPublicEncrypt(ArrayHelper::getValue($data, 'phone', '')), // 持卡人手机号
			'CardCvn2' => $this->object->rsaPublicEncrypt(ArrayHelper::getValue($data, 'cvv2', '')), // 信用卡CVV2码
			'CardExprDt'=> $this->object->rsaPublicEncrypt(ArrayHelper::getValue($data, 'expiry_date', '')), // 信用卡有效期
			'TradeType' => '11', //交易类型 11即时 12担保
			'TrxAmt' => ArrayHelper::getValue($data, 'amount', '')/100, //交易金额
			'EnsureAmount' => '', // 担保金额
			'OrdrName' =>  ArrayHelper::getValue($data, 'productname', ''), // 商品名称
			'OrdrDesc' => '', // 商品详情
			'RoyaltyParameters' => '', // 退款分润账号集
			'NotifyUrl' => $data['notify_url'], // 异步通知地址
			'Extension' => '', // 扩展字段
		];
		return $this->sendRequest($postdata);
	}

	/**
	 * 畅捷支付重发短信接口
	 * @param array $data
	 * @return array
	 */
	public function reSend($data){
		$postdata = [
			'Service' => $this->config['service_url']['sms_resend'], // 请求的服务项目
			'TrxId' => ArrayHelper::getValue($data, 'cli_orderid', ''), // 商户唯一订单号
			'OriTrxId' => ArrayHelper::getValue($data, 'cli_orderid', ''), // 商户唯一订单号
			'TradeType' => 'pay_order', // 订单类型
			'Extension' => '', // 扩展字段
		];
		return $this->sendRequest($postdata);
	}

	/**
	 * 畅捷支付确认接口
	 * @param array $data
	 * @return array
	 */
	public function confirmPay($data){
		$postdata = [
			'Service' => $this->config['service_url']['sms_confirm'], // 请求的服务项目
			'TrxId' => ArrayHelper::getValue($data, 'cli_orderid', ''), // 商户唯一订单号
			'OriPayTrxId' => ArrayHelper::getValue($data, 'cli_orderid', ''), // 商户唯一订单号
			'SmsCode' => ArrayHelper::getValue($data, 'smscode', ''), // 验证码
			'Extension' => '', // 扩展字段
		];
		return $this->sendRequest($postdata);
	}
	
	/**
	 * 主动补单查询
	 * @param array $data
	 * @return void
	 */
	public function queryOrder($data){
		$postdata = [
			'Service' => $this->config['service_url']['trade_query'], // 请求的服务项目
			'TrxId' => ArrayHelper::getValue($data, 'cli_orderid', ''), // 商户唯一订单号
			'OrderTrxId' => ArrayHelper::getValue($data, 'cli_orderid', ''), // 商户唯一订单号
			'TradeType' => 'pay_order', // 订单类型
			'Extension' => '', // 扩展字段
			'NotifyUrl' => '', // 回调地址
		];
		return $this->sendRequest($postdata);
	}

	/**
	 * 向畅捷发送数据报文请求
	 * @param array $postdata
	 * @return array
	 */
	private function sendRequest($postdata){
		$commonParams = $this->getCommonParams();
		$postdata = array_merge($postdata,$commonParams);
		$postdata['Sign'] = $this->object->rsaSign($postdata);
		$url = $this->config['action_url'];
		$result = $this->curlGet($url,$postdata);
		$result = json_decode($result,true);
		if($result){
			$sign = $result['Sign'];
			$res = $this->object->rsaVerify($result,$sign);
			if($res){
				return $result;
			}
			Logger::dayLog("cjxy","errorSign/verify畅捷同步返回数据验签失败", $url, $result);
		}
		return [];
	}
	
	/**
	 * GET请求接口
	 * @param string $url
	 * @param array $params
	 * @return string|NULL
	 */
	private function curlGet($url, $params = array()){
		$curl = new Curl();
		$curl -> setOption(CURLOPT_CONNECTTIMEOUT,10);
		$curl -> setOption(CURLOPT_TIMEOUT,10);
		$content = $curl->get($url, $params);
		$status = $curl->getStatus();
		$info = $curl->getInfo();
		Logger::dayLog("cjxy","对畅捷发起请求报文",$url,$params,"http状态",$status,'info',$info,"响应内容",$content);
		return $content;
	}

	/**
	 * 畅捷支付异步通知验签
	 * @param [type] $postdata
	 * @return void
	 */
	public function verify($postdata){
		$sign = $postdata['sign'];
		$res = $this->object->rsaVerify($postdata,$sign);
		if(!$res){
			Logger::dayLog("cjxy","errorSign/verify畅捷异步通知验签失败");
			return false;
		}
		return $res;
	}
}
