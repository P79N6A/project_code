<?php 
/**
 * 天行数科银行卡四要素验证接口
 * 此接口不区分生产环境和测试环境
 * @author lijin
 */
namespace app\modules\api\common\bank;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Http;
use app\common\Logger;

class Bank4{
	public $errinfo;// 错误结果
	
	private $tokenurl;
	private $bankurl;
	private $account;
	private $signature;
	
	private $cacheKey="bank4_token";
	
	public function __construct(){
		$this->tokenurl = "http://tianxingshuke.com/api/rest/common/organization/auth";
		$this->bankurl  = "http://tianxingshuke.com/api/rest/unionpay/auth/4element";
		$this->account = "xhh";
		$this->signature = "2788b38a36ca419c90ffa15543915fa3";
	}
	/**
	 * 获取token值
	 * 有缓存 , 24小时有效
	 */
	public function getToken(){
		//1 从缓存中获取
		$tk = Yii::$app -> cache -> get($this->cacheKey);
		if( is_array($tk) && isset($tk['expireTime']) && $tk['expireTime'] > time() ){
			return $tk['token'];
		}
		
		//2 获取接口数据
		$result = $this-> getApiToken();
		if(!$result){
			return $this->returnError('', "TOKEN_NO_RESPONSE");
		}
		
		//3 解析json
		$res = json_decode($result, true);
		if( !isset($res['success']) || !$res['success'] ){
			$error = isset($res['errorDesc']) ? $res['errorDesc'] : 'TOKEN_GET_FAIL';
			return $this->returnError('', $error);
		}
		
		//4 过期时间
		$token = ArrayHelper::getValue($res, "data.accessToken");
		$expireTime  = ArrayHelper::getValue($res, "data.expireTime");
		
		//5 放入缓存中
		$expireTime = intval($expireTime / 1000); //转成s
		Yii::$app -> cache -> set($this->cacheKey, ['token'=>$token,'expireTime'=>$expireTime]);
		
		//6 返回token
		return $token;
	}
	/**
	 * 获取token值
	 */
	private function getApiToken(){
		//@todo
		//$result = '{"success":true,"data":{"id":"cecf6658ed3041a1aa52af8f44ae1b49","account":"xhh","accessToken":"110b1bc710c34b52a872fa3c4c218fe0","expireTime":1454570526000}}';
		//return $result;
		
		$data = [
			'account'   => $this->account,
			'signature' => $this->signature,
		];
    	$res = Http::interface_post($this->tokenurl, http_build_query($data));
		return $res;
	}
	/**
	 * 检测四要素的状态
	 * @param $data
	 * @return string
	 */
	public function chk($data){
		//1 从接口中获取数据
		$result = $this->getApiBank($data);
        Logger::dayLog('bankvalid', '请求数据', $data, '天行返回', $result);
		if(!$result){
			//Logger::dayLog("bank4",'getapibank','QUERY_NO_RESPONSE',$this->errinfo);
			return $this->returnError(FALSE, "服务无响应,请稍后再试");
		}
		
		//2 解析json
		$res = json_decode($result, true);
		if( !isset($res['success']) || !$res['success'] ){
			$error = isset($res['error']) ? $res['error'] : "查询失败";
			return $this->returnError(FALSE, $error);
		}
		
		//3 获取结果字符串
		$r = ArrayHelper::getValue($res, "data.checkStatus");
		if( $r == 'SAME' ){
			return true;
		}else{
			$err = ArrayHelper::getValue($res, "data.result");
			return $this->returnError(FALSE, $r.'|'.$err);
		}
	}
	/**
	 * @param $data
	 * @return string json
	 */
	private function getApiBank($data){
		// @todo
		//$result = '{"success":true,"data":{"name":"黄鸿婕","identityCard":"350629199409150028","accountNo":"6217001850008924194","bankPreMobile":"13850553698","result":"认证信息匹配"}}';
		//return $result;
		
		//1 获取 token 数据
		$token = $this->getToken();
		if( !$token ){
			return $this->returnError(null, "token 获取失败:".$this->errinfo);
		}
		
		//2 组合参数并返回结果
		$queryData = [
			'account'   => $this->account,
			'accessToken' => $token,
			'name'  => $data['username'],
			'idCard' => $data['idcard'],
			'accountNO' => $data['cardno'],
			'bankPreMobile' => $data['phone'],
		];
		$url = $this->bankurl . '?' . http_build_query($queryData);
		//$url = $this->bankurl . "?account={$queryData['account']}&accssToken={$queryData['accssToken']}&name={$queryData['name']}&idCard={$queryData['idCard']}&accountNO={$queryData['accountNO']}&bankPreMobile={$queryData['bankPreMobile']}";
    	$res = Http::getCurl($url);
		if( !$res ){
			Logger::dayLog("bank4",'error', "4element 获取失败",$url);
			return $this->returnError(null, "4element 获取失败");
		}
    	return $res;
	}
	/**
	 * 返回错误信息
	 */
	public function returnError($result, $errinfo){
		$this->errinfo = $errinfo;
		return $result;
	}
	/**
	 * 解析错误信息,由|分隔
	 */
	public function parseError($err){
		$errs = explode('|',$err);
		$errmsg =  isset($errs[1]) && $errs[1] ? $errs[1] : $errs[0];
		/*if(preg_match ("/^[a-zA-Z]*$/",$errmsg)){
			return '验证失败';
		}*/
		return $errmsg;
	}
}