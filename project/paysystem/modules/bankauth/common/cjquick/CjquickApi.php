<?php

namespace app\modules\bankauth\common\cjquick;
use app\common\Curl;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\modules\bankauth\common\cjquick\Util;
use Yii;
/**
 * 畅捷通支付类
 */
class CjquickApi {

    private $config;
    private $object;

    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
        $this->object = new Util($this->config);
    }
    
    /**
     * 获取配置文件
     * @param  str $env
     * @param  str $aid
     * @return   []
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
     * Undocumented function
     * 畅捷支付请求接口
     * @param [type] $data
     * @return void
     */
    public function pay($data){
        $postdata = [
            'Service'   => 'nmg_zft_api_quick_payment',//请求接口名称
            'TrxId'     => $data['cli_orderid'],
            'OrdrName'  => $data['productname'],
            //'OrdrDesc'  => $data['productdesc'],
            'MerUserId' => $data['identityid'],
            'SellerId'  => $this->config['PartnerId'],
            'ExpiredTime'  => $data['orderexpdate'].'m',
            'BkAcctTp'  => $data['bankcardtype']==2?'00':'01',//00信用卡01借记卡
            'BkAcctNo'  => $this->object->rsaPublicEncrypt($data['cardno']),
            'IDTp'      => '01',//身份证
            'IDNo'      => $this->object->rsaPublicEncrypt($data['idcard']),
            'CstmrNm'   => $this->object->rsaPublicEncrypt($data['name']),
            'MobNo'     => $this->object->rsaPublicEncrypt($data['phone']),
            'CardCvn2'  => $this->object->rsaPublicEncrypt($data['cvv2']),
            'CardExprDt'=> $this->object->rsaPublicEncrypt($data['expiry_date']),
            'TradeType' => '11',//交易类型11及时12担保
            'TrxAmt'    => $data['amount']/100,
        ];
        return $this->sendRequest($postdata);
    }
    /**
     * Undocumented function
     * 信用卡支付
     * @param [type] $data
     * @return void
     */
    public  function creditPay($data){
        $postdata = [
            'Service'       => 'nmg_ebank_pay',//请求接口名称
            'OutTradeNo'    => $data['cli_orderid'],
            'MchId'         => $this->config['PartnerId'],
            'ChannelType'   => '01',//01wap02web
            'BizType'       => '01',//01个人02企业
            'CardFlag'      => '02',//01借记卡02贷记卡
            'PayFlag'       => '00',//00api接口直连01畅捷收银台
            'ServiceType'   => '01',//01及时02担保
            'TradeType'     => '05',
            'GoodsType'     => '00',//00虚拟01实体
            'Currency'       => '00',
            'OrderStartTime'=> date('YmdHms'),
            'UserIp'        => $data['userip'],
            'NotifyUrl'     => $data['notify_url'],
            //'ReturnUrl'     => $data['return_url'],
            'OrderAmt'      => $data['amount']/100,
            'ExpiredTime'   => $data['orderexpdate'].'m',
            'BankCode'      => 'OTHERBANK'
        ];
        //var_dump($postdata);die;
        $commonParams = $this->getCommonParams();
        $postdata = array_merge($postdata,$commonParams);
        $postdata['Sign'] = $this->object->rsaSign($postdata);
        $url    = $this->config['action_url']; 
        $result = $this->curlGet($url,$postdata);
        return $result;
    }
    /**
     * Undocumented function
     * 畅捷支付确认接口
     * @param [type] $data
     * @return void
     */
    public function confirmPay($data){
        $postdata = [
            'Service'   => 'nmg_api_quick_payment_smsconfirm',//订单确认接口名称
            'TrxId'     => $data['cli_orderid'],
            'OriPayTrxId'  => $data['cli_orderid'],
            'SmsCode'   => $data['smscode']
        ];
        return $this->sendRequest($postdata);
    }
    /**
     * Undocumented function
     * 查询订单
     * @param [type] $data
     * @return void
     */
    public function queryOrder($data){
        $postdata = [
            'Service'       => 'nmg_api_query_trade',
            'TrxId'         => $data['cli_orderid'],
            'OrderTrxId'    => $data['cli_orderid'],
            'TradeType'     => 'pay_order'
        ];
        return $this->sendRequest($postdata);
    }
    /**
     * Undocumented function
     * 短信发送接口
     * @param [type] $data
     * @return void
     */
    public function reSend($data){
        $postdata = [
            'Service'       => 'nmg_api_quick_payment_resend',
            'TrxId'         => $data['cli_orderid'],
            'OriTrxId'      => $data['cli_orderid'],
            'TradeType'     => 'pay_order'
        ];
        return $this->sendRequest($postdata);
    }
    private function sendRequest($postdata){
        $commonParams = $this->getCommonParams();
        $postdata = array_merge($postdata,$commonParams);
        $postdata['Sign'] = $this->object->rsaSign($postdata);
        $url    = $this->config['action_url']; 
        $result = $this->curlGet($url,$postdata);
        $result = json_decode($result,true);
        if($result){          
            $sign = $result['Sign'];
            //var_dump($sign);
             $res = $this->object->rsaVerify($result,$sign);
             if(!$res){
                 Logger::dayLog("cjquick","rsaVerify验签失败", $url, $result);
                 return false;
             }
             
        }
        return $result;
    }
    /**
	 * GET请求接口
	 * @param unknown $url
	 * @param unknown $params
	 * @return unknown|NULL
	 */
	function curlGet($url, $params = array()){
		$curl = new \app\common\Curl();
		$curl -> setOption(CURLOPT_CONNECTTIMEOUT,10);
		$curl -> setOption(CURLOPT_TIMEOUT,10);
        
		$content = $curl -> get($url, $params);
		$status  = $curl -> getStatus();
        $info = $curl->getInfo();
        //var_dump($curl);die;
        Logger::dayLog(
			"cjquick",
			"请求信息",$url,$params,
			"http状态",$status,
            'info',$info,
			"响应内容",$content
			);
		return $content;
	
	}
    /**
     * @desc 提交数据
     * @param string $url
     * @param string $data
     * @return string
     */
    private function HttpClientPost($url,$data) {
        $curl = new \app\common\Curl();
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->setOption(CURLOPT_TIMEOUT, 30);
        $curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setOption(CURLOPT_HTTPHEADER, array(
				"Content-Type: application/text;charset=UTF-8")
                );
        $content = '';
        $content = $curl->post($url, $data);
        $status = $curl->getStatus();
        Logger::dayLog("cjquick","请求信息", $url, $data,"http状态", $status,"响应内容", $content);
        return $content;
    }
    /**
     * Undocumented function
     * 获取公共请求参数
     * @return void
     */
    private function getCommonParams(){
        $commonParams = [
            'Version'   => $this->config['Version'],
            'PartnerId' => $this->config['PartnerId'],
            'InputCharset'  => $this->config['InputCharset'],
            'TradeDate' => date('Ymd'),
            'TradeTime' => date('Hms'),
            'SignType'  => $this->config['SignType']
        ];
        return $commonParams;
    }
    /**
     * Undocumented function
     * 畅捷wap贷记卡异步通知
     * @param [type] $postdata
     * @return void
     */
    public function verify($postdata){
        $sign = $postdata['sign'];
        //var_dump($sign);
        $res = $this->object->rsaVerify($postdata,$sign);
        if(!$res){
            Logger::dayLog("cjback","rsaVerify验签失败",$postdata);
            return false;
        }
        return $res;
    }

    /**
     * 查询卡bin信息
     */
    public function cjCardInfo($cardno) {
        $postdata = [
            'Service'   => 'cjt_dsf',//卡BIN信息查询接口名称
            'TransCode'  => 'C00016',
            'OutTradeNo' => md5(uniqid(mt_rand(), true)),
            'AcctNo'     => $this->object->rsaPublicEncrypt($cardno)
        ];
        return $this->sendRequest($postdata);
    }

    /**
     * 实名认证接口
     */
    public function getAuthCard($data){
        $account_type = ($data['cardType']==2) ? '01' : '00';
        $postdata = [
            'Service'   => 'cjt_dsf',//实名认证信息接口名称
            'TransCode'  => 'T00005',
            'OutTradeNo' => $data['requestid'],
            'BankCommonName' => $data['bankName'],
            'AcctNo'     => $this->object->rsaPublicEncrypt($data['cardno']),
            'AcctName'     => $this->object->rsaPublicEncrypt($data['username']),
            'LiceneceType'  =>  '01',
            'LiceneceNo'     => $this->object->rsaPublicEncrypt($data['idcard']),
            'AccountType' => $account_type,
            'Phone'     => $this->object->rsaPublicEncrypt($data['phone'])
        ];
        return $this->sendRequest($postdata);
    }
}
