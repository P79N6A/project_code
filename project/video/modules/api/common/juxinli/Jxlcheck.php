<?php
namespace app\modules\api\common\juxinli;
use app\common\Logger;
use app\models\JxlRequestModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 聚信立报告和详情检查程序
 */
class Jxlcheck {

	/**
	 * 检查每一项
	 */
	public function chkValid($model) {
		$valid = 0;

		// 报告是否ok
		$report_path = Yii::$app->basePath . '/web' . $model['url'];
		$report_valid = $this->reportChk($model['requestid'], $report_path);
		if ($report_valid) {
			$valid += 1;
		}

		// 详情是否ok
		$detail_path = Yii::$app->basePath . '/web' . str_replace(".json", "_detail.json", $model['url']);
		$detail_valid = $this->detailChk($model['requestid'], $detail_path);
		if ($detail_valid) {
			$valid += 2;
		}

		return $valid;
	}
	/**
	 * 检测报告是否正确
	 * @param  [type] $requestid
	 * @param  [type] $url
	 * @return [type]
	 */
	public function reportChk($requestid, $report_path) {
		// 1 读取并解析json
		$report = $this->getFromFile($report_path);
		if (is_array($report)) {
			//进行检查
			$isok = $this->reportIsValid($report);
			if ($isok) {
				return true;
			}
		}

		// 2 若结果为空那么重新获取并再次检查
		$report = (new JxlRequestModel)->getApiReport($requestid);
		$isok = $this->reportIsValid($report);
		Logger::dayLog('jxlcheck', 'report', $requestid, '重新获取', $isok ? 'ok' : 'no'); // 纪录日志
		if (!$isok) {
			return false;
		}

		// 3 保存文件
		$this->saveToFile($report_path, $report);
		return true;
	}
	/**
	 * 报告文件保存的格式是否合法
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function reportIsValid($report) {
		if (is_array($report) && isset($report['JSON_INFO']) && !empty($report['JSON_INFO'])) {
			return true;
		}
		return false;
	}

	/**
	 * 详情检查程序
	 * @param  [type] $requestid
	 * @param  [type] $url
	 * @return [type]
	 */
	public function detailChk($requestid, $detail_path) {
		// 1 读取并解析json
		$detail = $this->getFromFile($detail_path);
		if (is_array($detail)) {
			//进行检查
			$isok = $this->detailIsValid($detail);
			if ($isok) {
				return true;
			}
		}

		//2 重新获取并再次检查
		$detail = (new JxlRequestModel)->getApiDetail($requestid);
		$isok = $this->detailIsValid($detail);
		Logger::dayLog('jxlcheck', 'detail', $requestid, '重新获取', $isok ? 'ok' : 'no'); // 纪录日志
		if (!$isok) {
			return false;
		}

		//3 将正确结果重新写入到数据中
		$this->saveToFile($detail_path, $detail);
		return true;
	}
	/**
	 * 检测通话详情是否合法
	 * @param  [type] $detail_data [description]
	 * @return [type]              [description]
	 */
	private function detailIsValid($detail_data) {
		//1. 检测success
		$success = ArrayHelper::getValue($detail_data, 'success', '');
		$success = strtolower($success);
		if ($success != 'true') {
			return false;
		}

		//2. 检测请求状态码. 只有31200才是成功
		$error_code = ArrayHelper::getValue($detail_data, 'raw_data.members.error_code', 0);
		if ($error_code != 31200) {
			return false;
		}
		return true;
	}
	/**
	 * 从json文件中获取内容
	 * @param  [type] $report_path [description]
	 * @return [type]              [description]
	 */
	private function getFromFile($path) {
		if (!file_exists($path)) {
			return null;
		}
		$content = file_get_contents($path);
		$data = json_decode($content, true);
		return $data;
	}
	/**
	 * 保存到json文件中
	 * @param  [type] $path [description]
	 * @param  [type] $report      [description]
	 * @return [type]              [description]
	 */
	private function saveToFile($path, $data) {
		$jsonString = json_encode($data, JSON_UNESCAPED_UNICODE);
		\app\common\Func::makedir(dirname($path));
		file_put_contents($path, $jsonString);
	}
}