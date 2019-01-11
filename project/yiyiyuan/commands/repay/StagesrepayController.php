<?php

namespace app\commands\repay;

use app\commands\BaseController;
use app\commonapi\ApiSms;
use app\commonapi\Logger;
use app\models\news\BillRepay;
use app\models\news\Loan_repay;
use app\models\news\User_loan;

/**
 * 分期还款异步 定时处理还款金额分配，修改借款状态
 */

/**
 *   linux : /data/wwwroot/yiyiyuan/yii remit/msgpush stagesrepay
 */
class StagesrepayController extends BaseController {

    public function actionIndex() {
        $limit     = 100;
        $repayInfo = BillRepay::find()->where(['status' => '-1'])->orderBy('id asc')->limit($limit)->all();
        $successNum = (new BillRepay())->updateBillRepayAll($repayInfo);

        foreach ($repayInfo as $key => $obj) {
            $this->stagesRepaySuccess($obj);
        }
    }

    private function stagesRepaySuccess($loan_repay) {

        $res = $loan_repay->updateBillRepay(); //加乐观锁修改成20
        if (!$res) {
            Logger::dayLog('commands_stagesrepay', '修改分期还款记录状态为1失败', $loan_repay->id);
        }
        $total_fee      = $loan_repay->actual_money;
        $loan_id        = $loan_repay->loan_id;
        $res = (new Loan_repay()) ->stagesRepay($loan_repay);
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        $mobile   = $loaninfo->user->mobile;
        $this->sendSms($mobile, $loaninfo, $total_fee, 1);
    }

    /**
     * 借款在线还款结果短信通知用户
     * @param type $mobile 接收短信的手机号
     * @param type $loan 借款
     * @param type $type 1、支付成功，2、支付失败
     */
    private function sendSms($mobile, $loaninfo, $amount, $type = 2, $leftAmount = 0) {
        $newLoaninfo    = User_loan::findOne($loaninfo->loan_id);
        $huankuan_money = $newLoaninfo->getRepaymentAmount($loaninfo, 2);
        Logger::dayLog('repay_notify', 'huankuan_money', $huankuan_money);
        $apiSms         = new ApiSms();
        switch ($type) {
            case 1:
                if (bccomp($huankuan_money, 0, 2) > 0) {
                    $res = $apiSms->sendSmsByRepaymentPortion($mobile, $amount, $huankuan_money);
                } else {
                    $res = $apiSms->sendSmsByRepaymentAll($mobile);
                }
                break;
            case 2:
                $res = $apiSms->sendSmsByRepaymentFailed($mobile, $huankuan_money);
                break;
        }
    }



}
