<?php

namespace app\modules\renew\controllers;

use app\commonapi\Logger;
use app\models\news\Loan_repay;
use app\models\news\Payaccount;
use app\models\news\Renew_amount;
use app\models\news\Renew_record;
use app\models\news\Renewal_payment_record;
use app\models\news\User;
use app\models\news\User_loan;
use Yii;

class RenewalController extends RenewbaseController {

    public $layout = 'data';
    public $enableCsrfValidation = false;
    private $paytype = 'wrenewal';

    /**
     * 只有登陆帐号才可以访问
     * 子类直接继承
     */
    public function behaviors() {
        $loan_id = $this->get('loan_id');
        $renew = (new Renew_amount())->getRenew($loan_id);
        if (!empty($renew) && $renew->type == 3) {
            //登录
            $user_info = User::find()->select(['openid', 'user_id', 'mobile'])->where(['user_id' => $renew->user_id])->one();
            Yii::$app->renew->login($user_info, 1);
            Yii::$app->newDev->login($user_info, 1);
        } else {
            return parent::behaviors();
        }
        return [];
    }

    public function actionIndex() {
        $this->getView()->title = '续期';
        $loan_id = Yii::$app->request->get('loan_id');
        $loan = User_loan::findOne($loan_id);
        if (empty($loan)) {
            return $this->redirect('/new/loan');
        }
        $renewModel = new Renew_amount();
        $renew_amount = $renewModel->getRenew($loan->loan_id);
        if (empty($renew_amount) || $renew_amount->type != 3) {
            return $this->redirect('/new/loan');
        }
        $jsinfo = $this->getWxParam();

        //还款时间
        $end_date = (new User_loan())->getHuankuanTime($loan->status, $loan->end_date);
        //判断用户有没有开户、绑卡、设置密码
        $isCungan = (new Payaccount())->isCunguan($loan->user_id,1);

        return $this->render('cards', [
                    'jsinfo' => $jsinfo,
                    'isCungan' => $isCungan,
                    'loan' => $loan,
                    'end_date' => $end_date,
                    'csrf' => $this->getCsrf(),
        ]);
    }

    public function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

    public function actionRepay() {
        $loan_id = Yii::$app->request->post('loan_id');
        //判断借款是否存在
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (empty($loaninfo)) {
            return json_encode(['code' => '10090', 'msg' => '数据错误']);
            exit;
        }
        $renewModel = new Renew_amount();
        $is_allow = $renewModel->getRenew($loaninfo->loan_id);
        if (!$is_allow || $loaninfo->status == 8) {
            return json_encode(['code' => '10090', 'msg' => '续期失败,请登录一亿元app进行续期']);
            exit;
        }
        $insure = Renewal_payment_record::find()->where(['loan_id' => $loaninfo->loan_id])->orderBy('id desc')->one();
        if (!empty($insure)) {
            if (in_array($insure->status, [-1])) {
                return json_encode(['code' => '10090', 'msg' => '续期失败,请登录一亿元app进行续期']);
                exit;
            }
            $time = strtotime(date('Y-m-d H:i:s', time())) - strtotime(date($insure->create_time, time()));
            if ($time < 120) {
                return json_encode(['code' => '10107', 'msg' => '续期失败,请稍后尝试']);
                exit;
            }
        }
        $repay = Loan_repay::find()->where(['loan_id' => $loaninfo->loan_id, 'status' => '-1'])->orderBy('id desc')->one();
        if (!empty($repay)) {
            return json_encode(['code' => '10107', 'msg' => '续期失败,请稍后尝试']);
            exit;
        }

        //判断用户有没有开户、绑卡、设置密码
        $isCungan = (new Payaccount())->isCunguan($loaninfo->user_id);
        if ($isCungan['isOpen'] == 0) {
            return json_encode(['code' => '10001', 'msg' => '1']);
            exit;
        } else if ($isCungan['isPass'] == 0) {
            return json_encode(['code' => '10001', 'msg' => '2']);
            exit;
        }
        if ($isCungan['isAuth'] == 0) {
            return json_encode(['code' => '10001', 'msg' => '3']);
            exit;
        }
        $result = $this->runCunguanRenew($loaninfo, 1);
        Logger::dayLog('notify/renew', 'apirepay', $result);
        if ($result['rsp_code'] != '0000') {
            return json_encode(['code' => $result['rsp_code'], 'msg' => '续期失败,请稍后尝试']);
            exit;
        }
        if (isset($result['url'])) {
            $array['redirect_url'] = $result['url'];
            return json_encode(['code' => '0000', 'msg' => '', 'url' => $result['url']]);
            exit;
        }
        return json_encode(['code' => '5000', 'msg' => '续期失败,请稍后尝试',]);
        exit;
    }

    private function renewEntrustpay($loan, $source = 2) {
        $oRenewAmountModel = new Renew_amount();
        $result = $oRenewAmountModel->entrustpay($loan, $source);
        if (!$result) {
            return FALSE;
        }
        return $result;
    }

    private function renewEntrustloan($oRecord, $loan) {
        $oRenewAmountModel = new Renew_amount();
        $result = $oRenewAmountModel->entrustloan($loan);
//        var_dump($result);
//        die;
        if (!$result) {
            $oRecord->updateRegistration(11);
            return FALSE;
        }
        $oRecord->updateRegistration(6);
        return $result;
    }

    private function runCunguanRenew($loaninfo, $source) {
        $oRenewRecordModel = new Renew_record();
        $oRenewRecord = $oRenewRecordModel->getRecordByLoanId($loaninfo->loan_id);
        if (empty($oRenewRecord)) {
            $result = $loaninfo->createInvalidloan();
            if (!$result) {
                return ['rsp_code' => '10089'];
            }
            $oRenewRecord = $oRenewRecordModel->getRecordByLoanId($loaninfo->loan_id);
        }
        $loan_new = User_loan::findOne($oRenewRecord->loan_id_new);
        if ($oRenewRecord->registration != 6) {
            $result = $this->renewEntrustloan($oRenewRecord, $loan_new);
            if (!$result) {
                return ['rsp_code' => '0000'];
            }
            $oRenewRecord->refresh();
        }
        if (in_array($oRenewRecord->authorize, [1, 6, 11])) {
            return ['rsp_code' => '0000'];
        }
        $result = $this->renewEntrustpay($loan_new, $source);
        if (!$result) {
            return ['rsp_code' => '10089'];
        }
        $oRenewRecord->refresh();
        $url = $result['rsp_msg'];
        return ['rsp_code' => '0000', 'url' => $url];
    }

    public function actionRenewal() {
        $this->getView()->title = '提交成功';
        $this->layout = 'data';
        $jsinfo = $this->getWxParam();
        return $this->render('renewalsuccess', ['jsinfo' => $jsinfo]);
    }

}
