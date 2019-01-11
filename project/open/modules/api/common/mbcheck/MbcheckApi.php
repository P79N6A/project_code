<?php
/**
 * 创蓝校验手机空号检测
 * 接口请求层
 * @author 孙瑞
 */
namespace app\modules\api\common\mbcheck;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\mbcheck\MbcheckRequest;
use app\models\mbcheck\MbcheckResult;

class MbcheckApi{
	private $config = [];
	private $retryCode = array('999999','999998');

	/**
	 * 获取配置文件
	 * @return bool
	 */
	private function getConfig(){
		$is_prod = SYSTEM_PROD ? true : false;
		$cfg = $is_prod ? "prod.php" : 'dev.php';
		$configPath = Yii::$app->basePath.'/modules/api/common/mbcheck/config/'.$cfg;
		if (!file_exists($configPath)) {
			return false;
		}
		$config = include $configPath;
		$this->config = $config;
		return true;
	}

	/**
	 * 获取手机号校验结果[写入结果数据库,修改请求状态]
	 * @param array $mobiles 手机号数组 [请求表id=>手机号]
	 * @return array 失败+失败原因 成功+成功条数
	 */
	public function getCheckResult($mobiles){
		// 获取配置文件
		if(!$mobiles || !$this->getConfig()){
			return MbcheckCode::returnCodeArr(105201);
		}
		$oMbcheckRequest = new MbcheckRequest();
		$lock_res = $oMbcheckRequest->lockStatus(array_keys($mobiles));
		if (!$lock_res) {
			Logger::dayLog('mbcheck', 'collect/error 数据加锁失败');
		}
		// 拼接请求数据
		$biz_content = [
			'apiName' => ArrayHelper::getValue($this->config,'apiName',''), // apiName,登录Data平台查看
			'password' => ArrayHelper::getValue($this->config,'password',''), // password,登录Data平台查看
			'mobiles' => implode(',', array_unique($mobiles)), // 要检测的手机号，1-50个，仅支持11位国内号码，每个手机号间用,分割
		];
		// 请求创蓝获取检测结果
		Logger::dayLog('mbcheck', 'MbcheckApi/getCheckResult 创蓝请求数据为:'.json_encode($biz_content));
		$api_url = ArrayHelper::getValue($this->config,'mobileCheckApi');
		$return_data = (new MbcheckNotify())->curlPost($api_url, $biz_content);
		Logger::dayLog('mbcheck', 'collect/logging 创蓝返回数据为:'.$return_data);
		return $this->saveCheckResult($oMbcheckRequest, $mobiles, $return_data);
	}

	/**
	 * 检测返回信息,保存结果信息
	 * @param 请求表对象 $oMbcheckRequest
	 * @param 手机号数组 $mobiles [请求表id=>手机号]
	 * @param 创蓝返回信息 $return_data
	 * @return array 失败+失败原因 成功+成功条数
	 */
	private function saveCheckResult($oMbcheckRequest,$mobiles,$return_data){
		// 创蓝未返回数据或返回数据错误
		$check_res = $this->parseJson($return_data);
		if(!$check_res){
			$oMbcheckRequest->retryStatus(array_keys($mobiles), MbcheckRequest::STATUS_RETRY, '创蓝返回数据格式错误');
			return MbcheckCode::returnCodeArr(105210);
		}
		$check_res_code = (string)ArrayHelper::getValue($check_res, 'resultCode','999999');
		$check_res_msg = (string)ArrayHelper::getValue($check_res, 'resultMsg','返回数据错误');
		// 请求失败/业务异常,需要重试
		if(in_array($check_res_code, $this->retryCode)){
			$oMbcheckRequest->retryStatus(array_keys($mobiles), MbcheckRequest::STATUS_RETRY, $check_res_code.':'.$check_res_msg);
			return MbcheckCode::returnCodeArr(105211);
		}
		// 创蓝检测结果获取失败
		if($check_res_code != '000000'){
			foreach ($mobiles as $requestid=>$mobile){
				$reason = $check_res_code.':'.$check_res_msg;
				$result = (new MbcheckService())->saveRequestStatus($requestid, MbcheckRequest::STATUS_FAILURE, MbcheckRequest::STATUS_FAILURE, $reason);
				if(!$result){
					Logger::dayLog('mbcheck', 'MbcheckApi/saveCheckResult 手机号'.$mobile.':'.MbcheckCode::getCodeMsg(105213));
				}
			}
			return MbcheckCode::returnCodeArr(105214);
		}
		// 保存创蓝检测结果
		$all_res = ArrayHelper::getValue($check_res, 'resultObj',[]);
		$all_save_data = [];
		foreach($all_res as $each_res){
			$mobile = ArrayHelper::getValue($each_res, 'mobile', '');
			if(!$mobile){
				continue;
			}
			$mobile_status = MbcheckCode::getCheckCode(ArrayHelper::getValue($each_res, 'status', MbcheckRequest::STATUS_FAILURE));
			$save_data = [
				'requestid' => array_search($mobile,$mobiles),
				'mobile' => $mobile,
				'mobile_status' => $mobile_status,
				'check_res' => json_encode($each_res,JSON_UNESCAPED_UNICODE),
			];
			$result = (new MbcheckResult())->insertInfo($save_data);
			if($result){
				Logger::dayLog('mbcheck', 'MbcheckApi/saveCheckResult 手机号'.$mobile.':'.MbcheckCode::getCodeMsg(105212));
			}
			$all_save_data[$mobile] = $mobile_status;
		}
		// 保存请求表结果
		$success = 0;
		foreach ($mobiles as $requestid => $mobile){
			$mobile_status = ArrayHelper::getValue($all_save_data, $mobile, MbcheckRequest::STATUS_FAILURE);
			$result = (new MbcheckService())->saveRequestStatus($requestid, MbcheckRequest::STATUS_SUCCESS, $mobile_status, MbcheckCode::getCodeMsg(105215));
			if($result){
				$success++;
			}else{
				Logger::dayLog('mbcheck', 'MbcheckApi/saveCheckResult 手机号'.$mobile.':'.MbcheckCode::getCodeMsg(105213));
			}
		}
		return MbcheckCode::returnCodeArr('0', $success);
	}

	/**
	 * json数据解析
	 * @param json $content json串
	 * @return array 解析后数组
	 */
	private function parseJson($content){
		if(!$content){
			return [];
		}
		$arr = json_decode($content, true);
		$err = json_last_error();
		if($err){
			return [];
		}else{
			return $arr;
		}
	}
}