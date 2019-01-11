<?php

namespace app\commands\sysloannew;

use app\commands\BaseController;
use app\common\Curl;
use app\commonapi\Logger;
use app\models\news\User_loan;
use Yii;

/**
 * 每10分钟执行一次 
 * 同步逾前账单结清数据
 * sysloannew/beforestatus
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class BeforestatusController extends BaseController {

	// 命令行入口文件
	public function actionIndex($stime = '') {
		$time	 = time();
        
        if (empty($stime)) {
            $stime	 = date('Y-m-d H:i:00', strtotime('-10 minutes'));
		}else{
            $stime	 = date('Y-m-d H:i:00', $stime);
        }
		$etime	 = date('Y-m-d H:i:00');
		
		$beforeDays	 = $this->getBeforeDays();
		$start_time	 = date("Y-m-d 00:00:00", strtotime("+$beforeDays day"));
		$end_time	 = date("Y-m-d 00:00:00", strtotime("+1 day"));
		
		$where	 = [
			'AND',
			['>=', 'last_modify_time', $stime],
			['<=', 'last_modify_time', $etime],
			['between', 'end_date', $end_time, $start_time],
			['status' => 8],
		];
		$res	 = (new User_loan())->find()->where($where)->all();
		$resArr = array_chunk($res, 500);
		foreach ($resArr as $rk => $rv) {
			$data		 = $postData	 = [];
			foreach ($res as $k => $val) {
				$data[$k]['loan_id']		 = $val['loan_id'];
				$data[$k]['product_source']	 = $this->getProductsource($val);
			}
			$postData['data']	 = json_encode($data);
			$postData['sign']	 = $this->encrySign($postData);
			$url				 = Yii::$app->params['sysloan_api_url'] . "/api/loannew/beforesetstatus";
			$result				 = (new Curl())->post($url, $postData);
			$resultArr			 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('beforestatus', '同步逾前结清数据', $postData, $result);
			}
		}
	}
}
