<?php

namespace app\commands\sysloannew;

use app\commands\BaseController;
use app\common\Curl;
use app\common\Logger;
use app\models\news\User_loan;
use Yii;

/**
 * 逾前未分期订单推送  每天3:30执行一次
 * sysloannew/beforovdue
 */
set_time_limit(0);
ini_set('memory_limit', '-1');

class BeforovdueController extends BaseController {

	public $enableCsrfValidation = false;

	public function actionIndex($start_time='') {
		$beforeDays	 = $this->getBeforeDays();
		if(empty($start_time)){
			$start_time	 = date("Y-m-d 00:00:00", strtotime("+$beforeDays day"));
		}
		$where		 = [
			'and',
			['in', 'status', [9, 11]],
//			['in', 'business_type', [1, 4]],
			['=', 'end_date', $start_time],
		];
		$user_loan	 = User_loan::find()->where($where)->all();
		if (empty($user_loan)) {
			exit();
		}
		$postArr = array_chunk($user_loan, 500);  //批量推送
		foreach ($postArr as $pk => $pv) {
			$data		 = $postData	 = [];
			foreach ($pv as $key => $val) {
				if (empty($val->parentremit) || $val->parentremit['remit_status'] != 'SUCCESS') {
					continue;
				}
				$data[$key]['loan_id']			 = $val['loan_id'];
				$data[$key]['parent_loan_id']	 = $val['parent_loan_id'];
				$data[$key]['business_type']	 = $val['business_type'];
				$data[$key]['status']			 = $val['status'];
				$data[$key]['end_date']			 = $val['end_date'];
				$data[$key]['product_source']	 = $this->getProductsource($val);
			}
			if (empty($data)) {
				continue;
			}
			$postData['data']	 = json_encode($data);
			$postData['sign']	 = $this->encrySign($postData);
			$url				 = Yii::$app->params['sysloan_api_url'] . "/api/loannew/savebeforloan";
			$result				 = (new Curl())->post($url, $postData);
			$resultArr			 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('beforovdue', '同步逾前账单', $postData['data']);
			}
		}
	}

}
