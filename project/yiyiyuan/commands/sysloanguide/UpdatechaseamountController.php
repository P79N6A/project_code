<?php

namespace app\commands\sysloanguide;

use app\commands\BaseController;
use app\common\Curl;
use app\models\day\Overdue_loan_guide;
use Yii;
use app\common\Logger;

/**
 * 更新逾后滞纳金  每天凌晨执行一次
 * Class CharuserloanController
 * @package app\commands
 * sysloannew/updatechaseamount/index
 */
class UpdatechaseamountController extends BaseController {

	public $enableCsrfValidation = false;

	public function actionIndex() {
		$where		 = [
			'or',
			[
				'and',
				['in', 'loan_status', [11, 12, 13]],
				['is_push' => ['0']],
			],
			[
				'and',
				['in', 'loan_status', [11, 12, 13]],
				['is_push' => 1],
				['>=', 'last_modify_time', date("Y-m-d")]
			]
		];
		$count		 = (new Overdue_loan_guide())->find()->where($where)->count();
		$id			 = 0;
		$limit		 = 500;
		$forcount	 = ceil($count / $limit);
		for ($i = 1; $i <= $forcount; $i++) {
			$where = [
				'or',
				[
					'and',
					['in', 'loan_status', [11, 12, 13]],
					['is_push' => ['0']],
					['>', 'id', $id],
				],
				[
					'and',
					['>', 'id', $id],
					['>=', 'last_modify_time', date("Y-m-d")],
					['in', 'loan_status', [11, 12, 13]],
					['is_push' => 1],
				]
			];
			$postData = $data = [];
			$info = (new Overdue_loan_guide())->find()->where($where)->orderBy('id asc')->limit($limit)->all();
			foreach ($info as $key => $val) {
				$data[$key]['loan_id']			 = $val->loan_id;
				$data[$key]['product_source']	 = $this->getProductsource($val);
				$data[$key]['chase_amount']		 = $val['chase_amount'];
				$id								 = $val['id'];
			}
			$postData['data']	 = json_encode($data);
			$postData['sign']	 = $this->encrySign($postData);
			//调用贷后接口
			$url			 = Yii::$app->params['sysloan_api_url'] . "/api/loannew/updatecharseamount";
			$result			 = (new Curl())->post($url, $postData);
			$resultArr		 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('sysloanguide/updatechaseamount', '同步逾后还款记录失败', $postData);
			}
		}
	}

}
