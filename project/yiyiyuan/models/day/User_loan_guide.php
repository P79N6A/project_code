<?php

namespace app\models\day;

use app\commonapi\Logger;
use app\models\BaseModel;
use app\models\news\No_repeat;
use app\models\news\Renewal_payment_record;
use app\models\news\User;
use app\models\news\User_loan;
use Exception;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yi_user_loan_guide".
 *
 * @property string $loan_id
 * @property string $parent_loan_id
 * @property integer $number
 * @property integer $settle_type
 * @property string $user_id
 * @property string $loan_no
 * @property string $amount
 * @property integer $days
 * @property string $start_date
 * @property string $end_date
 * @property integer $type
 * @property integer $status
 * @property integer $prome_status
 * @property string $interest_fee
 * @property string $desc
 * @property string $contract
 * @property string $contract_url
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 * @property string $repay_time
 * @property string $withdraw_fee
 * @property string $chase_amount
 * @property string $coupon_amount
 * @property integer $repay_type
 * @property integer $business_type
 * @property string $withdraw_time
 * @property string $bank_id
 * @property integer $source
 * @property integer $is_calculation
 */
class User_loan_guide extends BaseModel {

	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'qj_user_loan';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['parent_loan_id', 'number', 'settle_type', 'user_id', 'days', 'type', 'status', 'prome_status', 'version', 'repay_type', 'business_type', 'bank_id', 'source', 'is_calculation'], 'integer'],
			[['user_id', 'amount', 'bank_id'], 'required'],
			[['amount', 'interest_fee', 'withdraw_fee', 'chase_amount', 'coupon_amount'], 'number'],
			[['start_date', 'end_date', 'last_modify_time', 'create_time', 'repay_time', 'withdraw_time'], 'safe'],
			[['loan_no', 'contract'], 'string', 'max' => 64],
			[['desc'], 'string', 'max' => 1024],
			[['contract_url'], 'string', 'max' => 128]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'loan_id'			 => 'Loan ID',
			'parent_loan_id'	 => 'Parent Loan ID',
			'number'			 => 'Number',
			'settle_type'		 => 'Settle Type',
			'user_id'			 => 'User ID',
			'loan_no'			 => 'Loan No',
			'amount'			 => 'Amount',
			'days'				 => 'Days',
			'start_date'		 => 'Start Date',
			'end_date'			 => 'End Date',
			'type'				 => 'Type',
			'status'			 => 'Status',
			'prome_status'		 => 'Prome Status',
			'interest_fee'		 => 'Interest Fee',
			'desc'				 => 'Desc',
			'contract'			 => 'Contract',
			'contract_url'		 => 'Contract Url',
			'last_modify_time'	 => 'Last Modify Time',
			'create_time'		 => 'Create Time',
			'version'			 => 'Version',
			'repay_time'		 => 'Repay Time',
			'withdraw_fee'		 => 'Withdraw Fee',
			'chase_amount'		 => 'Chase Amount',
			'coupon_amount'		 => 'Coupon Amount',
			'repay_type'		 => 'Repay Type',
			'business_type'		 => 'Business Type',
			'withdraw_time'		 => 'Withdraw Time',
			'bank_id'			 => 'Bank ID',
			'source'			 => 'Source',
			'is_calculation'	 => 'Is Calculation',
		];
	}

	/**
	 * 乐观所版本号
	 * @return string
	 */
	public function optimisticLock() {
		return "version";
	}

	public function getUser() {
		return $this->hasOne(User_guide::className(), ['user_id' => 'user_id']);
	}

	public function getUser_remit_list_guide() {
		return $this->hasOne(User_remit_list_guide::className(), ['loan_id' => 'loan_id']);
	}

	public function getBank() {
		return $this->hasOne(User_bank_guide::className(), ['id' => 'bank_id']);
	}

	public function getParentremit() {
		return $this->hasOne(User_remit_list_guide::className(), ['loan_id' => 'parent_loan_id']);
	}

	public function repayStatus() {
		$days = $this->days + 1;
		try {
			$this->status			 = 9;
			$this->start_date		 = date('Y-m-d 00:00:00');
			$this->end_date			 = date('Y-m-d 00:00:00', strtotime("+$days" . ' days'));
			$this->last_modify_time	 = date('Y-m-d H:i:s');
			return $this->save();
		} catch (Exception $ex) {
			Logger::dayLog('dayloanstatus', 'repaystatus', $this->id);
			return FALSE;
		}
	}

	/**
	 * 出款锁定，添加出款表
	 * @return boolean
	 */
	public function lock() {
		try {
			$this->status			 = 106;
			$this->last_modify_time	 = date('Y-m-d H:i:s');
			return $this->save();
		} catch (Exception $ex) {
			return FALSE;
		}
	}

	public function lockBatch($loan_ids) {
		try {
			return self::updateAll(['status' => 106], ['loan_id' => $loan_ids, 'status' => 6]);
		} catch (\Exception $ex) {
			return FALSE;
		}
	}

	public function getInitData($limit = 500) {
		$loan = self::find()->where(['status' => 6])->limit($limit)->all();
//        print_r($loan);die;
		return $loan;
	}

	/**
	 * 监测是否可以借款
	 * @param $user_id
	 * @return bool
	 * @author 王新龙
	 * @date 2018/8/2 21:46
	 */
	public function checkCanLoan($user_id) {
		if (empty($user_id)) {
			return false;
		}
		$o_user_guide = (new User_guide())->getById($user_id);
		if (empty($o_user_guide) || empty($o_user_guide->identity)) {
			return false;
		}
		$o_user_credit_guide = (new User_credit_guide())->getByIdentity($o_user_guide->identity);
		if (empty($o_user_credit_guide)) {
			return false;
		}
		$havein_loan_result = $this->getHaveinLoan($user_id);
		if (!empty($havein_loan_result)) {
			return false;
		}
		if (!empty($o_user_guide->identity)) {//@todo 获取一亿元是否有借款
			$yyyUser		 = (new User())->getUserinfoByIdentity($o_user_guide->identity);
			$yyy_loan_result = (new User_loan())->getHaveinLoan($yyyUser->user_id);
			if (!empty($yyy_loan_result)) {
				return false;
			}
			$oRepeatModel	 = new No_repeat();
			$repeat_res		 = $oRepeatModel->norepeat($yyyUser->user_id, 2);
			if (!$repeat_res) {
				return FALSE;
			}
		}
		return true;
	}

	/**
	 * 获取进行中的借款
	 * @param $userid
	 * @return bool|int|mixed
	 * @author 王新龙
	 * @date 2018/8/2 21:51
	 */
	public function getHaveinLoan($userid) {
		if (empty($userid)) {
			return null;
		}
		$status		 = array('6', '9', '11', '12');
		$where		 = [
			'user_id'	 => $userid,
			'status'	 => $status
		];
		$user_loan	 = self::find()->where($where)->orderBy("loan_id asc")->one();
		return !empty($user_loan) ? $user_loan : null;
	}

	/**
	 * 生成借款
	 * @param $condition
	 * @param int $business_type
	 * @return bool|string
	 * @author 王新龙
	 * @date 2018/8/2 21:57
	 */
	public function addUserLoan($condition, $business_type = 7) {
		if (!is_array($condition) || empty($condition)) {
			return false;
		}
		$data						 = $condition;
		$data['number']				 = 0;
		$data['settle_type']		 = 0;
		$data['open_start_date']	 = date('Y-m-d H:i:s');
		$data['open_end_date']		 = $this->getOpenEndTime();
		$data['create_time']		 = date('Y-m-d H:i:s');
		$data['last_modify_time']	 = date('Y-m-d H:i:s');
		$data['version']			 = 1;
		$data['business_type']		 = $business_type;

		$error = $this->chkAttributes($data);
		if ($error) {
			return false;
		}
		$result = $this->save();
		if (!$result) {
			return false;
		}

		$loan_id							 = Yii::$app->db->getLastInsertID();
		$o_user_loan_guide					 = self::findOne($loan_id);
		$o_user_loan_guide->parent_loan_id	 = $loan_id;
		$o_user_loan_guide->save();
		return $loan_id;
	}

	/**
	 * 获取还款金额
	 * @param $o_user_loan_guide
	 * @param $repay_mark 1应还 2未还
	 * @return int
	 * @author 王新龙
	 * @date 2018/8/3 18:16
	 */
	public function getRepayment($o_user_loan_guide, $repay_mark = 1) {
		if (empty($o_user_loan_guide)) {
			return 0;
		}
		$total_amount = $this->getAllMoney($o_user_loan_guide->loan_id);
		if ($o_user_loan_guide->status != 8 || $repay_mark != 1) {
			$already_money = $o_user_loan_guide->getRepayAmount(2);
			if (bccomp($already_money, 0, 2) != 0) {
				$total_amount = bcsub($total_amount, $already_money, 2);
			}
		}
		if ($total_amount * 10000 % 100 != 0) {
			return ceil($total_amount * 100) / 100;
		} else {
			return $total_amount;
		}
	}

	/**
	 * 修改记录
	 * @param $condition
	 * @return bool
	 * @author 王新龙
	 * @date 2018/8/3 17:46
	 */
	public function updateRecord($condition) {
		if (!is_array($condition) || empty($condition)) {
			return false;
		}
		$data						 = $condition;
		$data['last_modify_time']	 = date('Y-m-d H:i:s');
		$error						 = $this->chkAttributes($data);
		if ($error) {
			return false;
		}
		try {
			$result = $this->save();
			return $result;
		} catch (\Exception $ex) {
			return FALSE;
		}
	}

	/**
	 * 获取结束时间
	 * @param string $open_start_date 开始时间
	 * @return false|string
	 * @author 王新龙
	 * @date 2018/8/2 21:57
	 */
	private function getOpenEndTime($open_start_date = '') {
		if (empty($open_start_date)) {
			$hour = date('H');
		} else {
			$hour = date('H', strtotime($open_start_date));
		}
		if ($hour >= 0 && $hour < 9) {
			$open_end_date = date('Y-m-d 15:00:00');
		} else if ($hour < 18) {
			$open_end_date = date('Y-m-d H:i:s', strtotime('+6 hour'));
		} else {
			$open_end_date = date('Y-m-d H:i:s', strtotime('+15 hour'));
		}
		return $open_end_date;
	}

	public function getRepayAmount($type = 1) {
		$parent_id = $this->parent_loan_id;
		if (empty($parent_id)) {
			$type = 1;
		}
		if ($type == 1) {
			$loan_id = $this->loan_id;
			$amount	 = Loan_repay_guide::find()->where(['loan_id' => $loan_id, 'status' => 1])->sum('actual_money');
		} else {//续期
			$loan_id	 = User_loan_guide::find()->select(['loan_id'])->where(['parent_loan_id' => $parent_id])->asArray()->all();
			$loan_ids	 = ArrayHelper::getColumn($loan_id, 'loan_id');
			$amount		 = Loan_repay_guide::find()->where(['loan_id' => $loan_ids, 'status' => 1])->sum('actual_money');
		}
		return $amount;
	}

	public function getAllMoney($loan_id, $type = 1) {
		$loan	 = User_loan_guide::findOne($loan_id);
		$moneys	 = $loan->getMoneyByCalculation();
		if ($type == 2) {
			return $moneys;
		}

		$loan->chase_amount = $this->getChaseamount($loan_id);  //分期后 重置逾期金额
		//逾期返回逾期金额
		if (!empty($loan->chase_amount) && $loan->chase_amount != '0.0000') {
			return $this->getFormatAmount($loan->chase_amount);
		}
		//未逾期
		//status=7模型驳回  3同盾驳回
//        if ($loan->status == 7 || ($loan->prome_status == 1 && $loan->status == 3)) {
//            return $this->getFormatAmount($moneys);
//        }
		//借款正常状态
		if ($loan->is_calculation == 1) {
			$total_amount = $moneys >= $loan->amount ? $moneys : $loan->amount;
		} else {
			$total_amount = $moneys >= ($loan->amount + $loan->withdraw_fee) ? $moneys : ($loan->amount + $loan->withdraw_fee);
		}
		return $this->getFormatAmount($total_amount);
	}

	public function getMoneyByCalculation() {
		if ($this->is_calculation == 1) {
			$moneys = $this->amount + $this->interest_fee;
		} else {
			$moneys = $this->amount + $this->interest_fee + $this->withdraw_fee;
		}
		return $moneys;
	}

	public function getChaseamount($loan_id) {
		if (empty($loan_id) || !is_numeric($loan_id)) {
			return 0;
		}
		$loan = self::findOne($loan_id);
		if (empty($loan)) {
			return 0;
		}
		if (time() <= strtotime($loan->end_date) || !in_array($loan->status, [8, 11, 12, 13])) {
			return 0;
		}
		$overDue = Overdue_loan_guide::find()->where(['loan_id' => $loan->loan_id])->one();
		if (empty($overDue) || empty($overDue->chase_amount)) {
			if ($loan->status == 8) {
				return $loan->chase_amount;
			}
			return $loan->getMoneyByCalculation();
		}
		return $overDue->chase_amount;
	}

	public function getFormatAmount($total_amount) {
		if ($total_amount * 10000 % 100 != 0) {
			return ceil($total_amount * 100) / 100;
		}
		return $total_amount;
	}

	/*
	 * 贷后
	 * 获取用户借款和还款信息
	 */

	public function getUserLoanByUserId($userId) {
		$data = self::find()->from('qj_user_loan as a')
				->leftJoin('qj_loan_repay AS b', 'a.loan_id = b.loan_id')
				->select('a.* , sum(b.actual_money)')
				->where(['a.user_id' => $userId])
				->groupBy('a.loan_id')
				->all();
		return $data;
	}

	/**
	 * 贷后
	 * 根据条件获取逾期信息 
	 */
	public function getLoaninfo($where = []) {
		return self::find()->where($where)->one();
	}

	/*
	 * 贷后
	 * 获取已还款金额
	 */

	public function getRepayAmountByLoanId() {
		$amount = Loan_repay_guide::find()->where(['status' => 1, 'loan_id' => $this->loan_id])->sum('actual_money');
		return $amount > 0 ? $amount : 0;
	}

	/*
	 * 贷后 
	 * 逾前提醒列表
	 */

	public function getLoanBeforeList($loanIds) {
		if (empty($loanIds) || !is_array($loanIds)) {
			return false;
		}
		return self::find()->where(['loan_id' => $loanIds])->all();
	}

	public function changeStatus($status) {
		try {
			$this->status			 = $status;
			$this->last_modify_time	 = date('Y-m-d H:i:s');
			return $this->save();
		} catch (Exception $ex) {
			Logger::dayLog('dayloanstatus', 'repaystatus', $this->id);
			return FALSE;
		}
	}

	public function createRenewLoan($renew_pay_time, $renewalPaymentRecordId) {
		$renewModel	 = new Renew_amount_guide();
		$renew		 = $renewModel->getRenew($this->loan_id, $renew_pay_time);
		if (empty($renew)) {
			return FALSE;
		}
		$parent_loan_id	 = $this->parent_loan_id;
		$parent_loan	 = self::findOne($parent_loan_id);
		$number			 = $this->number + 1;
		$days			 = $this->days + 1;
		$end_date		 = date('Y-m-d 00:00:00', strtotime("+$days days"));
		$new_loan_id	 = (new self)->saveRenewLoan($parent_loan, $end_date, $number, $parent_loan_id);
		if (!empty($new_loan_id)) {
			$condition	 = [
				'settle_type'	 => 2,
				'repay_time'	 => date('Y-m-d H:i:s'),
			];
			$up			 = $this->update_userLoan($condition);
			if ($up) {
				//修改逾期表订单状态
				$over_due = Overdue_loan_guide::find()->where(['loan_id' => $this->loan_id])->one();
				if (!empty($over_due)) {
					$over_due->clearOverdueLoan();
				}
				$res		 = $this->changeStatus(8);
				//向可续期表添加记录
				$renew_res	 = $renewModel->addExtension($parent_loan, $renew, $end_date, $new_loan_id);
				Logger::dayLog('day/renew', $new_loan_id, $renew_res, 'uu', $renewalPaymentRecordId);

				$renewalPaymentRecordObj = Renewal_payment_record_guide::findOne($renewalPaymentRecordId);
				$result					 = $renewalPaymentRecordObj->update_batch(['new_loan_id' => $new_loan_id]);
				Logger::dayLog('day/renew', $new_loan_id, $result);
				if (!$result) {
					return FALSE;
				}

				return $res;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	private function saveRenewLoan($parent_loan, $end_date, $number, $parent_loan_id) {
		$date		 = date('Y-m-d H:i:s');
		$start_date	 = date('Y-m-d 00:00:00');
		foreach ($parent_loan as $key => $value) {
			$renewloan[$key] = $value;
		}
		$renewloan['settle_type']		 = 3;
		$renewloan['like_amount']		 = 0;
		$renewloan['chase_amount']		 = NULL;
		$renewloan['coupon_amount']		 = NULL;
		$renewloan['status']			 = 9;
		$renewloan['number']			 = $number;
		$renewloan['end_date']			 = $end_date;
		$renewloan['parent_loan_id']	 = $parent_loan_id;
		$renewloan['create_time']		 = $date;
		$renewloan['last_modify_time']	 = $date;
		$renewloan['start_date']		 = $start_date;
		$renewloan['repay_time']		 = NULL;
		unset($renewloan['loan_id']);
		$error							 = $this->chkAttributes($renewloan);
		if ($error) {
			return false;
		}
		$res = $this->save();
		if (!$res) {
			return null;
		}
		return $this->loan_id;
	}

	public function update_userLoan($condition) {
		if (empty($condition)) {
			return false;
		}
		$create_time = date('Y-m-d H:i:s');

		if (isset($condition['open_end_date'])) {
			$condition['open_end_date'] = $this->getOpenEndTime($this->open_start_date);
		}
		$condition['last_modify_time']	 = $create_time;
		$error							 = $this->chkAttributes($condition);
		if ($error) {
			return false;
		}
		return $this->save();
	}

	/**
	 * 根据借款状态显示对应的页面
	 */
	public function showPage($old_page = '') {
		if ($this->status == 6) {
			$page = '/day/loan/showloan';
		}
		if ($this->status == 11) {
			$page = '/day/repay/verify';
		}
		if (in_array($this->status, [9, 12])) {
			if ($this->number > 0) {
				$page = '/day/repay';
			} else {
				if (empty($this->user_remit_list_guide) || $this->user_remit_list_guide->remit_status != 'SUCCESS') {
					$page = '/day/loan/showloan';
				} else {
					$page = '/day/repay';
				}
			}
			$repay = Loan_repay_guide::find()->where(['loan_id' => $this->loan_id, 'status' => '-1'])->one();
			if (!empty($repay)) {
				$page = '/day/repay/verify';
			}
		}
		if ($page == $old_page) {
			return NULL;
		}
		return $page;
	}

}
