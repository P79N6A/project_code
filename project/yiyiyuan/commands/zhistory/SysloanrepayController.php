<?php

namespace app\commands\sysloan;

use app\commands\BaseController;
use app\common\Curl;
use app\commonapi\Logger;
use app\models\news\GoodsBill;
use app\models\news\Loan_repay;
use Yii;

/**
 * 同步贷后系统成功还款记录  每10分钟执行一次
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * windows D:phpStudy\php56n\php.exe D:WWW\yyy_loan\yii sysloan/sysloanrepay/index
 * C:\wamp64\bin\php\php7.0.0\php.exe C:\wamp64\www\yiyiyuan\yii sysloan/sysloanrepay/index
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SysloanrepayController extends BaseController {

	// 命令行入口文件
	public function actionIndex() {
		$time		 = time();
		$startTime	 = date('Y-m-d H:i:00', strtotime('-10 minutes'));
		$endTime	 = date('Y-m-d H:i:00', $time);

		$res = (new Loan_repay())->getLoanByTime($startTime, $endTime);
		$ids = [];
		if (!empty($res)) {
			foreach ($res as $k => $v) {
				$ids[]				 = $v['loan_id'];
				$data				 = [];
				$data['version']	 = '1.0';
				$data['order_id']	 = $v['repay_id'];
				$data['loan_id']	 = $v['loan_id'];
				$data['bill_id']	 = isset($v['bill_id']) ? $v['bill_id'] : '';
				$data['user_id']	 = $v['user_id'];
				$data['amount']		 = $v['actual_money'] > 0 ? $v['actual_money'] : 0;
				$data['pic_repay1']	 = isset($v['pic_repay1']) ? $v['pic_repay1'] : '';
				$data['pic_repay2']	 = isset($v['pic_repay2']) ? $v['pic_repay2'] : '';
				$data['pic_repay3']	 = isset($v['pic_repay3']) ? $v['pic_repay3'] : '';
				$data['platform']	 = $v['platform'];
				$data['source']		 = $v['source'];
				$data['status']		 = $v['status'];
				$data['realname']	 = isset($v->user->realname) ? $v->user->realname : '';
				$data['mobile']		 = isset($v->user->mobile) ? $v->user->mobile : '';
				$data['identity']	 = isset($v->user->identity) ? $v->user->identity : '';
				$data['repay_time']	 = $v['repay_time'] ? $v['repay_time'] : '0000-00-00 00:00:00';
				if (isset($v->userloan) && in_array($v->userloan->business_type, [1, 4])) {
					$data['product_source'] = $this->getProductsource($v->userloan);
				} else {
					$data['product_source'] = $this->getProductsource($v->userloan);
					$data['repay_amount_info']	 = $this->getOverdueInfo($v['loan_id']);
				}

				$data['sign']	 = $this->encrySign($data);
				//调用贷后接口
				$url			 = Yii::$app->params['daihou_api_url'] . "/api/loan/setrepayrecord";
				$result			 = (new Curl())->post($url, $data);
				$resultArr		 = json_decode($result, true);
				if ($resultArr['rsp_code'] != '0000') {
					Logger::dayLog('sysloan', '同步还款记录失败', $data);
				}
			}

			Logger::dayLog('returnloanids', '同步还款数据loanID记录', $ids);
		}
	}

	public function getOverdueInfo($loan_id) {
		if (empty($loan_id)) {
			return [];
		}
		$data	 = [];
		$info	 = GoodsBill::find()->where(['loan_id' => $loan_id])->all();
		if (!empty($info)) {
			foreach ($info as $k => $v) {
				$data[$k]['bill_id']		 = $v['bill_id'];
				$data[$k]['repay_amount']	 = $v['repay_amount'];
				$data[$k]['over_principal']	 = $v['over_principal'];
				$data[$k]['over_interest']	 = $v['over_interest'];
				$data[$k]['over_late_fee']	 = $v['over_late_fee'];
			}
		}
		return json_encode($data);
	}

}
