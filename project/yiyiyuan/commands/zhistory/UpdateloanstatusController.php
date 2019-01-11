<?php

namespace app\commands\sysloan;

use app\commands\BaseController;
use app\common\Curl;
use app\commonapi\Logger;
use app\models\news\GoodsBill;
use app\models\news\OverdueLoan;
use app\models\news\User_loan;
use Yii;

/**
 * 每10分钟执行一次 
 * 同步逾期账单结清数据 并将贷后中的loans状态该成8  分期数据
 * C:\wamp64\bin\php\php7.0.0\php.exe C:\wamp64\www\yiyiyuan\yii sysloan/updateloanstatus/index
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class UpdateloanstatusController extends BaseController {

	// 命令行入口文件
	public function actionIndex() {
		$time	 = time();
		$stime	 = date('Y-m-d H:i:00', strtotime('-10 minutes'));
		$etime	 = date('Y-m-d H:i:00');
		$where	 = [
			'AND',
			['loan_status' => 8],
			['>=', 'last_modify_time', $stime],
			['<=', 'last_modify_time', $etime],
		];
		$res	 = (new OverdueLoan())->find()->where($where)->all();
		$count	 = count($res);
		foreach ($res as $k => $val) {
			$data					 = [];
			$data['version']		 = '1.0';
			$data['loan_id']		 = $val['loan_id'];
			$data['bill_id']		 = isset($val['bill_id']) ? $val['bill_id'] : '';
			$data['source']			 = $val['source'];
			$data['is_more']		 = $val['is_push'];
			$data['user_id']		 = $val['user_id'];
			$data['business_type']	 = $val['business_type'];
			//判断是否是分期
			if (in_array($val['business_type'], [5, 6])) {
				$data['amount']			 = isset($val->goodsbill) ? $val->goodsbill->current_amount : 0;
				$data['loan_time']		 = isset($val->goodsbill) ? $val->goodsbill->create_time : '0000-00-00 00:00:00';
				$data['product_source']	 = 3;
				$data['phase']			 = isset($val->goodsbill) ? $val->goodsbill->phase : 0;
				$data['number']			 = isset($val->goodsbill) ? $val->goodsbill->number : 0;
				$data['goods_id']		 = isset($val->goodsbill) ? $val->goodsbill->goods_id : '';
				$data['order_id']		 = isset($val->goodsbill) ? $val->goodsbill->order_id : '';
				$data['phase']			 = isset($val->goodsbill) ? $val->goodsbill->phase : 0;
				$data['fees']			 = isset($val->goodsbill) ? $val->goodsbill->fee : 0;
				$data['number']			 = isset($val->goodsbill) ? $val->goodsbill->number : 0;
				$data['goods_amount']	 = isset($val->goodsbill) ? $val->goodsbill->goods_amount : 0;
				$data['current_amount']	 = isset($val->goodsbill) ? $val->goodsbill->current_amount : 0;
				$data['actual_amount']	 = isset($val->goodsbill) ? $val->goodsbill->actual_amount : 0;
				$data['repay_amount']	 = isset($val->goodsbill) ? $val->goodsbill->repay_amount : 0;
				$data['principal']		 = isset($val->goodsbill) ? $val->goodsbill->principal : 0;
				$data['over_principal']	 = isset($val->goodsbill) ? $val->goodsbill->over_principal : 0;
				$data['interest']		 = isset($val->goodsbill) ? $val->goodsbill->interest : 0;
				$data['over_interest']	 = isset($val->goodsbill) ? $val->goodsbill->over_interest : 0;
				$data['over_late_fee']	 = isset($val->goodsbill) ? $val->goodsbill->over_late_fee : 0;
			} else {
				$loanInfo				 = (new User_loan())->getLoanById($val['loan_id']);
				$data['amount']			 = $loanInfo['amount'];
				$data['parent_loan_id']	 = $loanInfo['parent_loan_id'];
				$data['loan_time']		 = $loanInfo['create_time'];
				$data['product_source'] = $this ->getProductsource($val);
			}
			$data['days']				 = $val['days'];
			$data['desc']				 = $val['desc'];
			$data['start_date']			 = $val['start_date'] ? $val['start_date'] : '0000-00-00 00:00:00';
			$data['end_date']			 = $val['end_date'] ? $val['end_date'] : '0000-00-00 00:00:00';
			$data['chase_amount']		 = $val['chase_amount'];
			$data['status']				 = $val['loan_status'];
			$data['repay_time']			 = $val['last_modify_time'] ? $val['last_modify_time'] : '0000-00-00 00:00:00';
			$data['is_calculation']		 = $val['is_calculation'];
			$data['create_time']		 = date("Y-m-d H:i:s", time());
			$data['last_modify_time']	 = date("Y-m-d H:i:s", time());
			$data['fee']				 = $val['interest_fee'];
			$data['servic_fee']			 = $val['withdraw_fee'];
			$data['bank_id']			 = isset($val->bank->id) ? $val->bank->id : 0;
			$data['username']			 = isset($val->user->realname) ? $val->user->realname : '';
			$data['mobile']				 = isset($val->user->mobile) ? $val->user->mobile : '';
			$data['identity']			 = isset($val->user->identity) ? $val->user->identity : '';
			$data['prome_score']		 = isset($val->promes) && !empty($val->promes) ? $val->promes->prome_score : 0;
			$data['prome_subject']		 = isset($val->promes) && !empty($val->promes) ? $val->promes->prome_subject : '';
			$data['remit_time']			 = isset($val->remit->last_modify_time) ? $val->remit->last_modify_time : '0000-00-00 00:00:00';
			$data['sign']				 = $this->encrySign($data);
//                调用贷后接口 
			$url						 = Yii::$app->params['daihou_api_url'] . "/api/loan/payall";
			$result						 = (new Curl())->post($url, $data);
			$resultArr					 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('sysloan', '同步逾期转结清数据', $data, $result);
			}
		}
	}

	// 纪录日志
	private function log($message) {
		echo $message . "\n";
	}

}
