<?php
/**
 * 创蓝校验手机空号检测
 * 异步通知层
 * @author 孙瑞
 */
namespace app\modules\api\common\mbcheck;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\mbcheck\MbcheckRequest;
use app\models\mbcheck\MbcheckNotify AS MbcheckNotifyModel;

class MbcheckNotify {
	private $oMbcheckNotify;

	public function __construct() {
		$this->oMbcheckNotify = new MbcheckNotifyModel;
	}

	/**
	 * 执行单条通知
	 * @param int $requestid 请求表id
	 * @return boolean 通知是否成功
	 */
	public  function runOne($requestid) {
		//根据请求id去查询通知表 然后开始通知
		$one_data = $this->oMbcheckNotify->getOne($requestid, 'requestid');
		if (!$one_data) {
			Logger::dayLog('mbcheck', 'MbcheckNotify/runOne 请求id为'.$requestid.'的通知数据获取失败');
			return false;
		}
		$lock_res = $this->oMbcheckNotify->lockNotify([$one_data->id]);
		if (!$lock_res) {
			Logger::dayLog('mbcheck', 'MbcheckNotify/runOne 请求id为'.$requestid.'的通知数据加锁失败');
			return false;
		}
		return $this->doNotify($one_data->attributes);
	}

	/**
	 * 获取需要批量通知的列表
	 * @return boolean 通知是否成功
	 */
	public  function runAll($start_time, $end_time) {
		$data_list = $this->oMbcheckNotify->getNotifyList($start_time, $end_time);
		return $this->runNotify($data_list);
	}

	/**
	 * 执行批量通知
	 * @param type $data_list 需要通知的数据列表
	 * @return int 通知成功条数
	 */
	private function runNotify($data_list) {
		if (!$data_list) {
			Logger::dayLog('mbcheck', 'MbcheckNotify/runNotify 未获取到需要通知的数据列表');
			return 0;
		}
		// 锁定状态为通知中
		$ids = ArrayHelper::getColumn($data_list, 'id');
		$lock_res = $this->oMbcheckNotify->lockNotify($ids);
		if (!$lock_res) {
			Logger::dayLog('mbcheck', 'MbcheckNotify/runNotify 数据加锁失败');
			return 0;
		}
		// 循环执行通知任务
		$num = 0;
		foreach ($data_list as $oEachNotify) {
			$result = $this->doNotify($oEachNotify->attributes);
			if (!$result) {
				continue;
			}
			$num++;
		}
		logger::dayLog('Mbcheck','MbcheckNotify/runNotify 数据通知成功条数:'.$num.' 成功数据ID为:'.json_encode($ids));
		return $num;
	}

	/**
	 *  执行通知操作
	 * @param array $notify_data 通知表单条数据数组
	 * @return boolean T=>成功 F=>失败
	 */
	private function doNotify($notify_data) {
		$requestid = ArrayHelper::getValue($notify_data, 'requestid');
		$notifyid = ArrayHelper::getValue($notify_data, 'id');
		if(!$requestid || !$notifyid){
			return false;
		}
		// 获取回调地址
		$oRequest = (new MbcheckRequest())->getOne($requestid);
		if (!$oRequest || !$oRequest->callback_url) {
			$this->oMbcheckNotify->changeNotifyStatus($notifyid, MbcheckNotifyModel::STATUS_FAILURE, '没有回调地址');
			return false;
		}
		// 开始通知业务端
		Logger::dayLog('mbcheck', 'MbcheckNotify/doNotify 请求id为'.$requestid.'的检测结果通知业务端');
		$data = [
			'request_id' => $oRequest->id,
			'mobile' => $oRequest->mobile,
			'status' => $oRequest->request_status,
			'msg' => MbcheckCode::getStatusMsg($oRequest->request_status)
		];
		// 加密通知数据
		$encrypt_data = $this->encryptData($oRequest->aid, $data);
		if(!$encrypt_data){
			$this->oMbcheckNotify->changeNotifyStatus($notifyid, MbcheckNotifyModel::STATUS_FAILURE, '加密通知数据失败');
			return false;
		}
		// 发送通知数据
		$response = $this->curlPost($oRequest->callback_url, $encrypt_data);
		Logger::dayLog('mbcheck', 'MbcheckNotify/doNotify 请求id为'.$requestid.'通知业务端后的返回数据'.$response);
		// 不成功设置重新通知
		if ($response != 'SUCCESS') {
			$this->oMbcheckNotify->changeNotifyStatus($notifyid, MbcheckNotifyModel::STATUS_RETRY, !$response?'无响应':$response);
			return false;
		}
		// 成功修改通知状态
		$result = $this->oMbcheckNotify->changeNotifyStatus($notifyid, MbcheckNotifyModel::STATUS_SUCCESS, $response);
		// 通知状态修改失败设置重新通知
		if (!$result) {
			$this->oSlbankNotify->changeNotifyStatus($notifyid, SlbankNotifyModel::STATUS_RETRY, '通知状态修改失败');
			return false;
		}
		Logger::dayLog('mbcheck', 'MbcheckNotify/doNotify 请求id为'.$requestid.'的通知发送成功');
		return true;
	}

	/**
	 * 加密
	 * @param int $aid 应用id
	 * @param string $data 数据
	 * @return array
	 */
	private function encryptData($aid, $data){
		// 加密信息
		try{
			$encrypt_data =  \app\models\App::model()->encryptData($aid, $data);
			return [ 'res_data' => $encrypt_data, 'res_code'=> 0];
		}catch(\Exception $e){
			return [];
		}
	}

	/**
	 * 提交数据
	 * @param string $url 请求地址
	 * @param array $data 请求数据
	 * @return string
	 */
	public function curlPost($url, $data) {
		$curl = new \app\common\Curl();
		$curl->setOption(CURLOPT_CONNECTTIMEOUT, 20);
		$curl->setOption(CURLOPT_TIMEOUT, 20);
		$res = $curl->post($url, $data);
		return $res;
	}
}