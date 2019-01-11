<?php

namespace app\models\remit;

use app\models\news\User_loan_extend;
use app\models\news\Plan;
use app\commonapi\Logger;

class Distribution {

    // 当前需要处理的放款列表
    private $loan_lists = [];
    //当前有效的排期计划列表
    private $plan_lists = [];
    //当前成功分配数量
    private $num_success;
    private $oUle;
    private $oPlan;

    /**
     * 初始化 获取借款数据 排期
     *
     * @return void
     */
    public function __construct() {
        $this->oPlan = new Plan();
        $this->oUle = new User_loan_extend();
        $this->plan_lists = $this->getPlans();
        $this->loan_lists = $this->getLoans();
        $this->num_success = 0;
    }

    /**
     * 数据库中获取排期表
     *
     * @return []
     */
    private function getPlans() {
        $plans = $this->oPlan->getPlans();
        if (empty($plans))
            return [];
        return $plans;
    }

    /**
     * 获取当前借款数据
     *
     * @return [] 
     */
    private function getLoans() {
        $loanLists = $this->oUle->getPreLists();
        if (empty($loanLists))
            return [];
        return $loanLists;
    }

    /**
     * 分配
     *
     * @return void
     */
    public function run() {
        //1.获取排期表
        if (empty($this->plan_lists)) {
            Logger::dayLog("distribution", 'getPlans', "排期数据为空");
            return false;
        }

        //2.排期表锁定
        $lock = $this->oPlan->lockPlan($this->plan_lists);
        if (!$lock) {
            Logger::dayLog("distribution", 'lockPlan', "排期数据锁定失败");
            return false;
        }

        //3.刷新排期表中的金额数据
        $this->plan_lists = $this->oPlan->refreshAll($this->plan_lists);
        if (empty($this->plan_lists)) {
            $waitPlan = $this->oPlan->saveWait();
            Logger::dayLog("distribution", 'refreshAll', "刷新排期表中的金额数据失败");
            return false;
        }

        //4.待处理的出款数据
        $loanLists = $this->loan_lists;
        $allNum = count($this->loan_lists);
        if (empty($loanLists)) {
            $waitPlan = $this->oPlan->saveWait();
            Logger::dayLog("distribution", 'loan_lists', "待处理的出款数据为空");
            return false;
        }
        //5.分配
        foreach ($this->loan_lists as $oLoan) {
            if (!$oLoan->loan) {
                continue;
            }

            $result_time = $oLoan->loan->saveEndtime($oLoan->loan->days);
            if (!$result_time) {
                Logger::dayLog("distribution", $oLoan->loan_id, "修改计息时间失败");
                continue;
            }
            if (empty($this->plan_lists))
                break;
            $res = $this->distribute($oLoan);
            if ($res)
                $this->num_success++;
        }

        //排期表状态改为WAIT_STATUS
        $waitPlan = $this->oPlan->saveWait();
        if ($waitPlan < 1) {
            Logger::dayLog("distribution", 'saveWait', "排期数据更改为WAIT_STATUS记录0条");
            return false;
        }
        echo "{$allNum}成功分配的{$this->num_success}";
        Logger::dayLog("distribution", 'distribute', "{$allNum}成功分配的{$this->num_success}");
        return true;
    }

    /**
     * 分配一条借款数据
     *
     * @param [obj] $oLoan
     * @return bool
     */
    private function distribute($oLoan) {
        if (empty($this->plan_lists))
            return false;
        foreach ($this->plan_lists as $key => $oPlan) {
            $hitRes = $oPlan->hitRule();
            if ($hitRes == 2) {
                //将次排期状态改为完成并且删除当前排期置为完成
                $closeRes = $oPlan->saveFinished();
                unset($this->plan_lists[$key]);
                continue;
            } elseif ($hitRes == 3) {
                unset($this->plan_lists[$key]);
                continue;
            }
            //======================
            //一个亿的借款只能从资方11出款
            if ($oLoan->loan->business_type == 10 && !in_array($oPlan->fund, [11])) {
                continue;
            }
            //一亿元的借款不从资方11出款
            if ($oLoan->loan->business_type != 10 && in_array($oPlan->fund, [11])) {
                continue;
            }

            //===========================
            //工厂方法产生资方对象
            $faudObj = CapitalFactory::create($oPlan->fund);
            if (!$faudObj)
                continue;
            //检查订单是否符合当前排期
            $isSupport = $faudObj->isSupport($oLoan);
            if (!$isSupport) {
                continue;
            }

            //分配成功 修改
            $disRes = $oLoan->saveWillRemit($oPlan->fund);
            if (!$disRes)
                continue;
            //累加当前排期金额
            $amount = $oLoan->loan ? $oLoan->loan->amount : 0;
            $addres = $oPlan->addAndRefresh($amount);
            break;
        }
        return true;
    }

}

?>