<?php

namespace app\commands\sysloannew;

use app\models\news\RepayCouponUse;
use app\commonapi\Logger;
use app\common\Curl;
use app\commands\BaseController;
use Yii;
use yii\db\Query;
use yii\console\Controller;

/**
 * 还款优惠券推送  10分钟一次
 * Class BeforbillrepayController
 * @package app\commands\sysloan
 * sysloannew/repaycouponuse
 */
class RepaycouponuseController extends BaseController {

	public function actionIndex() {
		$stime	 = date("Y-m-d H:i:00", strtotime("-10 minute"));
		$etime	 = date("Y-m-d H:i:00");
		$where	 = [
			'and',
			['=', 'yi_repay_coupon_use.repay_status', 1],
			['>=', 'yi_repay_coupon_use.last_modify_time', $stime],
			['<', 'yi_repay_coupon_use.last_modify_time', $etime],
			['!=', 'b.loan_status', 7],
			['>', 'b.loan_id', 0],
		];
		$list	 = (new RepayCouponUse())->find()->select('b.days,b.business_type,b.end_date,yi_repay_coupon_use.*')->leftJoin('yi_overdue_loan AS b', 'yi_repay_coupon_use.loan_id = b.loan_id')->where($where)->asArray()->all();
		if (empty($list)) {
			exit();
		}
		$listArr = array_chunk($list, 500);
		foreach ($listArr as $lk => $lv) {
			$data		 = $postData	 = [];
			foreach ($lv as $key => $val) {
				$data[$key]['user_id']			 = isset($val['user_id']) ? $val['user_id'] : '';
				$data[$key]['loan_id']			 = isset($val['loan_id']) ? $val['loan_id'] : '';
				$data[$key]['discount_id']		 = isset($val['discount_id']) ? $val['discount_id'] : '';
				$data[$key]['repay_id']			 = isset($val['repay_id']) ? $val['repay_id'] : '';
				$data[$key]['repay_amount']		 = isset($val['repay_amount']) ? $val['repay_amount'] : '';
				$data[$key]['repay_status']		 = isset($val['repay_status']) ? $val['repay_status'] : '';
				$data[$key]['coupon_amount']	 = isset($val['coupon_amount']) ? $val['coupon_amount'] : '';
				$data[$key]['product_source']	 = $this->getProductsource($val);
			}
			$postData['data']	 = json_encode($data);
			$postData['sign']	 = $this->encrySign($postData);
			$url				 = Yii::$app->params['sysloan_api_url'] . "/api/loannew/saverepaycouponuse";
			$result				 = (new Curl())->post($url, $postData);
			$resultArr			 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('repaycouponuse', '同步还款使用优惠券', $data);
			}
		}
	}

}
