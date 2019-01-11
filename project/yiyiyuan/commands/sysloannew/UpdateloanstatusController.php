<?php

namespace app\commands\sysloannew;

use app\commands\BaseController;
use app\common\Curl;
use app\commonapi\Logger;
use app\models\news\GoodsBill;
use app\models\news\OverdueLoan;
use app\models\news\User_loan;
use Yii;

/**
 * 每10分钟执行一次 
 * 同步逾期账单结清数据 并将贷后中的loans状态该成8
 * sysloannew/updateloanstatus
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class UpdateloanstatusController extends BaseController {

	// 命令行入口文件
	public function actionIndex($stime='') {
		$time	 = time();
        
        if (empty($stime)) {
            $stime	 = date('Y-m-d H:i:00', strtotime('-10 minutes'));
		}else{
            $stime	 = date('Y-m-d H:i:00', $stime);
        }
        
		$etime	 = date('Y-m-d H:i:00');
		$where	 = [
			'AND',
			['loan_status' => 8],
			['>=', 'last_modify_time', $stime],
			['<=', 'last_modify_time', $etime],
		];
		$res	 = (new OverdueLoan())->find()->where($where)->all();
		if(empty($res)){
			exit();
		}
		$resArr = array_chunk($res, 500);
		foreach ($resArr as $rk => $rv) {
			$data		 = $postData	 = [];
			foreach ($res as $k => $val) {
				$data[$k]['loan_id']		 = $val['loan_id'];
				$data[$k]['product_source']	 = $this->getProductsource($val);
				$data[$k]['repay_time']		 = $val['last_modify_time'] ? $val['last_modify_time'] : '0000-00-00 00:00:00';
			}

			$postData['data']	 = json_encode($data);
			$postData['sign']	 = $this->encrySign($postData);
			$url				 = Yii::$app->params['sysloan_api_url'] . "/api/loannew/settleloan";
			$result				 = (new Curl())->post($url, $postData);
			$resultArr			 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('updateloanstatus', '同步逾期转结清数据', $postData, $result);
			}
		}
	}

	// 纪录日志
	private function log($message) {
		echo $message . "\n";
	}

}
