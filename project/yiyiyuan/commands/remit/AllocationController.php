<?php

namespace app\commands\remit;

use Yii;
use app\commonapi\Logger;
use app\models\news\User_loan_extend;
use app\models\remit\Distribution;
use app\models\news\GlobalLock;

class AllocationController extends \app\commands\BaseController {

    //资方
    private $faudType = 1;

    /**
     * AUTHED -> PRE-REMIT
     *
     * @return void
     */
    public function runPre() {
        $last_modify_time = date('Y-m-d H:i:s', time() - 86400 * 2);
        $now_time = date('Y-m-d H:i:s');
        $sucess = 0;
        $oUle = new User_loan_extend();
        $loanLists = $oUle->getAuthedLists();
        $nums = is_array($loanLists) ? count($loanLists) : 0;
        Logger::dayLog('allocation', 'run-pre', $last_modify_time . ' to ' . $now_time . '共获取出款条数' . $nums);
        if (empty($loanLists))
            return 0;
        //2 锁定状态,避免下次重复处理，先将user_loan_extend的状态改为预处理状态PREREMIT，再将user_loan的借款状态改为9，批量添加状态变更记录
        $flow_nums = $oUle->batchSetPreremit($loanLists);
        Logger::dayLog('allocation', 'run-pre', $last_modify_time . ' to ' . $now_time . '插入到user_loan_flows条数' . $flow_nums);
        echo $flow_nums;
    }

    /**
     * 分配
     *
     * @return void
     */
    public function runDistribute() {
        // 1.全局表加锁
        $oGlock = new GlobalLock();
        $fandLock = $oGlock->getByType($this->faudType);
        if (!$fandLock) {
            return false;
        }
        $lockRes = $fandLock->setOptimistic();
        if (!$lockRes) {
            Logger::dayLog("allocation", 'setOptimistic', "全局锁锁定失败");
            return false;
        }
        //2.分配
        $oDistribute = new Distribution();
        $res = $oDistribute->run();
        //3.释放全局锁
        $unlockRes = $fandLock->unsetOptimistic();
        if (!$unlockRes) {
            Logger::dayLog("allocation", 'setOptimistic', "全局锁释放失败");
            return false;
        }
    }

}
