<?php

namespace app\modules\balance\models\yyy;

use app\models\BaseModel;
use Yii;
use app\commonapi\Common;
use app\commonapi\Logger;

/**
 * This is the model class for table "yi_loan_repay".
 *
 * @property string $id
 * @property string $repay_id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $bank_id
 * @property integer $platform
 * @property integer $source
 * @property string $pic_repay1
 * @property string $pic_repay2
 * @property string $pic_repay3
 * @property integer $status
 * @property string $money
 * @property string $actual_money
 * @property string $pay_key
 * @property string $code
 * @property string $paybill
 * @property string $last_modify_time
 * @property string $createtime
 * @property string $repay_time
 * @property string $repay_mark
 */
class Loan_repay extends YyyBase {

    public $huankuan_amount;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_loan_repay';
    }

    public function getExchange() {
        return $this->hasOne(Exchange::className(), ['loan_id' => 'loan_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'loan_id', 'last_modify_time', 'createtime'], 'required'],
            [['user_id', 'loan_id', 'bank_id', 'platform', 'source', 'status', 'version'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['last_modify_time', 'createtime'], 'safe'],
            [['repay_id', 'pay_key', 'repay_time'], 'string', 'max' => 32],
            [['paybill'], 'string', 'max' => 64],
            [['pic_repay1', 'pic_repay2', 'pic_repay3', 'repay_mark'], 'string', 'max' => 128],
            [['code'], 'string', 'max' => 6]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id'               => 'ID',
            'repay_id'         => 'Repay ID',
            'user_id'          => 'User ID',
            'loan_id'          => 'Loan ID',
            'bank_id'          => 'Bank ID',
            'platform'         => 'Platform',
            'source'           => 'Source',
            'pic_repay1'       => 'Pic Repay1',
            'pic_repay2'       => 'Pic Repay2',
            'pic_repay3'       => 'Pic Repay3',
            'status'           => 'Status',
            'money'            => 'Money',
            'actual_money'     => 'Actual Money',
            'pay_key'          => 'Pay Key',
            'code'             => 'Code',
            'paybill'          => 'Paybill',
            'last_modify_time' => 'Last Modify Time',
            'createtime'       => 'Createtime',
            'repay_time'       => 'Repay Time',
            'repay_mark'       => 'Repay Mark',
        ];
    }

    public function getUserloan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    /**
     * 添加还款记录
     * @author zhangyafeng@xianhuahua.com
     * @date 2017/07/08
     * @param $condition
     * @return bool
     */
    public function save_repay($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data                     = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['createtime']       = date('Y-m-d H:i:s');
        $data['version']          = 1;
        $error                    = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $result = $this->save();
            if ($result) {
                $orderid          = 'Y' . date('mdHis') . $this->id;
                $this['repay_id'] = (string) $orderid;
                $result           = $this->save();
                if ($result) {
                    return $this->id;
                } else {
                    return false;
                }
            }
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    /**
     * 修改还款信息
     * @author zhangyafeng@xianhuahua.com
     * @date 2017/07/08
     * @param $condition
     * @return bool
     */
    public function update_repay($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data                     = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error                    = $this->chkAttributes($data);
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

    /*
     * 检查是否可以还款（loan数据状态，当前loan的repay状态）
     */

    public function check_repay($loaninfo) {
        if (!isset($loaninfo) || empty($loaninfo)) {
            return FALSE;
        }
        $repay_satus = [9, 12, 13];
        if (!in_array($loaninfo['status'], $repay_satus)) {
            return FALSE;
        }
        $repay_info_obj = self::find()->select('status')->where(['loan_id' => $loaninfo['loan_id'], 'status' => -1])->all();
        if (!empty($repay_info_obj)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * 添加还款记录
     */
    public function addRepay($condition) {
        if (empty($condition)) {
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $nowtime                = date('Y-m-d H:i:s');
        $this->last_modify_time = $nowtime;
        $this->createtime       = $nowtime;
        $this->version          = 1;
        try {
            $result = $this->save();
            if ($result) {
                $orderid          = 'Y' . date('mdHis') . $this->id;
                $this['repay_id'] = (string) $orderid;
                $result           = $this->save();
                if ($result) {
                    return $this->id;
                } else {
                    return false;
                }
            }
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    //修改还款信息
    public function updateRepay($condition) {
        if (empty($condition)) {
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->last_modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     *
     * @return type
     */
    public function getRepayByLoanId($loanId) {
        if (empty($loanId)) {
            return null;
        }

        $where = [
            'AND',
            ['status' => '1'],
            ['loan_id' => $loanId],
        ];
        return self::find()->where($where)->all();
    }

    /**
     *
     * @return type
     */
    public function getOfflineRepayByLoanId($loanId) {
        if (empty($loanId)) {
            return null;
        }

        $where = [
            'AND',
//            ['status' => '1'],
            ['loan_id' => $loanId],
            ['!=', 'pic_repay1', ''],
            ['NOT', ['pic_repay1' => null]],
        ];
        return self::find()->where($where)->all();
    }

    /**
     * 根据repay_id获取记录
     * @return type
     */
    public function getRepayByRepayId($repayId) {
        if (empty($repayId)) {
            return null;
        }

        return self::find()->where(['repay_id' => $repayId])->one();
    }

    /**
     * 根据条件获取记录
     * @return type
     * 贷后系统
     */
    public function getRepayByConditions($conditions = []) {
        $repayName     = Loan_repay::tableName();
        $userTableName = User::tableName();
        $loanTableName = User_loan::tableName();
        $where         = [
            'AND',
            [$repayName . '.status' => '1'],
            ['in', $loanTableName . '.status', [12, 13]],
        ];
        if (isset($conditions['repay_id']) && !empty($conditions['repay_id'])) {
            $where[] = [$repayName . '.repay_id' => $conditions['repay_id']];
        }
        if (isset($conditions['loan_id']) && !empty($conditions['loan_id'])) {
            $where[] = [$repayName . '.loan_id' => $conditions['loan_id']];
        }
        if (isset($conditions['mobile']) && !empty($conditions['mobile'])) {
            $where[] = [$userTableName . '.mobile' => $conditions['mobile']];
        }
        if (isset($conditions['realname']) && !empty($conditions['realname'])) {
            $where[] = [$userTableName . '.realname' => $conditions['realname']];
        }
        if (isset($conditions['identity']) && !empty($conditions['identity'])) {
            $where[] = [$userTableName . '.identity' => $conditions['identity']];
        }
        $res = Loan_repay::find()
            ->joinWith('user', true, 'LEFT JOIN')
            ->joinWith('loan', true, 'LEFT JOIN')
            ->where($where)
            ->all();
        return $res;
    }

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['id' => 'bank_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getLog() {
        return $this->hasOne(Manager_logs::className(), ['log_id' => 'id']);
    }

    public function getLoanByTime($startTime, $endTime) {
        $sql = "SELECT a.* from yi_loan_repay as a LEFT JOIN yi_overdue_loan as b  on a.loan_id=b.loan_id where a.last_modify_time >= '$startTime' and a.last_modify_time < '$endTime' and a.status = 1 and b.`id` > 0 GROUP BY repay_id";
        return self::findBySql($sql)->all();
    }

    //查询指定时间点内成功代扣的次数
    public function getWithholdCount($bank_id, $begin_time, $end_time) {

        $condition_day = [
            'AND',
            ['source' => 4],
            ['status' => 1],
            ['bank_id' => $bank_id],
            ['>=', 'createtime', $begin_time],
            ['<=', 'createtime', $end_time],
        ];
        $count_day     = Loan_repay::find()->where($condition_day)->count();
        return $count_day > 0 ? $count_day : 0;
    }

    /**
     * 还款订单失败
     * @return boolean
     */
    public function saveFail() {
        try {
            $this->status           = 4;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result                 = $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $result;
    }

    /**
     * 还款订单失败
     * @return boolean
     */
    public function saveSucc($actual_money, $money = '', $repay_time = '') {
        try {
            $this->status = 1;
            if (!empty($money)) {
                $this->money = $money;
            }
            $this->actual_money     = $money;
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->repay_time       = empty($repay_time) ? date('Y-m-d H:i:s') : $repay_time;
            $result                 = $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $result;
    }

    public function getRepayByOrderId($orderId) {
        if (empty($orderId)) {
            return false;
        }
        $res = self::find()->where(['repay_id' => $orderId])->one();
        return $res;
    }

    //分期还款金额分配，账单结清
    public function stagesRepay($loan_repay, $loan_id = 0) {
        $total_fee = $loan_repay->actual_money;
        if ($loan_id <= 0) {
            $loan_id = $loan_repay->loan_id;
        }
        $loaninfo       = User_loan::find()->where(['loan_id' => $loan_id])->one();
        $userinfo       = $loaninfo->user;
        $huankuan_money = $loaninfo->getStagesAllRepayAmount();

        $clear = false;
        if (bccomp($total_fee, $huankuan_money['total_amount'], 2) >= 0) { //全部结清
            $clear             = true;
            $assignRepayAmount = $huankuan_money;
        } else {
            $assignRepayAmount = $loaninfo->getAssignRepayAmount($huankuan_money, $total_fee);
        }

        $success = true;
        foreach ($assignRepayAmount as $key => $val) {
            //结清 或 已还款金额为0
            if ($val['total'] <= 0 || $key == 'total_amount') {
                continue;
            }
            $billInfo               = GoodsBill::findOne($key);
            $data                   = [];
            $data['repay_amount']   = bcadd($billInfo['repay_amount'], $val['total'], 2);
            $data['over_principal'] = bcadd($val['principal'], $billInfo['over_principal'], 2);
            $data['over_interest']  = bcadd($val['interest'], $billInfo['over_interest'], 2);
            $data['over_late_fee']  = bcadd($val['late_fee'], $billInfo['over_late_fee'], 2);
            //还款金额大于应还款金额 则可以结清
            if (bccomp($val['total'], $val['pleasetotal'], 2) >= 0) {
                //结清账单
                $data['bill_status']   = 8;
                $data['repay_time']    = date("Y-m-d H:i:s");
                $data['actual_amount'] = $data['repay_amount'];
            }
            $saveRes = $billInfo->saveGoodsBill($data);
            if (!$saveRes) {
                Logger::dayLog('new_notify', $loaninfo->loan_id, $data, '更新账单还款金额失败');
                $success = false;
            }
            //如果逾期了 将逾期账单也结清
            if (isset($data['bill_status']) && $data['bill_status'] == 8) {
                $billId      = $billInfo->bill_id;
                $where       = [
                    "AND",
                    ['bill_id' => $billId],
                    ['!=', 'loan_status', 8],
                ];
                $overdusLoan = OverdueLoan::find()->where($where)->one();
                if (!empty($overdusLoan)) {
                    $overdusLoan->clearOverdueLoan();
                    if (!$overdusLoan) {
                        Logger::dayLog('new_notify', $overdusLoan, '更新逾期账单结清状态失败');
                        $success = false;
                    }
                }
            }

            //记录还款对应账单的分配明细
            $detail    = $this->getDetailParams($loan_repay, $billInfo, $val);
            $detailRes = (new BillRepayDetail())->saveDetail($detail);
            if (!$detailRes) {
                Logger::dayLog('new_notify', $detail, '增加还款分配明细记录失败');
                $success = false;
            }
        }

        //还款金额大于等于总应还款金额 结清user_loan
        if ($clear) {
            //结清user_loan
            $times      = date("Y-m-d H:i:s");
            $status     = 8;
            $loanres    = $loaninfo->changeStatus($status);
            $loanresult = $loaninfo->update_userLoan(['repay_type' => 2, 'repay_time' => $times]);
            if ($loanres == false || $loanresult == false) {
                Logger::dayLog('new_notify', $detail, '增加还款分配明细记录失败');
                $success = false;
            }
            $ret = $userinfo->inputWhite($userinfo['user_id']);
        }
        return $success;
    }

    private function getDetailParams($loan_repay, $billInfo, $val) {
        $detail = [
            'bill_repay_id' => $loan_repay->id,
            'repay_id'      => $loan_repay->repay_id,
            'loan_id'       => $loan_repay->loan_id,
            'bill_id'       => $billInfo->id,
            'principal'     => $val['principal'],
            'interest'      => $val['interest'],
            'late_fee'      => $val['late_fee'],
        ];
        return $detail;
    }

    public function add_repay($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data  = $condition;
        $error = $this->chkAttributes($data);
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
     * 判断用户是否支持内还款
     * @param $loanInfo
     * @return bool
     */
    public function payCg($loanInfo) {
//        return false;
        if (empty($loanInfo) || !is_object($loanInfo)) {
            return false;
        }
        $exchangeInfo = Exchange::find()->where(['loan_id' => $loanInfo->loan_id, 'type' => 1])->one();
        //不是体制内，或者是体制内，已经刚兑
        if (!$exchangeInfo || $exchangeInfo->exchange != 0) {
            return false;
        }
        $bank     = (new User_bank())->getBankByUserId($loanInfo->user_id);
        $bank_ids = '';
        foreach ($bank as $v) {
            $bank_ids[] = $v->id;
        }
        //1.逾期 2.还款日当天除6~12点之间 3.还款日之前除6~19点之间
        if (in_array($loanInfo->status, [12, 13])) {
            return false;
        }
        if (in_array($loanInfo->business_type, [5, 6])) {
            return false;
        }
        $nowTime  = date("Y-m-d H:i:s");
        $six      = date("Y-m-d 06:00:00");
        $twelve   = date("Y-m-d 12:00:00");
        $nineteen = date("Y-m-d 19:00:00");
        if ($nowTime > date($loanInfo->end_date, strtotime('-1 day'))) {
            if ($nowTime < $six || $nowTime > $twelve) {
                return false;
            }
        } else {
            if ($nowTime < $six || $nowTime > $nineteen) {
                return false;
            }
        }
        //判断用户卡是否有存管开户卡
        $payAccount = new Payaccount();
        $isAccount  = $payAccount->getPaysuccessByUserId($loanInfo->user_id, 2, 1);
        if (!$isAccount || !in_array($isAccount->card, $bank_ids)) {
            return false;
        }
        return true;
    }

    /*
     * 贷后获取逾前还款列表
     */

    public function getBeforeRepay($repay_id = []) {
        if (empty($repay_id)) {
            return false;
        }
        return self::find()->where(['repay_id' => $repay_id])->all();
    }

}
