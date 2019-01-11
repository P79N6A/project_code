<?php

namespace app\modules\sysloanguide\controllers;

//use app\models\news\OverdueLoan;


use app\models\day\Loan_repay_guide;
use app\models\day\Overdue_loan_guide;
use app\models\day\User_guide;
use app\models\day\User_loan_guide;
use app\models\news\Loan_repay;
use app\models\news\Manager_logs;
use app\models\news\OverdueLoan;
use app\models\news\User_loan;
use app\modules\sysloan\common\ApiController;
use Yii;

class LoanbeforeController extends ApiController {

	public $enableCsrfValidation = false;

	public function actionLoanlist() {

		$required				 = ['loan_id'];  //必传参数
		$httpParams				 = $this->post();  //获取参数
		$verify					 = $this->BeforeVerify($required, $httpParams);
		$httpParams['loan_id']	 = str_replace('6_', '', $httpParams['loan_id']);
		$loanIds				 = json_decode($httpParams['loan_id'], true);
		$loanInfo				 = (new User_loan_guide())->getLoanBeforeList($loanIds);
		$array					 = $this->result('0000', $loanInfo);
		exit(json_encode($array));
	}

	//根据手机号获取loan_id
	public function actionGetloanidbymobile() {
		$required	 = ['mobile'];  //必传参数
		$httpParams	 = $this->post();  //获取参数
		$verify		 = $this->BeforeVerify($required, $httpParams);

		$userinfo		 = (new User_guide)->find()->where(['mobile' => $httpParams['mobile']])->one();  //2499136
		$loans			 = $userinfo->allloan;
		$array			 = $this->errorreback('0000');
		$array['res']	 = array_column($loans, 'loan_id');
		exit(json_encode($array));
	}

	//根据loan_id获取renewal loan信息
	public function actionGetrenewalloaninfo() {
		$required		 = ['loan_id'];  //必传参数
		$httpParams		 = $this->post();  //获取参数
		$verify			 = $this->BeforeVerify($required, $httpParams);
		$select			 = 'amount,chase_amount,interest_fee as fee,days,end_date';
		$loaninfo		 = (new Overdue_loan_guide())->find()->select($select)->where(['loan_id' => $httpParams['loan_id']])->asArray()->one();  //2499136
		$array			 = $this->errorreback('0000');
		$array['res']	 = $loaninfo;
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
			$data[$key]['bill_id']		 = 0;
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
			$getAmount		 = $loanInfo->getRepayAmount();
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
			$data[$key]['loan_id']		 = $this->getPrefixByDays($val) .  $val['loan_id'];
			$data[$key]['business_type'] = $val['business_type'];
			$data[$key]['amount']		 = $val['amount'];
			$data[$key]['days']			 = $val['days'];
			$data[$key]['realname']		 = $val->user['realname'];
			$data[$key]['mobile']		 = $val->user['mobile'];
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

	/**
	 * 为贷后提供账单查询接口
	 */
	public function actionLoaninfos() {
		$required				 = ['loan_id'];  //必传参数
		$httpParams				 = $this->post();  //获取参数
		$verify					 = $this->BeforeVerify($required, $httpParams);
		$httpParams['loan_id']	 = str_replace('6_', '', $httpParams['loan_id']);
		$loanIds				 = json_decode($httpParams['loan_id'], true);
		//测试数据
		$loanInfo				 = (new User_loan_guide())->getLoanBeforeList($loanIds);
		if (empty($loanInfo)) {
			exit(json_encode('账单不存在'));
		}
		foreach ($loanInfo as $k => $val) {
			$data[$k]['loan_id']			 = $val['loan_id'];
			$data[$k]['source']				 = $val['source'];
			$data[$k]['user_id']			 = $val['user_id'];
			$data[$k]['business_type']		 = $val['business_type'];
			$data[$k]['amount']				 = $val['amount'] ? $val['amount'] : 0;
			$data[$k]['days']				 = $val['days'];
			$data[$k]['desc']				 = $val['desc'] ? $val['desc'] : '';
			$data[$k]['start_date']			 = $val['start_date'] ? $val['start_date'] : '0000-00-00 00:00:00';
			$data[$k]['end_date']			 = $val['end_date'] ? $val['end_date'] : '0000-00-00 00:00:00';
			$data[$k]['chase_amount']		 = $this->getLoanChaseamount($val['loan_id']);
			$data[$k]['status']				 = $val['status'];
			$data[$k]['repay_time']			 = $val['repay_time'];
			$data[$k]['is_calculation']		 = $val['is_calculation'];
			$data[$k]['create_time']		 = date("Y-m-d H:i:s", time());
			$data[$k]['last_modify_time']	 = date("Y-m-d H:i:s", time());
			$data[$k]['fee']				 = $val['interest_fee'] > 0 ? $val['interest_fee'] : 0;
			$data[$k]['servic_fee']			 = $val['withdraw_fee'] > 0 ? $val['withdraw_fee'] : 0;
			$data[$k]['bank_id']			 = isset($val->bank->id) ? $val->bank->id : 0;
			$data[$k]['username']			 = isset($val->user->realname) ? $val->user->realname : '';
			$data[$k]['mobile']				 = isset($val->user->mobile) ? $val->user->mobile : '';
			$data[$k]['identity']			 = isset($val->user->identity) ? $val->user->identity : '';
			$data[$k]['prome_score']		 = 0;
			$data[$k]['prome_subject']		 = '';
			$data[$k]['remit_time']			 = isset($val->user_remit_list_guide->last_modify_time) ? $val->user_remit_list_guide->last_modify_time : '0000-00-00 00:00:00';
			$data[$k]['is_more']			 = 1;
			$data[$k]['loan_time']			 = $val['create_time'] ? $val['create_time'] : '0000-00-00 00:00:00';
			$data[$k]['parent_loan_id']		 = $val['parent_loan_id'] ? $val['parent_loan_id'] : '';
			$data[$k]['loan_repay']			 = $this->getLoanRepay($val['loan_id'], $val['business_type']);
			//是否分期
			$data[$k]['product_source']		 = $this ->getProductsource($val);
		}
		$array['rsp_code']		 = '0000';
		$array['loan_info']	 = $data;
		exit(json_encode($array));
	}

	/**
	 * 还款
	 * @param type $loan_id
	 */
	private function getLoanRepay($loan_id) {
		$info	 = Loan_repay_guide::find()->where(['loan_id' => $loan_id, 'status' => 1])->all();
		$data	 = [];
		if (!empty($info)) {
			foreach ($info as $k => $v) {
				$data[$k]['repay_id']		 = $v['repay_id'];
				$data[$k]['loan_id']		 = $v['loan_id'];
				$data[$k]['user_id']		 = $v['user_id'];
				$data[$k]['amount']			 = $v['actual_money'] > 0 ? $v['actual_money'] : 0;
				$data[$k]['pic_repay1']		 = $v['pic_repay1'] ? $v['pic_repay1'] : '';
				$data[$k]['pic_repay2']		 = $v['pic_repay2'] ? $v['pic_repay2'] : '';
				$data[$k]['pic_repay3']		 = $v['pic_repay3'] ? $v['pic_repay3'] : '';
				$data[$k]['platform']		 = $v['platform'];
				$data[$k]['source']			 = $v['source'];
				$data[$k]['status']			 = $v['status'];
				$data[$k]['realname']		 = isset($v->user->realname) ? $v->user->realname : '';
				$data[$k]['mobile']			 = isset($v->user->mobile) ? $v->user->mobile : '';
				$data[$k]['identity']		 = isset($v->user->identity) ? $v->user->identity : '';
				$data[$k]['repay_time']		 = $v['repay_time'] ? $v['repay_time'] : '0000-00-00 00:00:00';
				$data[$k]['product_source']	 = $this ->getProductsource($v->userloan);
			}
		}

		return json_encode($data);
	}

	private function getLoanChaseamount($loan_id) {
		if (empty($loan_id)) {
			return false;
		}
		$where = ['loan_id' => $loan_id];

		$res = (new Overdue_loan_guide())->find()->select('sum(chase_amount) as chase_amount')->where($where)->one();
		return $res['chase_amount'] ? $res['chase_amount'] : 0;
	}

	//逾前决策需要的数据
	public function actionBeforpolicy() {
		$required	 = ['loan_id'];  //必传参数
		$httpParams	 = $this->post();  //获取参数
		$verify		 = $this->BeforeVerify($required, $httpParams);
		$loanIds	 = json_decode($httpParams['loan_id'], true);
		$ovderLoan	 = Overdue_loan_guide::find()->select('amount,user_id,loan_id')->where(['in', 'loan_id', $loanIds])->asArray()->all();
		if (!empty($ovderLoan)) {
			$array['rsp_code']		 = '0000';
			$array['loan_info']	 = $ovderLoan;
			exit(json_encode($array));
		} else {
			$array['rsp_code']		 = '0001';
			$array['loan_info']	 = '';
			exit(json_encode($array));
		}
	}

}
