<?php

namespace app\modules\sysloanguide\controllers;

//use app\models\news\OverdueLoan;


use app\models\day\Loan_repay_guide;
use app\models\day\Overdue_loan_guide;
use app\models\day\User_loan_guide;
use app\models\news\GoodsBill;
use app\models\news\Manager_logs;
use app\models\news\OverdueLoan;
use app\models\news\User_loan;
use app\modules\sysloan\common\ApiController;
use Yii;

class LoanController extends ApiController {

	public $enableCsrfValidation = false;

	public function actionLoanlist() {

		$required	 = ['user_id'];  //必传参数
		$httpParams	 = $this->post();  //获取参数

		$verify		 = $this->BeforeVerify($required, $httpParams);
		$loanInfo	 = (new User_loan_guide)->getUserLoanByUserId($httpParams['user_id']);
		$array		 = $this->result('0000', $loanInfo);
		exit(json_encode($array));
	}

	/*
	 * 获取账单逾期金额
	 *
	 */

	public function actionLoaninfo() {
		$required	 = ['loan_id'];  //必传参数
		$httpParams	 = $this->post();  //获取参数
		$verify		 = $this->BeforeVerify($required, $httpParams);

		$overdueInfo = (new Overdue_loan_guide())->getLoaninfo(['=', 'loan_id', $httpParams['loan_id']]);
		if (empty($overdueInfo)) {
			$array = $this->errorreback('10048');
			exit(json_encode($array));
		} else {
			$array			 = $this->errorreback('0000');
			$chase_amount	 = $overdueInfo->chase_amount > 0 ? $overdueInfo->chase_amount : 0;
			$array['info']	 = ['chase_amount' => $chase_amount];
			exit(json_encode($array));
		}
	}

	/*
	 * 获取账单逾期金额
	 *
	 */

	public function actionLoanchaseamount() {
		$required	 = ['loan_id'];  //必传参数
		$httpParams	 = $this->post();  //获取参数
		$verify		 = $this->BeforeVerify($required, $httpParams);
		$loanIds	 = json_decode($httpParams['loan_id'], true);
		if (!is_array($loanIds)) {
			$array = $this->errorreback('99994');
			exit(json_encode($array));
		}
		$loanInfo = (new Overdue_loan_guide())->find()->where(['loan_id' => $loanIds])->all();
		if (empty($loanInfo)) {
			$array = $this->errorreback('10048');
			exit(json_encode($array));
		}
		$array = $this->resultamount('0000', $loanInfo);
		exit(json_encode($array));
	}

	private function resultamount($code, $object) {
		$array				 = $this->errorreback($code);
		$array['loan_list']	 = [];
		if (empty($object)) {
			return $array;
		}
		foreach ($object as $key => $val) {
			$data[$key]['loan_id']		 = $this->getPrefixByDays($val) . $val['loan_id'];
			$data[$key]['bill_id']		 = '';
			$data[$key]['chase_amount']	 = $val['chase_amount'];
		}
		$array['loan_list'] = $data;
		return $array;
	}

	/**
	 * 修改借款状态
	 * @param type $code
	 * @param type $object
	 * @return array
	 */
	public function actionChangeloanstatus() {
		$required	 = ['loan_id', 'status', 'admin_id', 'realname'];  //必传参数
		$httpParams	 = $this->post();  //获取参数
		$verify		 = $this->BeforeVerify($required, $httpParams);

		$loanInfo = (new User_loan_guide())->getById($httpParams['loan_id']);
		if (empty($loanInfo)) {
			$array = $this->errorreback('10048');
			exit(json_encode($array));
		}
		$transaction = Yii::$app->db->beginTransaction();

		if ($httpParams['status'] == 8) { //结清
			//将逾期表状态改为结清
			if (in_array($loanInfo->business_type, [1])) {
				//查询逾期表中是否存在
				$overdueInfos = (new Overdue_loan_guide())->getLoaninfo(['=', 'loan_id', $loanInfo->loan_id]);
				if (!empty($overdueInfos)) {
					$res = $overdueInfos->clearOverdueLoan();
				}
			}
			if ($loanInfo->status == 8) { //已结清，不做任何操作
				$array = $this->errorreback('60011');
				exit(json_encode($array));
			}
			//应还金额
			$amount			 = $loanInfo['amount'];
			//已还金额
			$getAmount		 = $loanInfo->getRepayAmountByLoanId();
			$alreadyAmount	 = intval($getAmount * 10000);
			if ($amount > $alreadyAmount) {  //应还金额大于已还金额 不能结清
				$array = $this->errorreback('60003');
				exit(json_encode($array));
			}
		}


		//查询用户最后一次还款时间
		$repayInfo	 = Loan_repay_guide::find()->where(['loan_id' => $loanInfo->loan_id, 'status' => 1])->orderBy('id desc')->one();
		$repayTime	 = '';
		if (!empty($repayInfo)) {
			$repayTime				 = $repayInfo->repay_time;
			$loanInfo->repay_time	 = $repayInfo->repay_time;
			$loanSave				 = $loanInfo->save();
			if (!$loanSave) {
				$transaction->rollBack();
				$array = $this->errorreback('60002');
				exit(json_encode($array));
			}
		}

		$statusRes = $loanInfo->updateRecord(['status' => $httpParams['status']]);
		if (!$statusRes) {
			$transaction->rollBack();
			$array = $this->errorreback('60002');
			exit(json_encode($array));
		}
		$condition	 = array(
			'admin_id'		 => $httpParams['admin_id'],
			'admin_name'	 => $httpParams['realname'],
			'operation_type' => 2,
			'log_id'		 => $loanInfo->loan_id,
		);
		$result_log	 = (new Manager_logs)->updateManagerlogs($condition);
		if (!$result_log) {
			$transaction->rollBack();
			$array = $this->errorreback('60007');
			exit(json_encode($array));
		}
		$transaction->commit();
		$array			 = $this->errorreback('0000');
		$array['data']	 = ['repay_time' => $repayTime];
		exit(json_encode($array));
	}

	private function result($code, $object) {
		$array				 = $this->errorreback($code);
		$array['loan_list']	 = [];
		if (empty($object)) {
			return $array;
		}
		foreach ($object as $key => $val) {
			$flows						 = Overdue_loan_guide::find()->where(['loan_id' => $val['loan_id'], 'loan_status' => 12])->one();
			$is_overdue					 = empty($flows) ? 1 : 2;
			$data[$key]['loan_id']		 = $this->getPrefixByDays($val) . $val['loan_id'];
			$data[$key]['business_type'] = $val['business_type'];
			$data[$key]['amount']		 = $val['amount'];
			$data[$key]['days']			 = $val['days'];
			$data[$key]['desc']			 = $val['desc'];
			$data[$key]['loan_time']	 = $val['create_time'];
			$data[$key]['end_date']		 = $val['end_date'];
			$data[$key]['repay_time']	 = $val['repay_time'];
			$data[$key]['repay_amount']	 = isset($val['repay_amount']) && $val['repay_amount'] > 0 ? $val['repay_amount'] : 0;
			$data[$key]['is_overdue']	 = $is_overdue;
		}
		$array['loan_list'] = $data;
		return $array;
	}

	public function actionDetail() {
		$required	 = ['loan_id'];  //必传参数
		$httpParams	 = $this->post();  //获取参数
		$verify		 = $this->BeforeVerify($required, $httpParams);

		$loanInfo		 = User_loan_guide::find()->where(['loan_id' => $httpParams['loan_id']])->one();
		$total_amount	 = $loanInfo->getRepayment($loanInfo);  //应还款金额
		$pay_amount		 = $loanInfo->getRepayAmount();  //已还款金额
		$data			 = [
			'amount'		 => $loanInfo['amount'],
			'user_id'		 => $loanInfo['user_id'],
			'days'			 => $loanInfo['days'],
			'bank_id'		 => $loanInfo['bank_id'],
			'desc'			 => $loanInfo['desc'],
			'start_date'	 => $loanInfo['start_date'],
			'end_date'		 => $loanInfo['end_date'],
			'repay_amount'	 => $total_amount, //   应还
			'status'		 => $loanInfo['status'],
			'servic_fee'	 => 0,
			'fee'			 => $loanInfo['interest_fee'],
			'source'		 => $loanInfo['source'],
			'business_type'	 => $loanInfo['business_type'],
			'late_amount'	 => $loanInfo['chase_amount'], // 滞纳金
			'paid_amount'	 => $pay_amount, // 已还
			'pay_amount'	 => isset($loanInfo->user_remit_list_guide->real_amount) ? $loanInfo->user_remit_list_guide->real_amount : '0', // 出款金额
			'remit_time'	 => isset($loanInfo->user_remit_list_guide->remit_time) ? $loanInfo->user_remit_list_guide->remit_time : '0', // 出款时间
			'card_number'	 => isset($loanInfo->user_remit_list_guide->bank) ? $loanInfo->user_remit_list_guide->bank->card : '', // 出款银行卡
		];

		$array	 = $this->errorreback('0000');
		$array	 = array_merge($array, $data);
		exit(json_encode($array));
	}

	public function actionTdloaninfo() {
		$required	 = ['loan_id'];  //必传参数
		$httpParams	 = $this->post();  //获取参数
		$verify		 = $this->BeforeVerify($required, $httpParams);

		$loanInfo		 = User_loan_guide::find()->where(['loan_id' => $httpParams['loan_id']])->one();
		$total_amount	 = $loanInfo->getRepayment($loanInfo);
		$data			 = [
			'username'		 => $loanInfo->user->realname,
			'identity'		 => $loanInfo->user->identity,
			'mobile'		 => $loanInfo->user->mobile,
			'end_date'		 => $loanInfo['end_date'],
			'chase_amount'	 => $total_amount //   应还
		];

		$array	 = $this->errorreback('0000');
		$array	 = array_merge($array, $data);
		exit(json_encode($array));
	}

	/*
	 * 逾前机器人获取数据接口
	 */

	public function actionRobotdata() {
		$required	 = ['loan_id'];  //必传参数
		$httpParams	 = $this->post();  //获取参数
		$verify		 = $this->BeforeVerify($required, $httpParams);
		$loanIds	 = json_decode($httpParams['loan_id'], true);
		$data		 = [];
		foreach ($loanIds as $key => $val) {
			$loanInfo		 = User_loan_guide::find()->where(['loan_id' => $val])->one();
			$total_amount	 = $loanInfo->getRepayment($loanInfo);
			if (empty($loanInfo) || empty($loanInfo->user)) {
				continue;
			}
			$data[$key] = [
				'username'		 => $loanInfo->user->realname,
				'mobile'		 => $loanInfo->user->mobile,
				'money'			 => $total_amount, //   应还
				'loan_id'		 => $loanInfo->loan_id,
				'product_source' => $this->getProductsource($loanInfo)
			];
		}

		$array				 = $this->errorreback('0000');
		$array['rsp_data']	 = $data;
		exit(json_encode($array));
	}

}
