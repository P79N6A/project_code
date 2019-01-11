<?php

namespace app\commands\sysloanguide;

use app\commands\BaseController;
use app\common\Curl;
use app\commonapi\Logger;
use app\models\day\User_loan_guide;
use Yii;

/**
 * 逾前未分期订单推送  每天执行一次
 * Class CharuserloanController
 * @package app\commands
 * 测试  D:\phpStudy\php\php-7.0.12-nts\php.exe D:\work\yiyiyuanOnline\yii sysloanguide/beforovdue
 */
class BeforovdueController extends BaseController {

	public $enableCsrfValidation = false;

	public function actionIndex() {
		$beforeDays	 = $this->getBeforeDays();
		$start_time	 = date("Y-m-d 00:00:00", strtotime("+$beforeDays day"));
		$where		 = [
			'and',
			['in', 'status', [9, 11]],
			['in', 'business_type', [7]],
			['=', 'end_date', $start_time],
		];
		$user_loan	 = User_loan_guide::find()->where($where)->all();
		if (empty($user_loan)) {
			exit();
		}
		$postArr = array_chunk($user_loan, 500);  //批量推送
		foreach ($postArr as $pk => $pv) {
			$data		 = $postData	 = [];
			foreach ($pv as $key => $val) {
				if (empty($val->user_remit_list_guide) || $val->user_remit_list_guide['remit_status'] != 'SUCCESS') {
					continue;
				}
				$data[$key]['loan_id']			 = $val['loan_id'];
				$data[$key]['parent_loan_id']	 = $val['parent_loan_id'];
				$data[$key]['business_type']	 = $val['business_type'];
				$data[$key]['status']			 = $val['status'];
				$data[$key]['end_date']			 = $val['end_date'];
				$data[$key]['product_source']	 = $this->getProductsource($val);
			}
			if(empty($data)){
				continue;
			}
			$postData['data']	 = json_encode($data);
			$postData['sign']	 = $this->encrySign($postData);
			$url				 = Yii::$app->params['sysloan_api_url'] . "/api/loannew/savebeforloan";
			$result				 = (new Curl())->post($url, $postData);
			$resultArr			 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('sysloanguide/beforovdue', '同步逾前账单', $postData['data']);
			}
		}
	}

}
