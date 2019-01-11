<?php

namespace app\commands\sysloannew;

use app\commands\BaseController;
use app\common\Curl;
use app\commonapi\Logger;
use app\models\news\Renewal_payment_record;
use Yii;

/**
 * 同步贷后系统成功还款记录  每10分钟执行一次
 * windows D:phpStudy\php56n\php.exe D:WWW\yyy_loan\yii sysloan/sysloanrepay/index
 * sysloannew/sysoutrenewal/index
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SysoutrenewalController extends BaseController {

	public function actionIndex($startTime = '', $endTime = '') {
		$time = time();
		if (empty($startTime)) {
			$startTime = date('Y-m-d H:i:00', strtotime('-10 minutes'));
		}else{
            $startTime	 = date('Y-m-d H:i:00', $startTime);
        }
		if (empty($endTime)) {
			$endTime = date('Y-m-d H:i:00', $time);
		}
		$where = [
            'and',
            ['>=', 'yi_renewal_payment_record.last_modify_time',$startTime],
            ['<',  'yi_renewal_payment_record.last_modify_time', $endTime],
            ['=',  'yi_renewal_payment_record.status',      1],
			['>', 'b.id', 0],
        ];
		$renewal_payment	 = (new Renewal_payment_record())->find()->leftJoin('yi_overdue_loan AS b', 'yi_renewal_payment_record.loan_id = b.loan_id')->where($where)->select('yi_renewal_payment_record.*')->all();
		if (empty($renewal_payment)) {
			exit();
		}
		$renewal_payment_list = array_chunk($renewal_payment, 500);
		foreach ($renewal_payment_list as $rk => $rv) {
			$ids		 = $postData	 = $data		 = [];
			foreach ($rv as $key => $val) {
				$ids[]							 = $val['loan_id'];
				$data[$key]['loan_id']			 = $val['loan_id'];
				$data[$key]['order_id']			 = $val['order_id'];
				$data[$key]['parent_loan_id']	 = $val['parent_loan_id'];
				$data[$key]['user_id']			 = $val['user_id'];
				$data[$key]['bank_id']			 = $val['bank_id'];
				$data[$key]['platform']			 = $val['platform'];
				$data[$key]['source']			 = $val['source'];
				$data[$key]['money']			 = $val['money'];
				$data[$key]['actual_money']		 = $val['actual_money'];
				$data[$key]['paybill']			 = $val['paybill'];
				$data[$key]['status']			 = $val['status'];
				$data[$key]['last_modify_time']	 = $val['last_modify_time'];
				$data[$key]['create_time']		 = $val['create_time'];
				$data[$key]['product_source']	 = $this->getProductsource($val->loan);
			}
			$postData['data']	 = json_encode($data);
			$postData['sign']	 = $this->encrySign($postData);
			//调用贷后接口
			$url				 = Yii::$app->params['sysloan_api_url'] . "/api/loannew/setrenewal";
			$result				 = (new Curl())->post($url, $postData);
			$resultArr			 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('sysoutrenewal', '同步还款记录失败', $data);
			}
			Logger::dayLog('returnloanids', '同步还款数据loanID记录', '开始时间为' . $startTime, '结束时间为' . $endTime, '条数' . count($ids), $ids);
		}
	}

}
