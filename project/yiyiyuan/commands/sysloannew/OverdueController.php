<?php

namespace app\commands\sysloannew;

use app\commands\BaseController;
use app\common\Curl;
use app\common\Logger;
use app\models\news\OverdueLoan;
use Yii;

/**
 * 逾后未分期订单推送  每天1:00执行一次
 * sysloannew/overdue
 */
set_time_limit(0);
ini_set('memory_limit', '-1');

class OverdueController extends BaseController {

	public $enableCsrfValidation = false;

	public function actionIndex() {
		$start_time	 = date("Y-m-d 00:00:00");
		$where		 = [
			'and',
			['in', 'loan_status', [11, 12, 13]],
			['=', 'end_date', $start_time],
		];
		$user_loan	 = OverdueLoan::find()->where($where)->all();
		if (empty($user_loan)) {
			exit();
		}
		$postArr = array_chunk($user_loan, 500);  //批量推送
		foreach ($postArr as $pk => $pv) {
			$data		 = $postData	 = [];
			foreach ($pv as $key => $val) {
				if (empty($val->userloan->parentremit) || $val->userloan->parentremit['remit_status'] != 'SUCCESS') {
					continue;
				}
				$data[$key]['loan_id']			 = $val['loan_id'];
				$data[$key]['source']			 = $val['source'];
				$data[$key]['user_id']			 = $val['user_id'];
				$data[$key]['business_type']	 = $val['business_type'];
				$data[$key]['amount']			 = $val['amount'] ? $val['amount'] : 0;
				$data[$key]['days']				 = $val['days'];
				$data[$key]['desc']				 = $val['desc'] ? $val['desc'] : '';
				$data[$key]['start_date']		 = $val['start_date'] ? $val['start_date'] : '0000-00-00 00:00:00';
				$data[$key]['end_date']			 = $val['end_date'] ? $val['end_date'] : '0000-00-00 00:00:00';
				$data[$key]['chase_amount']		 = $val['chase_amount'] > 0 ? $val['chase_amount'] : 0;
				$data[$key]['status']			 = $val['loan_status'];
				$data[$key]['repay_time']		 = $val['repay_time'];
				$data[$key]['is_calculation']	 = $val['is_calculation'];
				$data[$key]['create_time']		 = date("Y-m-d H:i:s", time());
				$data[$key]['last_modify_time']	 = date("Y-m-d H:i:s", time());
				$data[$key]['fee']				 = $val['interest_fee'] > 0 ? $val['interest_fee'] : 0;
				$data[$key]['servic_fee']		 = $val['withdraw_fee'] > 0 ? $val['withdraw_fee'] : 0;
				$data[$key]['bank_id']			 = isset($val->bank->id) ? $val->bank->id : 0;
				$data[$key]['username']			 = isset($val->user->realname) ? $val->user->realname : '';
				$data[$key]['mobile']			 = isset($val->user->mobile) ? $val->user->mobile : '';
				$data[$key]['identity']			 = isset($val->user->identity) ? $val->user->identity : '';
				$data[$key]['prome_score']		 = isset($val->promes) && !empty($val->promes) ? $val->promes->prome_score : 0;
				$data[$key]['prome_subject']	 = isset($val->promes) && !empty($val->promes) ? $val->promes->prome_subject : '';
				$data[$key]['remit_time']		 = isset($val->remit->last_modify_time) ? $val->remit->last_modify_time : '0000-00-00 00:00:00';
				$data[$key]['is_more']			 = $val['is_push'] == 1 ? 2 : 1;
				$data[$key]['loan_time']		 = !empty($val->userloan['create_time']) ? $val->userloan['create_time'] : '0000-00-00 00:00:00';
				$data[$key]['parent_loan_id']	 = !empty($val->userloan['parent_loan_id']) ? $val->userloan['parent_loan_id'] :'';
				$data[$key]['product_source']	 = $this->getProductsource($val);
			}
			if (empty($data)) {
				continue;
			}
			$postData['data']	 = json_encode($data);
			$postData['sign']	 = $this->encrySign($postData);
			$url				 = Yii::$app->params['sysloan_api_url'] . "/api/loannew/saveoverdueloan";
			$result				 = (new Curl())->post($url, $postData);
			$resultArr			 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('saveoverdueloan', '同步逾期账单', $postData['data']);
			}
		}
	}

}
