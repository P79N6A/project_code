<?php

namespace app\modules\balance\models\yyy;

use app\modules\balance\common\COverdue;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yi_overdue_loan".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $user_id
 * @property string $bill_id
 * @property string $bank_id
 * @property string $loan_no
 * @property string $amount
 * @property string $current_amount
 * @property integer $days
 * @property string $desc
 * @property string $start_date
 * @property string $end_date
 * @property integer $loan_type
 * @property integer $loan_status
 * @property string $interest_fee
 * @property string $contract
 * @property string $contract_url
 * @property string $late_fee
 * @property string $withdraw_fee
 * @property string $chase_amount
 * @property integer $is_push
 * @property integer $business_type
 * @property integer $source
 * @property integer $is_calculation
 * @property string $repay_time
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class OverdueLoan extends YyyBase
{
    public $accountId;
    public $mobile;
    public $fund;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_overdue_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'user_id', 'bank_id', 'amount', 'current_amount', 'days', 'desc', 'start_date', 'end_date', 'loan_status', 'interest_fee', 'contract', 'contract_url', 'is_push', 'business_type', 'source', 'is_calculation', 'repay_time', 'create_time', 'last_modify_time'], 'required'],
            [['loan_id', 'user_id', 'bank_id', 'days', 'loan_type', 'loan_status', 'is_push', 'business_type', 'source', 'is_calculation', 'version'], 'integer'],
            [['amount', 'current_amount', 'interest_fee', 'late_fee', 'withdraw_fee', 'chase_amount'], 'number'],
            [['start_date', 'end_date', 'repay_time', 'create_time', 'last_modify_time'], 'safe'],
            [['bill_id', 'loan_no'], 'string', 'max' => 64],
            [['desc', 'contract_url'], 'string', 'max' => 128],
            [['contract'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'user_id' => 'User ID',
            'bill_id' => 'Bill ID',
            'bank_id' => 'Bank ID',
            'loan_no' => 'Loan No',
            'amount' => 'Amount',
            'current_amount' => 'Current Amount',
            'days' => 'Days',
            'desc' => 'Desc',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'loan_type' => 'Loan Type',
            'loan_status' => 'Loan Status',
            'interest_fee' => 'Interest Fee',
            'contract' => 'Contract',
            'contract_url' => 'Contract Url',
            'late_fee' => 'Late Fee',
            'withdraw_fee' => 'Withdraw Fee',
            'chase_amount' => 'Chase Amount',
            'is_push' => 'Is Push',
            'business_type' => 'Business Type',
            'source' => 'Source',
            'is_calculation' => 'Is Calculation',
            'repay_time' => 'Repay Time',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
            'mobile' => 'Mobile',
        ];
    }

    /**
     * 逾期待收条件
     * @param $condition
     * @return bool|static
     */
    private function collectWhere($condition)
    {
        if (empty($condition)){
            return false;
        }
        $overdue_tablename = self::tableName();
        $loan_tablename = UserLoan::tableName();
        $user_tablename = User::tableName();
        $account_table = PayAccount::tableName();
        $remit_tablename = User_remit_list::tableName();
        $result = self::find()
            ->from($overdue_tablename)
            ->select([
                'id'                => $overdue_tablename.'.id',
                'mobile'            => $user_tablename.".mobile", //手机号
                'loan_id'           => $overdue_tablename.'.loan_id',
                'days'              => $overdue_tablename.'.days', //天数
                'start_date'        => $overdue_tablename.'.start_date', //到期日
                'end_date'          => $overdue_tablename.'.end_date', //到期日
                'amount'            => $overdue_tablename.'.amount', //借款金额
                'interest_fee'      => $overdue_tablename.'.interest_fee',//借款利息总额
                'accountId'         => $account_table.'.accountId',
                'withdraw_fee'      => $overdue_tablename.'.withdraw_fee',//提现手续费
                'late_fee'          => $overdue_tablename.'.late_fee',//滞纳金费用
                'create_time'       => $overdue_tablename.'.create_time',
                'fund'              => $remit_tablename.'.fund',
                'repay_time'        => $overdue_tablename.'.repay_time',

            ])
            ->leftJoin($remit_tablename, $remit_tablename.'.loan_id='.$overdue_tablename.".loan_id")
            ->leftJoin($loan_tablename, $loan_tablename.'.loan_id='.$overdue_tablename.'.loan_id')
            ->leftJoin($user_tablename, $user_tablename.'.user_id='.$overdue_tablename.'.user_id')
            ->leftJoin($account_table, $account_table.'.user_id='.$overdue_tablename.'.user_id')
            ->where([
                'AND',
                ['!=', $loan_tablename.'.status', 8],
                ['=', $remit_tablename.'.remit_status', "SUCCESS"],
                ['=', $account_table.'.activate_result', 0]
            ]);

        //借款编号
        if (!empty($condition['loan_id'])){
            $result->andWhere(['=', $overdue_tablename.'.loan_id', $condition['loan_id']]);
        }
        //业务类型
        if (!empty($condition['days'])){
            $result->andWhere(['=', $overdue_tablename.'.days', $condition['days']]);
        }
        //逾期类型
        if (!empty($condition['overdue_type'])){
            $oCOverdue = new COverdue();
            $overdueDay = $oCOverdue->overdueDay($condition['overdue_type']);
            $result->andWhere(['<', $overdue_tablename.'.end_date', date("Y-m-d", strtotime("-$overdueDay[0] days")). ' 00:00:00']);
            if (!empty($overdueDay[1])) {
                $result->andWhere(['>=', $overdue_tablename . '.end_date', date("Y-m-d", strtotime("-$overdueDay[1] days")) . ' 23:59:59']);
            }
        }
        //手机号
        if (!empty($condition['mobile'])){
            $result->andWhere(['=', $user_tablename.'.mobile', $condition['mobile']]);
        }
        if (!empty($condition['start_time'])){
            $result->andWhere(['>=', $overdue_tablename.'.end_date', $condition['start_time']. ' 00:00:00']);
        }
        if (!empty($condition['end_time'])){
            $result->andWhere(['<=', $overdue_tablename.'.end_date', $condition['end_time']. ' 23:59:59']);
        }
        //存管电子账户
        if (!empty($condition['accountId'])){
            $result->andWhere(['=', $account_table.'.accountId', $condition['accountId']]);
        }

        //分期类型types_of_stages

        //资金方
        if (!empty($condition['capital_side'])){
            $result->andWhere(['=', $remit_tablename.'.fund', $condition['capital_side']]);
        }

        $result->groupBy("yi_user_loan.loan_id");
        return $result;
    }

    /**
     * 逾期待收总笔数
     * @param $condition
     * @return int
     */
    public function getCollectCount($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $result = $this->collectWhere($condition);
        $total = $result->count();
        return empty($total) ? 0 : $total;
    }
    /*
     * 根据借款前后置获取金额
     */

    public function getMoneyByCalculation() {
        if ($this->is_calculation == 1) {
            $moneys = $this->amount + $this->interest_fee;
        } else {
            $moneys = $this->amount + $this->interest_fee + $this->withdraw_fee;
        }
        return $moneys;
    }

    /**
     * 逾期待收统计数据
     * @param $pages
     * @param $condition
     * @return array|bool
     */
    public function getCollectData($pages, $condition)
    {
        if (empty($condition) || empty($pages)){
            return false;
        }
        $result = $this->collectWhere($condition);
        return $result->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
    }

    /**
     * 逾期待收统计数据
     * @param $pages
     * @param $condition
     * @return array|bool
     */
    public function getCollectDatas($condition)
    {
        if (empty($condition)){
            return false;
        }
        $result = $this->collectWhere($condition);
        return $result->asArray()
            ->all();
    }

    /**
     * 逾期待收统计数据下载
     * @param $condition
     * @return bool
     */
    public function getCollectDataDown($condition)
    {
        if (empty($condition)){
            return false;
        }
        $result = $this->collectWhere($condition)->all();
        return $result;
    }

    /**
     * 借款本金累计
     * @param $condition
     * @return int
     */
    public function getAmountSum($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $result = $this->collectWhere($condition);
        $total = $result->sum('amount');
        return empty($total) ? 0 : $total;
    }

    /**
     * 获取逾期数据
     */
    public function getOverdueData()
    {
        $loan_name = UserLoan::tableName();  //yi_user_loan表
        $overdue_name = self::tableName();
        $where_config = [
            'AND',
            ['!=', $loan_name.'.status', 8],
        ];
        $result = self::find()
            ->leftJoin(UserLoan::tableName(), $loan_name.'.loan_id='.$overdue_name.'.loan_id')
            ->where($where_config)
            ->limit(10)
            ->all();
        return $result;
    }

    /**
     * 应还利息累计
     * @param $condition
     * @return int
     */
    public function getWithdrawFeeSum($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $result = $this->collectWhere($condition);
        $total = $result->sum('withdraw_fee');
        return empty($total) ? 0 : $total;
    }

    /**
     * 滞纳金累计
     * @param $condition
     * @return int
     */
    public function getLateFeeSum($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $result = $this->collectWhere($condition);
        $total = $result->sum('late_fee');
        return empty($total) ? 0 : $total;
    }

    /**
     * 获取滞纳金根据loan_id
     * @param $loanId
     * @return int|mixed
     */
    public function getLateFeeByLoanId($loanId) {
        if (!is_numeric($loanId) || empty($loanId)) {
            return 0;
        }
        $info = self::find()->where(['loan_id' => $loanId, 'loan_status' => [12, 13]])->sum('late_fee');
        return !empty($info) ? $info : 0;
    }

    /**
     * 滞纳金
     * @param $condition
     * @return int|mixed
     */
    public function lateFee($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $where_config = [
            'AND',
            ['>=', 'create_time', ArrayHelper::getValue($condition, 'start_time')],
            ['<=', 'create_time', ArrayHelper::getValue($condition, 'end_time')],
        ];
        $total = self::find()->where($where_config)->sum('late_fee');
        return empty($total) ? 0 : $total;
    }

}