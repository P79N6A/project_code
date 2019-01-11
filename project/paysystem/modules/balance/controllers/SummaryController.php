<?php
/**
 * 逾期待收统计（2017年）
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23
 * Time: 9:53
 */
namespace app\modules\balance\controllers;
use app\modules\balance\common\COverdue;
use app\modules\balance\models\peanut\Standard_order;
use app\modules\balance\models\peanut\WithdrawOrder;
use app\modules\balance\models\yyy\Insure;
use app\modules\balance\models\yyy\LoanRepay;
use app\modules\balance\models\yyy\OverdueLoan;
use app\modules\balance\models\yyy\Renew_amount;
use app\modules\balance\models\yyy\RenewalPaymentRecord;
use app\modules\balance\models\yyy\User_remit_list;
use app\modules\balance\models\yyy\UserLoan;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class SummaryController extends  AdminController
{
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];
    public function actionList()
    {
        $getData = $this->get();

        $condition = [
            'start_time'        => ArrayHelper::getValue($getData, 'start_time', date("Y-m-d")), //账单日期
            'end_time'          => ArrayHelper::getValue($getData, 'end_time', date("Y-m-d 23:59:59")), //账单日期
        ];

        //出款金额
        $oUser_remit_list = new User_remit_list();
        $totalMoney = $oUser_remit_list->totalMoney($condition);

        //已偿还本金
        $oLoanRepay = new LoanRepay();
        $repayPrincipal = $oLoanRepay->repayPrincipal($condition);

        //实收展期服务费金额
        $oRenew_amount = new RenewalPaymentRecord();
        $renewServer = $oRenew_amount->renewServer($condition);

        //利息
        $settle_fee_money = $oUser_remit_list->settleFee($condition);
        $settleFee = $this->interestRate($totalMoney, $repayPrincipal, $settle_fee_money);

        //实收滞纳金
        $oOverdueLoan = new OverdueLoan();
        $lateFee = $oOverdueLoan->lateFee($condition);
        
        //实收保险手续费返还
        $oInsure = new Insure();
        $insureServer = $oInsure->insureServer($condition);
        $insureServer = bcmul($insureServer, (0.9 + 0.0294),4);
        //收入累计
        $all_money = $renewServer + $settleFee + $lateFee;

        /********待收累计*******/
        //逾期类
        $oOverdueLoan = new OverdueLoan();
        $getData = $oOverdueLoan->getCollectDatas($condition);
        $num = 0;
        $current_amount = 0;
        foreach ($getData as $k => $v) {
            $num++;
            $oUserLoan = new UserLoan();
            $user_loan_info = $oUserLoan->getLoanById(ArrayHelper::getValue($v, 'loan_id'));
            if (!empty($user_loan_info)) {
                $current_amount += $oUserLoan->getRepaymentAmount($user_loan_info, 2); //应还剩余本金
            }
        }

        //应还利息累计
        $withdraw = $oOverdueLoan->getWithdrawFeeSum($condition);
        //滞纳金累计
        $late = $oOverdueLoan->getLateFeeSum($condition);

        /****未到期统计****/
        $oOverdueLoan = new UserLoan();
        $getData = $oOverdueLoan->getCollectDatas($condition);
        //未到期本金
        $amount = array_sum(array_column($getData, 'real_amount'));
        //未到期利息
        $withdraw_fee = array_sum(array_column($getData, 'withdraw_fee'));


        return $this->render('list', [
            'condition'                 => $condition,
            'repayPrincipal'            => number_format($repayPrincipal, 2),
            'renewServer'               => number_format($renewServer, 2),
            'settleFee'                 => number_format($settleFee, 2),
            'lateFee'                   => number_format($lateFee, 2),
            'insureServer'              => number_format($insureServer, 2),
            'settle_fee_money'          => number_format($settle_fee_money, 2),
            'all_money'                 => number_format($all_money, 2),
            'amount'                    => $amount,
            'withdraw_fee'              => $withdraw_fee,
            'current_amount'           => $current_amount,
            'withdraw'                  => $withdraw,
            'late'                      => $late,
        ]);
    }

    /**
     * 实收利息
     * @param $totalMoney       出款金额
     * @param $repayPrincipal   还款金额
     * @param $settleFee        利息
     * @return int
     */
    private function interestRate($totalMoney, $repayPrincipal, $settleFee)
    {
        if (empty($totalMoney) || empty($repayPrincipal) || empty($settleFee)){
            return 0;
        }
        $cal_money = bcsub($repayPrincipal, $totalMoney, 4);
        //（还款金额 - 出款金额） < 0  返回0
        if (bccomp($cal_money, 0) == -1){
            return 0;
        }
        //（还款金额 - 出款金额） < 利息  返回（还款金额 - 出款金额）
        if (bccomp($cal_money, $settleFee) == -1){
            $cal_money;
        }
        //返回利息
        return $settleFee;
    }
}