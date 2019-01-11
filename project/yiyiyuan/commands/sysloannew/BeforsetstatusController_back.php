<?php

namespace app\commands\sysloannew;

use app\models\news\User_loan;
use app\commonapi\Logger;
use app\common\Curl;
use app\commands\BaseController;
use Yii;
use yii\console\Controller;

/**
 * 逾前结清订单推送  十分钟执行一次
 * Class CharuserloanController
 * @package app\commands
 * sysloannew/beforsetstatus
 */
class BeforsetstatusController extends BaseController {

	public function actionIndex() {
		$startTime = date("Y-m-d 00:00:00", strtotime("+1 day"));

		$beforeDays	 = $this->getBeforeDays();
		$endTime	 = date("Y-m-d 00:00:00", strtotime("+$beforeDays day"));
		$stime		 = date("Y-m-d H:i:00", strtotime("-10 minute"));
		$etime		 = date("Y-m-d H:i:00");

		$where = [
			'and',
			['>=', 'end_date', $startTime],
			['<=', 'end_date', $endTime],
			['=', 'status', 8],
			['>=', 'last_modify_time', $stime],
			['<', 'last_modify_time', $etime],
		];

		$userLoan = User_loan::find()->where($where)->select('loan_id,business_type,end_date,days')->asArray()->all();
		if (empty($userLoan)) {
			exit();
		}
		$data = [];
		foreach ($userLoan as $key => $val) {
			$data[$key]['loan_id']			 = $val['loan_id'];
			$data[$key]['product_source']	 = $this->getProductsource($val);
		}
		$postData['data']	 = json_encode($data);
		$postData['sign']	 = $this->encrySign($postData);
		$url				 = Yii::$app->params['sysloan_api_url'] . "/api/loannew/clrearbeforloan";
		$result				 = (new Curl())->post($url, $postData);
		$resultArr			 = json_decode($result, true);
		if ($resultArr['rsp_code'] != '0000') {
			Logger::dayLog('beforsetstatus', '结清账单', $postData);
		}
	}

}
