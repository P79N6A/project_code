<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Crypt3Des;
use app\models\news\ApiSms;
use app\models\news\Cg_remit;
use app\models\news\Loan_repay;
use app\models\news\Sms_depository;
use app\models\news\User;
use app\commonapi\Logger;
use app\models\news\Payaccount;
use app\commonapi\Apidepository;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\news\User_remit_list;
use app\models\news\User_temporary_quota;
use Yii;

class DepositoryapiController extends NewdevController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    //分发
    public function actionDistribute()
    {
        $userId = $this->get('user_id');
        $fromStep = $this->get('from');
        $userInfo = User::findOne($userId);
        if (!$userInfo) {
            return $this->redirect('/new/depositoryapi/app?user_id=' . $userId);
        }

        $payAccount = new Payaccount();
        //判断用户是否存管设置密码
        $isPass = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        if (!$isPass) {
            $setpwd = $this->setpwd($userInfo);
            if (!$setpwd) {
                return $this->redirect('/new/depositoryapi/app?user_id=' . $userId);
            }
            echo $setpwd;
            die;
        }
        return $this->redirect('/new/depositoryapi/app?user_id=' . $userId);
    }

    //中转页
    public function actionAuth()
    {
        $userId = $this->get('user_id');
        $this->getView()->title = '设置中';
        return $this->render('auth',
            [
                'userId' => $userId,
                'csrf' =>$this->getCsrf()
            ]
        );
    }

    //回调app
    public function actionApp()
    {
        $this->layout = 'inv';
        $this->getView()->title = "操作成功";
        return $this->render('app');
    }

    //还款页面
    public function actionRepay()
    {
        $this->getView()->title = "还款确认";
        $key = $this->get('key');
        $repayId = Crypt3Des::decrypt($key, '48HfjalQXzNMIHxaNmvAVWd9jfApGD9v');
        $repayInfo = Loan_repay::findOne($repayId);
        if (!$repayInfo) {
            exit('订单非法');
        }
        return $this->renderPartial('repay', [
            'repay' => $repayInfo,
            'key' => $key
        ]);
    }

    //存管_还款_获取验证码
    public function actionSendcode()
    {
        $mobile = $this->post('mobile');
        $card = $this->post('card');
        //短信次数
        $smsCount = (new Sms_depository())->getSmsCount($mobile, 3);
        if ($smsCount >= 6) {
            $resultArr = array('res_code' => '2', 'rsp_msg' => "您今天获取验证码的次数过多，请明天再试");
            echo json_encode($resultArr);
            exit;
        }
        $condition = [
            'channel' => '000002',
            'mobile' => $mobile,
            'from' => 1,
            'reqType' => strval(2),
            'srvTxCode' => 'directRechargeOnline',
            'cardNo' => $card
        ];
        $depositoryApi = new Apidepository();
        $result = $depositoryApi->sendmsg($condition);
        if (!$result) {
            $arr = [
                'res_code' => '1'
            ];
        } else {
            $arr = [
                'res_code' => '0'
            ];
            (new Sms_depository())->addList(['recive_mobile' => $mobile, 'sms_type' => 3]);
        }
        echo json_encode($arr);
        exit;
    }

    //存管_还款_充值&&冻结
    public function actionDirectpayonline()
    {
        $key = $this->post('key');
        $smsCode = $this->post('smsCode');
        $smsSeq = $this->post('smsSeq');
        if (!$key || !$smsCode || !$smsSeq) {
            $arr = [
                'res_code' => '1',
                'res_msg' => '请输入验证码和序列号'
            ];
            echo json_encode($arr);
            exit;
        }
        $repayId = Crypt3Des::decrypt($key, '48HfjalQXzNMIHxaNmvAVWd9jfApGD9v');
        $repayInfo = Loan_repay::findOne($repayId);
        $loanInfo = User_loan::findOne($repayInfo->loan_id);
        $userInfo = User::findOne($repayInfo->user_id);
        if (!$repayInfo || !$loanInfo || !$userInfo) {
            $arr = [
                'res_code' => '1',
                'res_msg' => '还款异常'
            ];
            echo json_encode($arr);
            exit;
        }
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($repayInfo->user->user_id, 2, 1);

        //充值操作
        $apiDep = new Apidepository();
        $condition = [
            'channel' => '000002',//交易渠道
            'accountId' => $isAccount->accountId,
            'idType' => '01',//01-身份证
            'idNo' => $repayInfo->user->identity,//证件号码
            'name' => $repayInfo->user->realname,
            'mobile' => $repayInfo->user->mobile,
            'from' => 1,
            'cardNo' => $repayInfo->bank->card,
            'txAmount' => sprintf('%.2f', $repayInfo->money),
            'currency' => '156',
            'smsCode' => $smsCode,
            'smsSeq' => $smsSeq,
            'acqRes' => date('Ymdhis') . rand(100000, 999999)
        ];
        $payResult = $apiDep->directpayonline($condition);
        if (!$payResult) {
            $this->setLoanFailStatus($repayInfo, $loanInfo, $userInfo, $payResult);
            $arr = [
                'res_code' => '1',
                'res_msg' => '还款失败'
            ];
            echo json_encode($arr);
            exit;
        }

        //冻结操作
        $condition = [
            'channel' => '000002',//交易渠道
            'accountId' => $isAccount->accountId,
            'orderId' => $repayInfo->repay_id,
            'txAmount' => $payResult['txAmount']
        ];
        $freezeResult = $apiDep->freeze($condition);
        if (!$freezeResult) {
            $arr = [
                'res_code' => '1',
                'res_msg' => '还款失败'
            ];
            echo json_encode($arr);
            exit;
        }
        $result = $this->setLoanStatus($repayInfo, $loanInfo, $userInfo, $payResult);
        if (!$result) {
            $arr = [
                'res_code' => '1'
            ];
        } else {
            $arr = [
                'res_code' => '0',
                'source' => $repayInfo->source
            ];
        }
        echo json_encode($arr);
        exit;
    }

    //提现
    public function actionWithdraw()
    {
        $loanId = $this->get('loan_id');
        $userLoanObj = User_loan::findOne($loanId);
        $userObj = User::findOne($userLoanObj->user_id);
        $settle_amount = $userLoanObj->getActualAmount($userLoanObj->is_calculation, $userLoanObj->amount, $userLoanObj->withdraw_fee);
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userLoanObj->user_id, 2, 1);
        if (!$isAccount) {
            return $this->redirect('/new/depositoryapi/withdrawerror');
        }
        $isPassword = $payAccount->getPaysuccessByUserId($userLoanObj->user_id, 2, 2);
        if (!$isPassword) {
            return $this->redirect('/new/depositoryapi/withdrawerror');
        }
        $cgRemitModel = new Cg_remit();
        $cgRemit = $cgRemitModel->getByLoanId($loanId);
        if ($cgRemit->remit_status != 'WILLREMIT') {
            return $this->redirect('/new/depositoryapi/withdrawerror');
        }
        $remitting = $cgRemit->doremit();
        if (!$remitting) {
            return $this->redirect('/new/depositoryapi/withdrawerror');
        }
        $card = User_bank::findOne($isAccount->card);
        $apiDep = new Apidepository();
        $params = [
            'loan_id' => $loanId,
            'comefrom' => 1,
            'request_no' => date('YmdHis') . rand(1000, 9999),
            'account_id' => $isAccount->accountId,
            'identity' => $userObj->identity,
            'username' => $userObj->realname,
            'card_no' => $card->card,
            'mobile' => $userObj->mobile,
            'withdraw_money' => (string)round($settle_amount, 2),
            'withdraw_fee' => $userLoanObj->is_calculation == 1 ? (string)round($userLoanObj->withdraw_fee, 2) : '0',
            'forgot_pwdurl' => Yii::$app->request->hostInfo . '/borrow/custody/setpwdnew?userid=' . $userObj->user_id . '&from=app',
            'ret_url' => Yii::$app->request->hostInfo . '/new/depositoryapi/app',
            //'isUrl' => 1,
        ];
        $params['isUrl'] = 1;
        Logger::errorLog(print_r($params, true), 'moneyoutopen_post', 'depository');
        $ret_set = $apiDep->moneyoutopen($params);
        if (!$ret_set || $ret_set['rsp_code'] != 0) {
            $cgRemit->willRemit();
            return $this->redirect('/new/depositoryapi/withdrawerror');
        }
        
        return $this->redirect($ret_set['rsp_msg']);
    }

    public function actionWithdrawerror()
    {
        $this->getView()->title = '提现失败';
        return $this->render('withdrawerror');
    }

    /**
     * ajax_获取设置密码结果
     * @return string 1成功 2失败
     */
    public function actionGetsetpwd()
    {
        $userId = $this->post('user_id');
        $payAccount = new Payaccount();
        $isPass = $payAccount->getPaysuccessByUserId($userId, 2, 2);
        $result = [
            'res_code' => 2,
            'res_msg' => '设置失败'
        ];
        if ($isPass) {
            $result = [
                'res_code' => 1,
                'res_msg' => '设置成功'
            ];
        }
        return json_encode($result);
    }

    //设置密码
    private function setpwd($userInfo)
    {
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if (!$isAccount) {
            return false;
        }
        $add_condition = [
            "user_id" => $userInfo->user_id,
            'type' => 2,
            'step' => 2,
            'accountId' => $isAccount->accountId,
        ];
        $add_res = $payAccount->add_list($add_condition);
        if (!$add_res) {
            return false;
        }
        $apiDep = new Apidepository();
        $params = [
            'from' => 1,
            'channel' => '000002',
            'accountId' => $isAccount->accountId,
            'idType' => '01',
            'idNo' => $userInfo->identity,
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'retUrl' => Yii::$app->request->hostInfo . '/new/depositoryapi/auth?user_id=' . $userInfo->user_id,
            'notifyUrl' => Yii::$app->request->hostInfo . '/new/getsetpassnotify',
        ];
        $ret_set = $apiDep->pwdset($params);
        if (!$ret_set) {
            return false;
        }
        return $ret_set;
    }

    //授权
    private function setAuth($userInfo)
    {
        $payAccount = new Payaccount();
        $account = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if (!$account) {
            return false;
        }
        $ispass = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        if (!$ispass) {
            return false;
        }
        $payAccount = new Payaccount();
        $condition = [
            "user_id" => $userInfo->user_id,
            'type' => 2,
            'step' => 3,
            'activate_result' => 0,
            'accountId' => $account->accountId,
            'sign' => 1,
            //'card' => $account->card,
        ];
        $addRes = $payAccount->add_list($condition);
        if (!$addRes) {
            return false;
        }
        $apiDep = new Apidepository();
        $params = [
            'channel' => '000002',//交易渠道
            'accountId' => $account->accountId,
            'from' => 1,
            'orderId' => date('YmdHis') . rand(1000, 9999),
            'agreeWithdraw' => '1',//开通预约取现功能标志
            'autoBid' => '1',//开通自动投标功能标志
            'autoTransfer' => '1',//开通自动债转功能标志
            'directConsume' => '1',//开通无密消费功能标识
            'forgotPwdUrl' => Yii::$app->request->hostInfo . '/new/forgot?userid=' . $userInfo->user_id . '&from=app',
            'transactionUrl' => Yii::$app->request->hostInfo . '/new/depositoryapi/app?user_id=' . $userInfo->user_id,
            'notifyUrl' => Yii::$app->request->hostInfo . '/new/getauthnotify',
        ];
        $ret_open = $apiDep->auth($params);
        return $ret_open;
    }

    private function setLoanStatus($loan_repay, $loaninfo, $userinfo, $payResult)
    {
        if (!empty($loan_repay) && empty($loan_repay->paybill) && $payResult) {
            $transaction = Yii::$app->db->beginTransaction();
            $time = date('Y-m-d H:i:s');
            $repay_condition = [
                'status' => 1,
                'platform' => 20,
                'actual_money' => round($payResult['txAmount'], 2),
                'paybill' => $payResult['acqRes'],
                'repay_time' => $time
            ];
            $ret = $loan_repay->update_repay($repay_condition);
            if (!$ret) {
                Logger::dayLog('depository_notify', $loan_repay->repay_id, '更新还款订单失败', $repay_condition);
                $transaction->rollBack();
                return false;
            }
            $huankuan_money = $loaninfo->getRepaymentAmount($loaninfo);
            if (bccomp($huankuan_money, 0) == 1) {
                $transaction->commit();
                //发送还款成功通知
                if ($loan_repay->source != 4) {
                    $this->sendSms($userinfo['mobile'], $loaninfo, $payResult['txAmount'], 1);
                }
                return true;
            }
            //还款结清
            $change_ret = $loaninfo->changeStatus(8);
            if (!$change_ret) {
                Logger::dayLog('depository_notify', $loaninfo->loan_id, '更新借款状态为8失败');
                $transaction->rollBack();
                return false;
            }
            $loan_condition = [
                'settle_type' => $loaninfo->settle_type == 3 ? 1 : $loaninfo->settle_type,
                'repay_type' => 2,
                'repay_time' => $time
            ];
            $loan_ret = $loaninfo->update_userLoan($loan_condition);
            if (!$loan_ret) {
                $transaction->rollBack();
                Logger::dayLog('depository_notify', $loaninfo->loan_id, '更新借款信息失败');
                return false;
            }
            if (in_array($loaninfo->business_type, [1, 4, 5, 6])) {
                $userModel = new User();
                $userModel->inputWhite($loaninfo->user_id);
            }
            //发送还款成功通知
            if ($loan_repay->source != 4) {
                $this->sendSms($userinfo['mobile'], $loaninfo, $payResult['txAmount'], 1);
            }
            $transaction->commit();
            return true;
        } else {
            return false;
        }
    }

    private function setLoanFailStatus($repayInfo, $loanInfo, $userInfo, $payResult)
    {
        $conditon = [
            'status' => 4,
        ];
        $up_result = $repayInfo->update_repay($conditon);
        //发送还款成功通知
        if ($up_result) {
            $this->sendSms($userInfo->mobile, $loanInfo, $payResult['txAmount'], 2);
            return true;
        }
        Logger::dayLog('depository_notify', $loanInfo->loan_id, '更新还款status=>4失败');
        return false;
    }

    /**
     * 借款在线还款结果短信通知用户
     * @param type $mobile 接收短信的手机号
     * @param type $loan 借款
     * @param type $type 1、支付成功，2、支付失败
     */
    private function sendSms($mobile, $loaninfo, $amount, $type = 2)
    {
        $newLoaninfo = User_loan::findOne($loaninfo->loan_id);
        $huankuan_money = $newLoaninfo->getRepaymentAmount($loaninfo, 2);
        Logger::dayLog('repay_notify', 'huankuan_money', $huankuan_money);
        $apiSms = new ApiSms();
        switch ($type) {
            case 1:
                if (bccomp($huankuan_money, 0) == 1) {
                    $res = $apiSms->sendRepaymentPortionSms($mobile, $amount, $huankuan_money);
                } else {
                    $res = $apiSms->sendRepaymentAllSms($mobile);
                }
                break;
            case 2:
                $res = $apiSms->sendRepaymentFailedSms($mobile, $huankuan_money);
                break;
        }
    }

    public function getCsrf()
    {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }
}
