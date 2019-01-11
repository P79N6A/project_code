<?php

namespace app\commands\sysloannew;

use app\commands\BaseController;
use app\common\Curl;
use app\commonapi\Logger;
use app\models\news\GoodsBill;
use app\models\news\Loan_repay;
use Yii;

/**
 * 同步贷后系统成功还款记录  每10分钟执行一次
 * windows D:phpStudy\php56n\php.exe D:WWW\yyy_loan\yii sysloan/sysloanrepay/index
 * sysloannew/sysloanrepay/index
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SysloanrepayController extends BaseController {

	// 命令行入口文件
	public function actionIndex($startTime='') {
		$time		 = time();
		$endTime	 = date('Y-m-d H:i:00', $time);
        
        if (empty($startTime)) {
            $startTime	 = date('Y-m-d H:i:00', strtotime('-10 minutes'));
		}else{
            $startTime	 = date('Y-m-d H:i:00', $startTime);
        }

		$where	 = [
			'and',
			['=', 'yi_loan_repay.status', 1],
			['>=', 'yi_loan_repay.last_modify_time', $startTime],
			['<', 'yi_loan_repay.last_modify_time', $endTime],
			['>', 'b.id', 0],
		];
		$res	 = (new Loan_repay())->find()->leftJoin('yi_overdue_loan AS b', 'yi_loan_repay.loan_id = b.loan_id')->where($where)->select('yi_loan_repay.*')->groupBy('repay_id')->all();
		if (empty($res)) {
			exit();
		}
		$resArr = array_chunk($res, 500);
		foreach ($resArr as $rk => $rv) {
			$data		 = $postData	 = $ids		 = [];
			foreach ($rv as $key => $v) {
				$ids[]							 = $v['loan_id'];
				$data[$key]['order_id']			 = $v['repay_id'];
				$data[$key]['loan_id']			 = $v['loan_id'];
				$data[$key]['user_id']			 = $v['user_id'];
				$data[$key]['amount']			 = $v['actual_money'] > 0 ? $v['actual_money'] : 0;
				$data[$key]['pic_repay1']		 = isset($v['pic_repay1']) ? $v['pic_repay1'] : '';
				$data[$key]['pic_repay2']		 = isset($v['pic_repay2']) ? $v['pic_repay2'] : '';
				$data[$key]['pic_repay3']		 = isset($v['pic_repay3']) ? $v['pic_repay3'] : '';
				$data[$key]['platform']			 = $v['platform'];
				$data[$key]['source']			 = $v['source'];
				$data[$key]['status']			 = $v['status'];
				$data[$key]['realname']			 = isset($v->user->realname) ? $v->user->realname : '';
				$data[$key]['mobile']			 = isset($v->user->mobile) ? $v->user->mobile : '';
				$data[$key]['identity']			 = isset($v->user->identity) ? $v->user->identity : '';
				$data[$key]['repay_time']		 = $v['repay_time'] ? $v['repay_time'] : '0000-00-00 00:00:00';
				$data[$key]['product_source']	 = $this->getProductsource($v->userloan);
			}
			$postData['data']	 = json_encode($data);
			$postData['sign']		 = $this->encrySign($postData);
			//调用贷后接口
			$url				 = Yii::$app->params['sysloan_api_url'] . "/api/loannew/setrepayrecord";
			$result				 = (new Curl())->post($url, $postData);
			$resultArr			 = json_decode($result, true);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('sysloanrepay', '同步还款记录失败', $data);
			}
		}
	}

}