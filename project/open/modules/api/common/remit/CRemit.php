<?php
/**
 * 计划任务处理:中信出款流程
 * 这个是中信出款的逻辑类,相当于控制器功能
 * @author lijin
 */
namespace app\modules\api\common\remit;
use app\common\Logger;
use app\models\remit\ApiLog;
use app\models\remit\Bankno;
use app\models\remit\ClientNotify;
use app\models\remit\Remit;
use yii\helpers\ArrayHelper;

set_time_limit(0);

class CRemit {
	/**
	 * 接口类
	 */
	private $oRemitApi;
	/**
	 *
	 */
	private $oBankno;
	/**
	 * 初始化接口
	 */
	public function __construct() {
		$env = YII_ENV_DEV ? 'dev' : 'prod';
		$this->oRemitApi = new RemitApi($env);
		$this->oBankno = new Bankno;
	}

	/**
	 * 暂时五分钟跑一批:
	 * 处理出款
	 */
	public function runRemits() {
		//1 统计1小时剩余的数据
		$initRet = ['total' => 0, 'success' => 0];
		$oApiLog = new ApiLog;
		$restNum = $oApiLog->getRestRemit();
		if (!$restNum) {
			return $initRet;
		}

		//2 一次性处理最大设置为20 约(200/12(60/5分))
		$restNum = $restNum > 20 ? 20 : $restNum;
		$oRemit = new Remit;
		$remitData = $oRemit->getInitData($restNum);
		if (!$remitData) {
			return $initRet;
		}

		//3 锁定状态为出款中
		$ids = ArrayHelper::getColumn($remitData, 'id');
		$ups = $oRemit->lockRemit($ids); // 锁定出款接口的请求
		if (!$ups) {
			return $initRet;
		}

		//4 逐条处理
		$total = count($remitData);
		$success = 0;
		foreach ($remitData as $oRemit) {
			$result = $this->doRemit($oRemit);
			if ($result) {
				$success++;
			} else {
				Logger::dayLog('remit', 'CRemit/runRemits', '处理失败', $oRemit);
			}
		}

		//5 返回结果
		$initRet = ['total' => $total, 'success' => $success];
		return $initRet;
	}
	/**
	 * 处理单条出款
	 * @param object $oRemit
	 * @return bool
	 */
	private function doRemit($oRemit) {
		//1 检测是否是超限的数据
		if (!$oRemit) {
			return false;
		}
		// 重复纪录直接返回
		$isLimit = $oRemit->isTopLimit();
		if ($isLimit) {
			$result = $oRemit->saveRspStatus(Remit::STATUS_FAILURE, "_TOP_LIMIT", "此订单同一天内重复", 1);
			if (!$result) {
				Logger::dayLog('remit', 'CRemit/doRemit', 'Remit/isTopLimit', "出款超限", $oRemit->errors);
			}
			//加入到通知列表中
			$result = $this->addNotify($oRemit);
			return $result;
		}

		//2 组合数据
		$recBankNo = $this->oBankno->getNoByName($oRemit['guest_account_bank']);
		if (!$recBankNo) {
			Logger::dayLog('remit', 'CRemit/doRemit', 'Bankno/getNoByName', $oRemit['guest_account_bank'], '银行号不存在');
			return false;
		}
		if ($oRemit['settle_amount'] <= 0) {
			Logger::dayLog('remit', 'CRemit/doRemit', 'Bankno/getNoByName', $oRemit['settle_amount'], '金额必须大于0');
			return false;
		}
		if ($oRemit['settle_amount'] > 50000) {
			Logger::dayLog('remit', 'CRemit/doRemit', 'Bankno/getNoByName', $oRemit['settle_amount'], '数额太大');
			return false;
		}

		$cityFlag = $this->oRemitApi->getCityFlag($oRemit['guest_account_province']);
		$apiData = [
			// 银行联号
			'recBankNo' => $recBankNo, //收款人支付方式为05(网银跨行支付)时非空
			'clientID' => $oRemit['client_id'], //客户流水号 char(20)
			'preDate' => date('Ymd'), //延期支付日期char(8) 格式YYYYMMDD
			'preTime' => date('H:i'), //延期支付时间char(6) 格式hhmmss

			'recAccountNo' => $oRemit['guest_account'], //收款账号
			'recAccountName' => $oRemit['guest_account_name'], //收款账户名称
			'tranAmount' => $oRemit['settle_amount'], //金额 decimal(15,2)

			//同城标志 0：同城；1：异地
			'cityFlag' => $cityFlag,
			'abstract' => $oRemit['settlement_desc'] ? $oRemit['settlement_desc'] : '委托结算出款', //摘要 varchar(22)
		];

		//4 纪录到请求日志中
		$oApiLog = new ApiLog;
		$res = $oApiLog->saveData($oRemit['id'], $oRemit['remit_status'], 1); // 1表示出款接口
		if (!$res) {
			Logger::dayLog('remit', 'CRemit/doRemit', 'ApiLog/saveData', $oApiLog->attributes, $oApiLog->errors);
			return false;
		}

		//5 提交到接口中
		$response = $this->oRemitApi->remit($apiData);
		$oApiLog->xml = isset($response['xml']) && $response['xml'] ? $response['xml'] : '';
		$ret = $oApiLog->save();

		//6 解析状态响应码
		$oRemitStatus = new RemitStatus;
		$result = $oRemitStatus->parseRemitStatus($response); // 解析状态响应码
		if (!$result) {
			Logger::dayLog('remit', 'CRemit/doRemit', 'RemitStatus/parseRemitStatus', "解析响应失败", $response);
			return false;
		}

		//7.1 保存出款表中
		$result = $oRemit->saveRspStatus($oRemitStatus->remit_status, $oRemitStatus->rsp_status, $oRemitStatus->rsp_status_text, 1);
		if (!$result) {
			Logger::dayLog('remit', 'CRemit/doRemit', 'Remit/saveRspStatus', $oRemit->errors);
			return FALSE;
		}

		//7.2 保存到接口日志中
		$result = $oApiLog->saveRspStatus($oRemit->remit_status, $oRemit->rsp_status, $oRemit->rsp_status_text);
		if (!$result) {
			Logger::dayLog('remit', 'CRemit/doRemit', 'ApiLog/saveRspStatus', $oRemit->errors);
			return FALSE;
		}

		//8 加入到通知列表中
		$result = $this->addNotify($oRemit);
		if (!$result) {
			return FALSE;
		}

		return true;
	}

	/**
	 * 处理查询
	 * 暂定每分钟最多跑10个
	 */
	public function runQuerys() {
		//1 统计1小时剩余的数据
		$initRet = ['total' => 0, 'success' => 0];
		$oApiLog = new ApiLog;
		$restNum = $oApiLog->getRestQuery();
		if (!$restNum) {
			return $initRet;
		}

		//2 一次性处理最大设置为10
		$initRet = ['total' => 0, 'success' => 0];
		$restNum = $restNum > 40 ? 40 : $restNum;
		$oRemit = new Remit;
		$remitData = $oRemit->getDoingData($restNum);
		if (!$remitData) {
			return $initRet;
		}

		//3 锁定状态为查询中
		$ids = ArrayHelper::getColumn($remitData, 'id');
		$ups = $oRemit->lockQuery($ids); // 锁定出款接口的请求
		if (!$ups) {
			return $initRet;
		}

		//4 逐条处理
		$total = count($remitData);
		$success = 0;
		foreach ($remitData as $oRemit) {
			$result = $this->doQuery($oRemit);
			if ($result) {
				$success++;
			} else {
				Logger::dayLog('remit', 'CRemit/runQuerys', '处理失败', $oRemit);
			}
		}

		//5 返回结果
		$initRet = ['total' => $total, 'success' => $success];
		return $initRet;
	}

	/**
	 * 处理单条出款
	 * @param object $oRemit
	 * @return bool
	 */
	private function doQuery($oRemit) {
		//1 参数验证
		if (!$oRemit) {
			return false;
		}
		if (!isset($oRemit['client_id']) || !$oRemit['client_id']) {
			return false;
		}

		//2 纪录到请求日志中
		$oApiLog = new ApiLog;
		$res = $oApiLog->saveData($oRemit['id'], $oRemit['remit_status'], 2); // 2表示查询接口
		if (!$res) {
			Logger::dayLog('remit', 'CRemit/doQuery', 'ApiLog/saveData', $oApiLog->attributes, $oApiLog->errors);
			return false;
		}

		//5 提交到接口中并解析响应结果
		$response = $this->oRemitApi->query($oRemit['client_id']);
		$oApiLog->xml = isset($response['xml']) && $response['xml'] ? $response['xml'] : '';
		$ret = $oApiLog->save();

		//5.1 解析状态响应码
		$oRemitStatus = new RemitStatus;
		$result = $oRemitStatus->parseQueryStatus($response);
		if (!$result) {
			return false;
		}

		//5.2 保存查询表中
		$result = $oRemit->saveRspStatus($oRemitStatus->remit_status, $oRemitStatus->rsp_status, $oRemitStatus->rsp_status_text, 2);
		if (!$result) {
			Logger::dayLog('remit', 'CRemit/doQuery', 'Remit/saveRspStatus', $oRemit->errors);
			return FALSE;
		}

		//5.1 保存到接口日志中
		$result = $oApiLog->saveRspStatus($oRemit->remit_status, $oRemit->rsp_status, $oRemit->rsp_status_text);
		if (!$result) {
			Logger::dayLog('remit', 'CRemit/doQuery', 'ApiLog/saveRspStatus', $oRemit->errors);
			return FALSE;
		}

		//6 加入到通知列表中
		$result = $this->addNotify($oRemit);
		if (!$result) {
			return FALSE;
		}

		return true;
	}

	/**
	 * 加入通知列表中
	 */
	private function addNotify(Remit $oRemit) {
		if (in_array($oRemit['remit_status'], [Remit::STATUS_SUCCESS, Remit::STATUS_FAILURE])) {
			$oClientNotify = new ClientNotify;
			$result = $oClientNotify->saveData($oRemit['id'], $oRemit['remit_status'], $oRemit['rsp_status_text']);
			if (!$result) {
				Logger::dayLog('remit', 'CRemit/addNotify', 'ClientNotify/saveData', $oClientNotify->errors);
				return FALSE;
			}
		}
		return true;
	}

}