<?php

namespace app\commands\sysloan;

use app\models\dev\Loan_repay;
use app\models\dev\User;
use app\models\news\GoodsBill;
use app\models\news\OverdueLoan;
use app\models\news\User_loan;
use app\commonapi\Logger;
use app\common\Curl;
use app\commands\BaseController;
use Yii;
use yii\db\Query;
use yii\console\Controller;

/**
 * 逾前未分期还款推送  10分钟一次
 * Class BeforloanrepayController
 * @package app\commands\sysloan
 * 测试  D:\phpStudy\php\php-7.0.12-nts\php.exe D:\work\yiyiyuanOnline\yii sysloan/beforloanrepay
 */
class BeforloanrepayController extends BaseController {

	public function actionIndex($stime = '') {
		$start_time	 = date("Y-m-d 00:00:00", strtotime("+3 day"));
		$end_time	 = date("Y-m-d 00:00:00", strtotime("+1 day"));
		if(empty($stime)) {
			$stime		 = date("Y-m-d H:i:00", strtotime("-10 minute"));
		}
		$etime		 = date("Y-m-d H:i:00");
		$where		 = [
			'and',
			['=', 'yi_loan_repay.status', 1],
			['between', 'b.end_date', $end_time, $start_time],
			['>=', 'yi_loan_repay.last_modify_time', $stime],
			['<', 'yi_loan_repay.last_modify_time', $etime],
			['in', 'b.business_type', [1, 4]],
			['in', 'b.status', [8, 9, 11]],
		];
//        $query  = new Query();
//        $list   = $query
//            ->select('*')
//            ->from('yi_loan_repay as a ')
//            ->leftJoin('yi_user_loan as b', 'a.loan_id = b.loan_id')
//            ->where($where)
//            ->all();
		$list		 = (new Loan_repay())->find()->leftJoin('yi_user_loan AS b', 'yi_loan_repay.loan_id = b.loan_id')->where($where)->all();
		
		if (empty($list)) {
			exit();
		}
		foreach ($list as $key => $val) {
			$data					 = [];
			$data['version']		 = '1.0';
			$data['loan_id']		 = isset($val['loan_id']) ? $val['loan_id'] : '';
			$data['order_id']		 = isset($val['repay_id']) ? $val['repay_id'] : '';
			$data['business_type']	 = $val->loan->business_type;
			$data['product_source'] = $this ->getProductsource($val->loan);
			$data['status']			 = $val->loan->status;
			$data['user_id']		 = isset($val['user_id']) ? $val['user_id'] : '';
			$data['bank_id']		 = isset($val['bank_id']) ? $val['bank_id'] : '';
			$data['platform']		 = isset($val['platform']) ? $val['platform'] : '';
			$data['source']			 = isset($val['source']) ? $val['source'] : '';
			$data['pic_repay1']		 = isset($val['pic_repay1']) ? $val['pic_repay1'] : '';
			$data['pic_repay2']		 = isset($val['pic_repay2']) ? $val['pic_repay2'] : '';
			$data['pic_repay3']		 = isset($val['pic_repay3']) ? $val['pic_repay3'] : '';
			$data['amount']			 = isset($val['actual_money']) ? $val['actual_money'] : '';
			$data['pay_key']		 = isset($val['pay_key']) ? $val['pay_key'] : '';
			$data['realname']		 = isset($val->user->realname) ? $val->user->realname : '';
			$data['mobile']			 = isset($val->user->mobile) ? $val->user->mobile : '';
			$data['identity']		 = isset($val->user->identity) ? $val->user->identity : '';
			$data['paybill']		 = isset($val['paybill']) ? $val['paybill'] : '';
			$data['repay_time']		 = isset($val['repay_time']) ? $val['repay_time'] : '';
			$data['repay_mark']		 = isset($val['repay_mark']) ? $val['repay_mark'] : '';
			$data['repay_status']	 = isset($val['status']) ? $val['status'] : '';
			$data['repay_type']		 = 1;
			$data['sign']			 = $this->encrySign($data);
			$url					 = Yii::$app->params['daihou_api_url'] . "/api/loan/savebeforrepay";
			$result					 = (new Curl())->post($url, $data);
			$resultArr				 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('sysloan', '同步逾前账单', $data);
			}
		}
	}

}
