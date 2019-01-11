<?php

namespace app\commands\sysloannew;

use app\commands\BaseController;
use app\common\Curl;
use app\common\Logger;
use app\models\news\OverdueLoan;
use Yii;

/**
 * 处理历史数据  只执行一次
 * sysloannew/overduehistory
 * D:\phpStudy\php\php-7.0.12-nts\php.exe D:\phpStudy\WWW\yiyiyuan\yii sysloannew/overduehistory
 */
set_time_limit(0);
ini_set('memory_limit', '-1');

class OverduehistoryController extends BaseController {

	public $enableCsrfValidation = false;

	public function actionIndex() {
		$loan_ids  = [29052891,29064223,29065569,29066009,29066657,29066688,29066735,29066797,29066852,29066937,29066940,29066999,29067969,29068161,29068257,29068329,29068387,29068820,29068968,29069056,29069095,29069491,29069504,29069625,29069848,29069904,29070204,29070219,29070404,29070486,29070545,29070978,29071192,29071322,29071515,29080531,29059215,29065912,29071514,29071694,29071798,29071849,29072033,29072396,29073431,29073649,29073712,29073768,29073849,29074162,29074367,29074369,29074404,29074606,29074828,29074975,29074991,29075448,29075534,29075598,29075726,29075751,29075924,29076190,29076328,29076693,29076731,29076825,29077284,29077822,29078105,29210880,29065314,29072963,29073288,29073341,29075430,29078690,29078744,29079282,29079361,29079399,29079547,29079608,29079660,29080246,29080322,29080771,29081233,29081405,29082557,29082595,29082667,29083554,29083773,29084124,29219260,29076004,29076147,29076818,29077159,29077377,29077390,29077767,29079262,29080101,29081366,29082773,29083599,29083800,29084631,29084734,29085338,29085487,29086038,29086634,29086845,29086912,29087002,29087655,29087827,29088189,29088212,29088389,29088811,29089053,29089961,29087332,29088969,29090697,29091029,29091626,29092009,29092067,29092718,29093049,29093586,29093745,29094043,29094269,29094897,29095025,29095072,29095229,29095274,29095465,29095671,29095955,29095973,29096935,29097131,29097431,29087121,29093633,29093919,29094549,29096740,29097508,29098122,29098981,29098993,29099006,29099106,29099136,29099142,29099241,29099609,29099951,29100004,29100009,29100755,29100804,29100965,29101293,29101651,29102171,29102605,29102635,29103642,29227284,29232669,29241710,29103548,29104630,29104763,29105682,29106285,29106362,29106441,29106960,29107264,29107332,29107365,29108311,29109091,29109286,29109794,29110319,29119802,29123659,29109898,29110006,29110742,29111211,29111319,29112418,29112574,29112784,29112796,29112992,29113021,29113069,29113614,29114343,29114574,29114637,29114744,29093124,29257297,29257663,29116665,29122215,29122382,29123715,29123724,29123857,29126112,29114859,29130358,29132074,29133667,29136324,29284567,29115742,29141835,29156086,29157019,29158142,29159701,29160053,29147231,29147454,29150339,29150732,29151287,29152446,29153071,29153470,29153698,29161851,29163733,29305054,29305331,29115792,29119538,29155622,29156266,29156977,29158029,29158358,29165567,29166161,29168303,29159111,29160059,29160936,29161146,29162234,29163284,29163390,29170751,29171041,29172216,29173095,29124154,29125162,29126674,29128223,29128606,29129925,29129927,29161177,29164164,29165547,29165714,29166455,29166778,29166920,29176887,29177095,29177760,29177836,29178116,29180315,29181180,29181533,29181819,29181925,29182173,29326291,29170052,29183325,29183417,29183700,29183749,29183863,29184420,29184767,29184901,29186376,29186515,29186557,29186587,29187264,29187847,29336146,29337065];
		$where		 = [
			'and',
			['!=', 'loan_status', 8],
			['in', 'loan_id', $loan_ids],
		];
		$user_loan	 = OverdueLoan::find()->where($where)->all();
		if (empty($user_loan)) {
			exit();
		}
		$postArr = array_chunk($user_loan, 500);  //批量推送
		foreach ($postArr as $pk => $pv) {
			$data		 = $postData	 = [];
			foreach ($pv as $key => $val) {
				if (empty($val->userloan->parentremit) || $val->userloan->parentremit['remit_status'] != 'SUCCESS') {
					continue;
				}
				$data[$key]['loan_id']			 = $val['loan_id'];
				$data[$key]['source']			 = $val['source'];
				$data[$key]['user_id']			 = $val['user_id'];
				$data[$key]['business_type']	 = $val['business_type'];
				$data[$key]['amount']			 = $val['amount'] ? $val['amount'] : 0;
				$data[$key]['days']				 = $val['days'];
				$data[$key]['desc']				 = $val['desc'] ? $val['desc'] : '';
				$data[$key]['start_date']		 = $val['start_date'] ? $val['start_date'] : '0000-00-00 00:00:00';
				$data[$key]['end_date']			 = $val['end_date'] ? $val['end_date'] : '0000-00-00 00:00:00';
				$data[$key]['chase_amount']		 = $val['chase_amount'] > 0 ? $val['chase_amount'] : 0;
				$data[$key]['status']			 = $val['loan_status'];
				$data[$key]['repay_time']		 = $val['repay_time'];
				$data[$key]['is_calculation']	 = $val['is_calculation'];
				$data[$key]['create_time']		 = date("Y-m-d H:i:s", time());
				$data[$key]['last_modify_time']	 = date("Y-m-d H:i:s", time());
				$data[$key]['fee']				 = $val['interest_fee'] > 0 ? $val['interest_fee'] : 0;
				$data[$key]['servic_fee']		 = $val['withdraw_fee'] > 0 ? $val['withdraw_fee'] : 0;
				$data[$key]['bank_id']			 = isset($val->bank->id) ? $val->bank->id : 0;
				$data[$key]['username']			 = isset($val->user->realname) ? $val->user->realname : '';
				$data[$key]['mobile']			 = isset($val->user->mobile) ? $val->user->mobile : '';
				$data[$key]['identity']			 = isset($val->user->identity) ? $val->user->identity : '';
				$data[$key]['prome_score']		 = isset($val->promes) && !empty($val->promes) ? $val->promes->prome_score : 0;
				$data[$key]['prome_subject']	 = isset($val->promes) && !empty($val->promes) ? $val->promes->prome_subject : '';
				$data[$key]['remit_time']		 = isset($val->remit->last_modify_time) ? $val->remit->last_modify_time : '0000-00-00 00:00:00';
				$data[$key]['is_more']			 = $val['is_push'] == 1 ? 2 : 1;
				$data[$key]['loan_time']		 = !empty($val->userloan['create_time']) ? $val->userloan['create_time'] : '0000-00-00 00:00:00';
				$data[$key]['parent_loan_id']	 = !empty($val->userloan['parent_loan_id']) ? $val->userloan['parent_loan_id'] :'';
				$data[$key]['product_source']	 = $this->getProductsource($val);
			}
			if (empty($data)) {
				continue;
			}
			$postData['data']	 = json_encode($data);
			$postData['sign']	 = $this->encrySign($postData);
			$url				 = Yii::$app->params['sysloan_api_url'] . "/api/loannew/saveoverdueloan";
			$result				 = (new Curl())->post($url, $postData);
			$resultArr			 = json_decode($result, true);
			var_dump($resultArr);
			if ($resultArr['rsp_code'] != '0000') {
				Logger::dayLog('saveoverdueloan', '同步逾期账单', $postData['data']);
			}
		}
	}

}
