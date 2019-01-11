<?php

namespace app\modules\sysloan\controllers;

//use app\models\news\OverdueLoan;


use app\commonapi\Common;
use app\models\news\GoodsBill;
use app\models\news\Loan_repay;
use app\models\news\Manager_logs;
use app\models\news\OverdueLoan;
use app\models\news\User;
use app\models\news\User_loan;
use app\modules\sysloan\common\ApiController;
use Yii;

class LoanController extends ApiController {

	public $enableCsrfValidation = false;

	public function actionLoanlist() {

		$required	 = ['user_id'];  //必传参数
		$httpParams	 = $this->post();  //获取参数

		$verify		 = $this->BeforeVerify($required, $httpParams);
		/*		 * *************记录访问日志beigin************* */
		$ip			 = Common::get_client_ip();
		$result_log	 = Common::saveLog('LoanController', 'actionLoanlist', $ip, 'sysloan', $httpParams['user_id']);
		/*		 * *************记录访问日志end**************** */


		$loanInfo	 = (new User_loan)->getUserLoanByUserId($httpParams['user_id']);
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
		/*		 * *************记录访问日志beigin************* */
		$ip			 = Common::get_client_ip();
		$result_log	 = Common::saveLog('LoanController', 'actionLoaninfo', $ip, 'sysloan', $httpParams['loan_id']);
		/*		 * *************记录访问日志end**************** */

		$overdueInfo = (new OverdueLoan())->getLoaninfo(['=', 'loan_id', $httpParams['loan_id']]);
		if (empty($overdueInfo)) {
			$overdueInfo = (new OverdueLoan())->getLoaninfo(['=', 'bill_id', $httpParams['loan_id']]);
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
	}

	/*
	 * 获取账单逾期金额
	 *
	 */

	public function actionLoanchaseamount() {
		$required	 = ['loan_id'];  //必传参数
		$httpParams	 = $this->post();  //获取参数
		$verify		 = $this->BeforeVerify($required, $httpParams);
		/*		 * *************记录访问日志beigin************* */
		$ip			 = Common::get_client_ip();
		$result_log	 = Common::saveLog('LoanController', 'actionLoanchaseamount', $ip, 'sysloan', $httpParams['loan_id']);
		/*		 * *************记录访问日志end**************** */
		$loanIds	 = json_decode($httpParams['loan_id'], true);
		if (!is_array($loanIds)) {
			$array = $this->errorreback('99994');
			exit(json_encode($array));
		}
		$loanInfo = (new OverdueLoan())->find()->where(['loan_id' => $loanIds])->all();
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
			$data[$key]['bill_id']		 = $val['bill_id'];
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
		/*		 * *************记录访问日志beigin************* */
		$ip			 = Common::get_client_ip();
		$result_log	 = Common::saveLog('LoanController', 'actionChangeloanstatus', $ip, 'sysloan', $httpParams['loan_id']);
		/*		 * *************记录访问日志end**************** */

		$loanInfo = (new User_loan)->getById($httpParams['loan_id']);
		if (empty($loanInfo)) {
			$array = $this->errorreback('10048');
			exit(json_encode($array));
		}
		$transaction = Yii::$app->db->beginTransaction();

		if ($httpParams['status'] == 8) { //结清
			//将逾期表状态改为结清
			if (in_array($loanInfo->business_type, [1, 4])) {
				//查询逾期表中是否存在
				$overdueInfos = (new OverdueLoan())->getLoaninfo(['=', 'loan_id', $loanInfo->loan_id]);
				if (!empty($overdueInfos)) {
					$res = $overdueInfos->clearOverdueLoan();
				}
			}
			if ($loanInfo->status == 8) { //已结清，不做任何操作
				$array = $this->errorreback('60011');
				exit(json_encode($array));
			}
			//应还金额
			if ($loanInfo->is_calculation == 1) {
				$amount = intval(($loanInfo['amount'] + $loanInfo['interest_fee']) * 10000);
			} else {
				$amount = intval(($loanInfo['amount'] + $loanInfo['interest_fee'] + $loanInfo['withdraw_fee']) * 10000);
			}
			//已还金额
			$getAmount		 = $loanInfo->getRepayAmount(2);
			$getAmount		 = empty($getAmount) ? 0 : $getAmount;
			$alreadyAmount	 = intval($getAmount * 10000);
			if ($amount > $alreadyAmount) {  //应还金额大于已还金额 不能结清
				$array = $this->errorreback('60003');
				exit(json_encode($array));
			}
			//如果修改装填是结清（status = 8）那么直接加入白名单
			$userInfo = User::findOne($loanInfo['user_id']);
			if (!empty($userInfo) && $userInfo->status == 3) {
				$userModel	 = new User();
				$whiteRes	 = $userModel->inputWhite($loanInfo['user_id']);
				if (!$whiteRes) {
					$transaction->rollBack();
					$array = $this->errorreback('60015');
					exit(json_encode($array));
				}
			}
		}


		//查询用户最后一次还款时间
		$repayInfo	 = Loan_repay::find()->where(['loan_id' => $loanInfo->loan_id, 'status' => 1])->orderBy('id desc')->one();
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

		$statusRes = $loanInfo->changeStatus($httpParams['status'], $httpParams['admin_id']);
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

	/**
	 * 修改分期订单的状态
	 */
	public function actionChangebillstatus() {
		$required	 = ['loan_id', 'bill_id', 'status', 'admin_id', 'realname'];  //必传参数
		$httpParams	 = $this->post();  //获取参数
		$verify		 = $this->BeforeVerify($required, $httpParams);
//        $httpParams = [
//            'loan_id'  => '223726592',
//            'bill_id'  => json_encode(["W201711280243221333", "W201711280243221332"]),
//            'status'   => 8,
//            'admin_id' => 1,
//            'realname' => 'admin',
//        ];
		if (empty($httpParams['bill_id'])) {   //缺少订单id参数
			exit(json_encode($this->errorreback('60016')));
		}
		$bill_ids = json_decode($httpParams['bill_id'], true);

		//查询最早一起没结清的借款
		$where		 = [
			'and',
			['loan_id' => $httpParams['loan_id']],
			['!=', 'bill_status', 8],
		];
		$billsInfo	 = GoodsBill::find()->where($where)->select('bill_id')->orderBy('id')->asArray()->one();
		if (!empty($billsInfo) && !in_array($billsInfo['bill_id'], $bill_ids)) {
			$array = $this->errorreback('60020');
			exit(json_encode($array));
		}

		$loanInfo = (new User_loan)->getById($httpParams['loan_id']);
		if (empty($loanInfo)) {
			$array = $this->errorreback('10048');
			exit(json_encode($array));
		}


		//找出符合条件的bill逾期本金+利息
		$bill_model	 = new GoodsBill();
		$bill_info	 = $bill_model->getBillAmount($httpParams['loan_id'], $bill_ids);
		$bill_amount = $bill_info['principal'] + $bill_info['interest'];
		$getAmount	 = $bill_info['repay_amount'];
		if ($getAmount < $bill_amount) {   //借款金额大于还款金额不能结清
			$array = $this->errorreback('60003');
			exit(json_encode($array));
		}

		GoodsBill::updateAll(['bill_status' => 8], ['bill_id' => $bill_ids]); //修改分期订单（goods_bill）状态
		OverdueLoan::updateAll(['loan_status' => 8], ['bill_id' => $bill_ids]);  //修改分期账单(overdue_loan)状态
		//如果是全部分期都结清那么修改订单表的状态
		$overdue_count = $bill_model->getTotalNum(['loan_id' => $httpParams['loan_id'], 'bill_status' => 12]);
		if ($overdue_count == 0) {  //所有订单已结清则更给user_loan的状态
			$res = $loanInfo->changeStatus(8);
			if (!$res) {
				$array = $this->errorreback('60019');
				exit(json_encode($array));
			}
		}
		$array = $this->errorreback('0000');
		exit(json_encode($array));
	}

	private function result($code, $object) {
		$array				 = $this->errorreback($code);
		$array['loan_list']	 = [];
		if (empty($object)) {
			return $array;
		}
		foreach ($object as $key => $val) {
			$flows						 = OverdueLoan::find()->where(['loan_id' => $val['loan_id'], 'loan_status' => 12])->one();
			$is_overdue					 = empty($flows) ? 1 : 2;
			$data[$key]['loan_id']		 = $this->getPrefixByDays($val) . $val['loan_id'];
			$data[$key]['business_type'] = $val['business_type'];
			$data[$key]['amount']		 = $val['amount'];
			$data[$key]['days']			 = $val['days'];
			$data[$key]['desc']			 = $val['desc'];
			$data[$key]['amount']		 = $val['amount'];
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

		$loanInfo				 = User_loan::find()->where(['loan_id' => $httpParams['loan_id']])->one();
		$total_amount			 = $loanInfo->getRepaymentAmount($loanInfo);
		$loanInfo->chase_amount	 = $loanInfo->getChaseamount($loanInfo['loan_id']);  //分期后 重置逾期金额
		$pay_amount				 = $loanInfo->getRepayAmount(2);
		$data					 = [
			'amount'		 => $loanInfo['amount'],
			'user_id'		 => $loanInfo['user_id'],
			'days'			 => $loanInfo['days'],
			'bank_id'		 => $loanInfo['bank_id'],
			'desc'			 => $loanInfo['desc'],
			'start_date'	 => $loanInfo['start_date'],
			'end_date'		 => $loanInfo['end_date'],
			'repay_amount'	 => $total_amount, //   应还
			'status'		 => $loanInfo['status'],
			'servic_fee'	 => $loanInfo['withdraw_fee'],
			'fee'			 => $loanInfo['interest_fee'],
			'source'		 => $loanInfo['source'],
			'business_type'	 => $loanInfo['business_type'],
			'late_amount'	 => $loanInfo['chase_amount'], // 滞纳金
			'paid_amount'	 => $pay_amount, // 已还
			'pay_amount'	 => isset($loanInfo->remit->real_amount) ? $loanInfo->remit->real_amount : '0', // 出款金额
			'remit_time'	 => isset($loanInfo->remit->remit_time) ? $loanInfo->remit->remit_time : '0', // 出款时间
			'card_number'	 => isset($loanInfo->remit->bank) ? $loanInfo->remit->bank->card : '', // 出款银行卡
		];

		$array	 = $this->errorreback('0000');
		$array	 = array_merge($array, $data);
		exit(json_encode($array));
	}

	public function actionTdloaninfo() {
		$required	 = ['loan_id'];  //必传参数
		$httpParams	 = $this->post();  //获取参数
		$verify		 = $this->BeforeVerify($required, $httpParams);

		$loanInfo		 = User_loan::find()->where(['loan_id' => $httpParams['loan_id']])->one();
		$total_amount	 = $loanInfo->getRepaymentAmount($loanInfo);
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

		$loanIds = json_decode($httpParams['loan_id'], true);
		$data	 = [];
		foreach ($loanIds as $key => $val) {
			$loanInfo		 = User_loan::find()->where(['loan_id' => $val])->one();
			$total_amount	 = $loanInfo->getRepaymentAmount($loanInfo);
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
