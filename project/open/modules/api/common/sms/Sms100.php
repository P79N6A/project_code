<?php 
/**
 * 百分百短信接口
 * @author lijin
 */
namespace app\modules\api\common\sms;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Http;
use app\common\Logger;
use app\common\Xmlparse;

class Sms100{
	public $errinfo;// 错误结果
	private $config1 = [];
	private $config2 = [];
	public function __construct($env){
		
		if($env == 'prod'){
			// 1:触发短信
			$this->config1 = [
				'sname' => 'dlxianhua',//提交账户
				'spwd'  => 'xianhuahua1605',//提交账户的密码
				'scorpid'  => '',//企业代码（扩展号，不确定请赋值空）
				'sprdid'  => '1012812',//产品编号
			];
			// 2:营销短信
			$this->config2 = [
				'sname' => 'dlxianhua',//提交账户
				'spwd'  => 'xianhuahua1605',//提交账户的密码
				'scorpid'  => '',//企业代码（扩展号，不确定请赋值空）
				'sprdid'  => '1012812',	//产品编号
			];
			
		}else{
			// 测试帐号
			// 1:触发短信
			$this->config1 = [
				'sname' => 'dl-wangyahui1',	//提交账户
				'spwd'  => 'wangyh888',	//提交账户的密码
				'scorpid'  => '',//企业代码（扩展号，不确定请赋值空）
				'sprdid'  => '1012888',	//产品编号
			];
			// 2:营销短信
			$this->config2 = [
				'sname' => 'dl-wangyh3222',	//提交账户
				'spwd'  => 'wangyh888',	//提交账户的密码
				'scorpid'  => '',//企业代码（扩展号，不确定请赋值空）
				'sprdid'  => '1012812',	//产品编号
			];
		}
	}
	/**
	 * 发送短信
	 * @param $mobile 手机号
	 * @param $content 内容
	 * @param $smstype: 1:触发短信 2:营销短信
	 * @return bool
	 */
	public function sendSms($mobile, $content, $smstype){
		$content = rawurlencode($content);
		if($smstype == 1){
			$config = $this->config1;
		}elseif($smstype == 2){
			$config = $this->config2;
		}else{
			return $this->returnError(null, "smstype参数不正确" ); 
		}
		
		$target = "http://cf.51welink.com/submitdata/Service.asmx/g_Submit";
		$post_data = "sname={$config['sname']}&spwd={$config['spwd']}&scorpid=&sprdid={$config['sprdid']}&sdst={$mobile}&smsg={$content}";
		$res = $this->dataPost($post_data, $target);
		//@todo 
		/*$res = '<?xml version="1.0" encoding="utf-8"?>
<CSubmitState xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://tempuri.org/">
  <State>0</State>
  <MsgID>1603221004199540102</MsgID>
  <MsgState>提交成功</MsgState>
  <Reserve>0</Reserve>
</CSubmitState>';*/

		$arr = $this->xml2array($res);
		if( is_array($arr) && $arr['State'] == 0 ){
			return $arr;
		}else{
			// 错误处理
			$error = '';
			if( isset($arr['MsgState'] ) ){
				$error = $arr['MsgState']  . $arr['State'];
			}
			return $this->returnError(null, $error );
		}
	}
	/**
	 * 将xml解析成数组
	 */
	private function xml2array( &$resxml ){
		$xmlParse = new Xmlparse(true);
		$arr = $xmlParse -> parse($resxml);
		if( !is_array($arr) || empty($arr) ){
			return null;
		}
		return $arr;
	}
	/**
	 * 100提交接口
	 */
	public function dataPost($data, $target) {
	    $url_info = parse_url($target);
	    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
	    $httpheader .= "Host:" . $url_info['host'] . "\r\n";
	    $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
	    $httpheader .= "Content-Length:" . strlen($data) . "\r\n";
	    $httpheader .= "Connection:close\r\n\r\n";
	    //$httpheader .= "Connection:Keep-Alive\r\n\r\n";
	    $httpheader .= $data;
	
	    $fd = fsockopen($url_info['host'], 80);
	    fwrite($fd, $httpheader);
	    $gets = "";
	    while(!feof($fd)) {
	        $gets .= fread($fd, 128);
	    }
	    fclose($fd);
	    if($gets != ''){
	        $start = strpos($gets, '<?xml');
	        if($start > 0) {
	            $gets = substr($gets, $start);
	        }        
	    }
	    return $gets;
	}
	/**
	 * 返回错误信息
	 */
	public function returnError($result, $errinfo){
		$this->errinfo = $errinfo;
		return $result;
	}
}