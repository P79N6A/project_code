<?php

namespace app\commands\sysloanguide;

use app\commands\BaseController;
use app\common\Curl;
use app\commonapi\Logger;
use app\models\day\Loan_repay_guide;
use Yii;

/**
 * 逾前未分期还款推送  10分钟一次
 * Class BeforloanrepayController
 * @package app\commands\sysloan
 * 测试  D:\phpStudy\php\php-7.0.12-nts\php.exe D:\work\yiyiyuanOnline\yii sysloanguide/beforloanrepay
 */
class BeforloanrepayController extends BaseController {

	public function actionIndex() {
		$beforeDays	 = $this->getBeforeDays();
		$start_time	 = date("Y-m-d 00:00:00", strtotime("+$beforeDays day"));
		$end_time	 = date("Y-m-d 00:00:00", strtotime("+1 day"));
		$stime		 = date("Y-m-d H:i:00", strtotime("-10 minute"));
		$etime		 = date("Y-m-d H:i:00");
		$where		 = [
			'and',
			['=', 'qj_loan_repay.status', 1],
			['between', 'b.end_date', $end_time, $start_time],
			['>=', 'qj_loan_repay.last_modify_time', $stime],
			['<', 'qj_loan_repay.last_modify_time', $etime],
			['in', 'b.business_type', [7]],
			['in', 'b.status', [8, 9, 11]],
		];
		$list		 = (new Loan_repay_guide())->find()->leftJoin('qj_user_loan AS b', 'qj_loan_repay.loan_id = b.loan_id')->where($where)->all();
		if (empty($list)) {
			exit();
		}
		$repayArr = array_chunk($list, 500); //批量推送
		foreach ($repayArr as $r => $rv) {
			$data		 = $postData	 = [];
			foreach ($rv as $key => $val) {
				$data[$key]['loan_id']			 = $val['loan_id'];
				$data[$key]['product_source']	 = $this->getProductsource($val->loan);
				$data[$key]['order_id']			 = $val['repay_id'];
				$data[$key]['business_type']	 = $val->loan->business_type;
				$data[$key]['status']			 = $val->loan->status;
				$data[$key]['user_id']			 = $val['user_id'];
				$data[$key]['bank_id']			 = empty($val['bank_id']) ? $val['bank_id'] : '';
				$data[$key]['platform']			 = empty($val['platform']) ? $val['platform'] : '';
				$data[$key]['source']			 = empty($val['source']) ? $val['source'] : '';
				$data[$key]['pic_repay1']		 = empty($val['pic_repay1']) ? $val['pic_repay1'] : '';
				$data[$key]['pic_repay2']		 = empty($val['pic_repay2']) ? $val['pic_repay2'] : '';
				$data[$key]['pic_repay3']		 = empty($val['pic_repay3']) ? $val['pic_repay3'] : '';
				$data[$key]['amount']			 = empty($val['actual_money']) ? $val['actual_money'] : '';
				$data[$key]['pay_key']			 = empty($val['pay_key']) ? $val['pay_key'] : '';
				$data[$key]['realname']			 = isset($val->user->realname) ? $val->user->realname : '';
				$data[$key]['mobile']			 = isset($val->user->mobile) ? $val->user->mobile : '';
				$data[$key]['identity']			 = isset($val->user->identity) ? $val->user->identity : '';
				$data[$key]['paybill']			 = isset($val['paybill']) ? $val['paybill'] : '';
				$data[$key]['repay_time']		 = isset($val['repay_time']) ? $val['repay_time'] : '';
				$data[$key]['repay_mark']		 = isset($val['repay_mark']) ? $val['repay_mark'] : '';
				$data[$key]['repay_status']		 = isset($val['status']) ? $val['status'] : '';
				$data[$key]['repay_type']		 = 1;
			}
			$postData['data']	 = json_encode($data);
			$postData['sign']	 = $this->encrySign($postData);
			$url				 = Yii::$app->params['sysloan_api_url'] . "/api/loannew/savebeforrepay";
			$result				 = (new Curl())->post($url, $postData);
			$resultArr			 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('sysloanguide/beforloanrepay', '同步逾前账单', $postData);
			}
		}
	}

}
