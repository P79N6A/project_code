<?php

namespace app\commands\sysloan;

use app\commands\BaseController;
use app\common\Curl;
use app\models\news\OverdueLoan;
use Yii;
use app\common\Logger;

/**
 * 更新逾后滞纳金  每天凌晨执行一次
 * Class CharuserloanController
 * @package app\commands
 * C:\wamp64\bin\php\php7.0.0\php.exe C:\wamp64\www\youxin_after\yii sysloan/updatechaseamount/index
 */
class UpdatechaseamountController extends BaseController {

	public $enableCsrfValidation = false;

	public function actionIndex() {
		$where	 = [
			'and',
			['in', 'loan_status', [11, 12, 13]]
		];
		$info	 = (new OverdueLoan())->find()->where($where)->all();
		if (empty($info)) {
			exit();
		}
		foreach ($info as $key => $val) {
			$data[$key]['loan_id']			 = $val->order_id;
			$data[$key]['product_source']	 = $this->getProductsource($val);
			$data[$key]['chase_amount']		 = $val['chase_amount'];
		}
		$datas['data']	 = json_encode($data);
		$datas['sign']	 = $this->encrySign($datas);
		//调用贷后接口
		$url			 = Yii::$app->params['daihou_api_url'] . "/api/loan/updatecharseamount";
		$result			 = (new Curl())->post($url, $datas);
		$resultArr		 = json_decode($result, true);
		if ($resultArr['rsp_code'] != '0000') {
			Logger::dayLog('sysloan/updatechaseamount', '同步逾后还款记录失败', $datas);
		}
	}

}
