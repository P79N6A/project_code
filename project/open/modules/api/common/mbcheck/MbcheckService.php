<?php
/**
 * 创蓝校验手机空号检测
 * 逻辑处理层
 * @author 孙瑞
 */
namespace app\modules\api\common\mbcheck;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;

use app\models\mbcheck\MbcheckRequest;
use app\models\mbcheck\MbcheckResult;
use app\models\mbcheck\MbcheckNotify as MbcheckNotifyModel;

class MbcheckService{

	private $expiryTime = 7776000; // 90*24*60*60 有效期90天

	/**
	 * 手机号列表的数据处理状态
	 * @param array $mobiles 手机号列表数组
	 * @param int $aid 请求应用id
	 * @param string $callback_url 回调地址
	 * @param boolean $is_single 处理方式  T=>单条请求保存并请求创蓝返回同步结果 F=>多条请求仅保存等待计划任务调用
	 * @return array 0+检验结果/成功 | 失败码+失败原因
	 */
	public function saveMobiles($mobiles, $aid, $callback_url, $is_single){
		if(!$mobiles || !$aid || !$callback_url){
			return MbcheckCode::returnCodeArr(105201);
		}
		$request_data['aid'] = $aid;
		$request_data['callback_url'] = $callback_url;
		$request_data['batch_no'] = (new MbcheckRequest())->getNewBatch();
		// 单条请求保存并请求创蓝返回同步结果
		if($is_single){
			// 限制一分钟只能请求一次
			$mobile = ArrayHelper::getValue($mobiles,'0');
			$over_max = (new MbcheckRequest())->isOverMaxRequest($mobile, 60, 1);
			if(!$over_max){
				Logger::dayLog('mbcheck', 'MbcheckService/saveMobiles 手机号'.$mobile.'请求过于频繁');
				return MbcheckCode::returnCodeArr(105202);
			}
			return $this->saveSingleMobile($mobile, $request_data);
		}
		// 多条请求仅保存等待计划任务调用
		$total = $save = count($mobiles);
		$error = '';
		foreach($mobiles as $each_mobile){
			$requestid = $this->doInsertRequest($each_mobile, $request_data);
			if(!$requestid){
				$error .= ','.$each_mobile;
				$save -= 1;
			}
		}
		Logger::dayLog('mbcheck','MbcheckService/saveMobiles 批量请求数据共 '.$total.' 条结果,成功 '.$save.' 条,失败数据为:'.$error);
		if($total != $save){
			return MbcheckCode::returnCodeArr(105205);
		}
		return MbcheckCode::returnCodeArr('0', ['reason' => MbcheckCode::getCodeMsg(105206)]);
	}

	/**
	 * 保存单条手机号数据并获取结果
	 * @param string $mobile 手机号
	 * @param array $request_data 请求表保存数据
	 * @return array 成功返回检测结果|失败返回错误码+错误信息
	 */
	private function saveSingleMobile($mobile, $request_data){
		$requestid = $this->doInsertRequest($mobile, $request_data);
		if(!$requestid){
			return MbcheckCode::returnCodeArr(105205);
		}
		return $this->getSingleRes($mobile,$requestid, TRUE);
	}

	/**
	 * 获取历史结果并校验有效期
	 * @param string $mobile 手机号
	 * @param int $requestid 请求表id
	 * @param boolean $is_single 处理方式  T=>单条请求保存并请求创蓝返回同步结果 F=>多条请求仅校验是否有历史数据
	 * @return array 单条返回检测结果,多条返回保存成功|失败返回请求表id+手机号
	 */
	private function getSingleRes($mobile, $requestid, $is_single){
		$oMbcheckResult = new MbcheckResult();
		$oResultRow = $oMbcheckResult->getOne($mobile, 'mobile', 'create_time desc');
		if($oResultRow){
			// 判断已获得的数据有效期是否超过有效时间
			$now_time = time();
			$modify_time = strtotime(ArrayHelper::getValue($oResultRow,'modify_time'));
			if(($modify_time + $this->expiryTime) > $now_time){
				Logger::dayLog('mbcheck', 'MbcheckService/getSingleRes 手机号'.$mobile.'历史数据未过期继续使用');
				$mobile_status = ArrayHelper::getValue($oResultRow, 'mobile_status');
				$this->saveRequestStatus($requestid, MbcheckRequest::STATUS_SUCCESS, $mobile_status, '从历史结果中读取');
				$result = [
					'request_id' => $requestid,
					'mobile' => $mobile,
					'status' => $mobile_status,
					'msg' => MbcheckCode::getStatusMsg($mobile_status)
				];
				return MbcheckCode::returnCodeArr('0', $result);
			}
			unset($oResultRow);
		}
		// 批量抓取时只做校验,有则异步通知,没有则返回>0的code码
		if(!$is_single){
			return MbcheckCode::returnCodeArr($requestid, $mobile);
		}
		Logger::dayLog('mbcheck', 'MbcheckService/getSingleRes 手机号'.$mobile.'是单个请求需要直接获取结果');
		$check_res = (new MbcheckApi())->getCheckResult([$requestid => $mobile]);
		$check_res_code = ArrayHelper::getValue($check_res, 'code', 105201);
		if($check_res_code){
			Logger::dayLog('mbcheck', 'MbcheckService/getSingleRes 手机号'.$mobile.'获取创蓝校验结果失败');
			return $check_res;
		}
		$oResultRow = $oMbcheckResult->getOne($mobile, 'mobile', 'create_time desc');
		$mobile_status = ArrayHelper::getValue($oResultRow, 'mobile_status');
		$result = [
			'request_id' => $requestid,
			'mobile' => $mobile,
			'status' => $mobile_status,
			'msg' => MbcheckCode::getStatusMsg($mobile_status)
		];
		return MbcheckCode::returnCodeArr('0', $result);
	}

	/**
	 * 执行请求数据插入操作
	 * @param string $mobile 手机号
	 * @param array $request_data 请求数据
	 * @return int
	 */
	private function doInsertRequest($mobile, $request_data){
		$request_data['mobile'] = $mobile;
		$insert_id = (new MbcheckRequest())->insertRequestInfo($request_data);
		if($insert_id){
			return $insert_id;
		}
		Logger::dayLog('mbcheck','MbcheckService/doInsertRequest 保存请求数据错误,手机号为:'.$mobile);
		return 0;
	}

	/**
	 * 修改请求表状态 新建通知表数据 发送通知
	 * @param int $requestid 请求表id
	 * @param int $request_status 请求表状态
	 * @param int $mobile_status 手机号检测结果
	 * @param int $reason 创蓝返回信息
	 * @return boolean T=>成功 F=>失败
	 */
	public function saveRequestStatus($requestid, $request_status, $mobile_status, $reason){
		$change_result = (new MbcheckRequest())->changeRequestStatus($requestid, $request_status, $mobile_status, $reason);
		if(!$change_result){
			Logger::dayLog('mbcheck', 'MbcheckService/saveRequestStatus 请求id为'.$requestid.'的表状态修改失败');
			return false;
		}
		$insert_result = (new MbcheckNotifyModel())->addNotify($requestid, $request_status);
		if(!$insert_result){
			Logger::dayLog('mbcheck', 'MbcheckService/saveRequestStatus 请求id为'.$requestid.'的通知表信息添加失败');
			return false;
		}
		(new MbcheckNotify())->runOne($requestid);
		return true;
	}

	/**
	 * 获取需要采集的数据
	 * @param string $start_time 开始时间
	 * @param string $end_time 结束时间
	 * @return int 成功条数
	 */
	public function runAll($start_time, $end_time) {
		$data_list = (new MbcheckRequest())->getProcessList($start_time, $end_time, 50);
		return $this->runCollection($data_list);
	}

	/**
	 * 执行采集操作
	 * @param array $data_list 需要采集的数据列表
	 * @return int 成功条数
	 */
	private function runCollection($data_list) {
		if(!$data_list){
			Logger::dayLog('mbcheck', 'MbcheckService/runCollection 未获取到需要采集的数据列表');
			return 0;
		}
		$mobiles = [];
		foreach ($data_list as $oRequsetRow){
			$single_res = $this->getSingleRes($oRequsetRow->mobile, $oRequsetRow->id, false);
			$is_new = ArrayHelper::getValue($single_res, 'code');
			if(!$is_new){
				Logger::dayLog('mbcheck', 'MbcheckService/runCollection 手机号'.$oRequsetRow->mobile.'存在未过期历史数据,跳过');
				continue;
			}
			$mobiles[$oRequsetRow->id] = $oRequsetRow->mobile;
		}
		if(count($mobiles) < 1){
			Logger::dayLog('mbcheck', 'MbcheckService/runCollection 需要采集的数据列表为空');
			return 0;
		}
		$check_res = (new MbcheckApi())->getCheckResult($mobiles);
		$check_res_code = ArrayHelper::getValue($check_res, 'code', 105201);
		$check_res_data = ArrayHelper::getValue($check_res, 'data', '0');
		if($check_res_code){
			return 0;
		}
		Logger::dayLog('mbcheck', 'MbcheckService/runCollection 采集成功条数为'.intval($check_res_data));
		return intval($check_res_data);
	}
}
?>