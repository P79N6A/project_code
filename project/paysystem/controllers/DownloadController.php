<?php
/**
 * 聚信立报告等下载
 *
 */
namespace app\controllers;

use app\common\Crypt3Des;
use app\models\JxlStat;
use Yii;

class DownloadController extends BaseController {

	public function init() {
		parent::init();
	}
	
	public function actionIndex() {

	}
	/**
	 * 下载聚信立报告和详情
	 * @return [type] [description]
	 */
	public function actionReport() {
		$phone = $this->get('phone');
		$phone = $this->decryptPhone($phone);
		$json = (new JxlStat)->getReportByPhone($phone);
		header('Content-type: application/json');
		echo $json;exit;
	}
	public function actionDetail() {
		$phone = $this->get('phone');
		$phone = $this->decryptPhone($phone);
		$json = (new JxlStat)->getDetailByPhone($phone);
		header('Content-type: application/json');
		echo $json;exit;
	}
	private function decryptPhone($phone) {
		//579BEFGINPQUVZehilprstxy
		$phone = Crypt3Des::decrypt($phone, Yii::$app->params['trideskey']);
		return $phone;
	}

	/**
	 * (废弃)下载聚信立报表
	 */
	private function actionJxl() {
		//1 检测参数
		$str = $this->get('t');
		if (!$str) {
			return $this->showMessage(1, '参数不合法');
		}

		//2 解密数据
		$oJxlStat = new JxlStat;
		$data = $oJxlStat->decryptUrl($str);
		if (!is_array($data) || empty($data)) {
			return $this->showMessage(1, '参数不合法');
		}

		//3 获取文件
		$type = $data['type'];
		switch ($type) {
		case 'JSON':
			return $this->jxlJson($data);
			break;
		case 'XLS':
			return $this->jxlXls($data);
			break;
		default:
			return $this->showMessage(1, '参数不合法');
		}
	}
	/**
	 * (废弃)输出json结果
	 */
	private function jxlJson($data) {
		header('Content-type: application/json');
		//header('Content-type: text/json');
		return file_get_contents($data['path']);
	}
	/**
	 * (废弃)输出xls结果
	 */
	private function jxlXls($data) {

	}
}
