<?php

namespace app\modules\api\controllers\controllers310;

use app\commonapi\Apidepository;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Coupon_list;
use app\models\news\Loan_repay;
use app\models\news\Payaccount;
use app\models\news\PayAccountError;
use app\models\news\PayAccountExtend;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_credit;
use app\models\news\User_loan;
use app\modules\api\common\ApiController;
use Yii;

class DepositoryController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $is_repay = Yii::$app->request->post('is_repay', 1); //1借款调用 2还款调用
        if (empty($version) || empty($user_id)) {
            exit($this->returnBack('99994'));
        }

        $userInfo = User::findOne($user_id);
        if (empty($userInfo)) {
            exit($this->returnBack('10001'));
        }

        //判断先花商城中订单及借款状况
        if($is_repay == 1){
            $shop_res = (new User_credit())->getshopOrder($userInfo);
            if(!$shop_res){
                exit($this->returnBack('10246'));
            }
        }
        
        $returnData['type'] = '6';//已完成
        $returnData['url'] = '';
        $apiDepository = new Apidepository();
        $payAccount = new Payaccount();
        //判断用户是否存管开户
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        //判断是否完成设置密码
        $isPassword = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 2);
        //判断是否完成还款授权
        $isrepayAuth = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 4);
        //判断是否完成缴费授权
        $isfundAuth = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 5);
        //判断身份证号是否一致
        $isIdentify = $this->getIdentify($user_id);
        //判断是否完成四合一授权
        $isfourinoneAuth = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 6);
        //2 299存管流程
        $url = Yii::$app->request->hostInfo . '/borrow/custody/list?user_id=' . $userInfo->user_id;
        $isDep = true;
        if ($is_repay == 2) {
            $url = Yii::$app->request->hostInfo . '/new/depositorynew/choice?user_id=' . $userInfo->user_id;
            $loan_id = Yii::$app->request->post('loan_id');
            $bank_id = Yii::$app->request->post('bank_id');
            $money = Yii::$app->request->post('repay_amount');
            $coupon_id = Yii::$app->request->post('coupon_id');
            if (empty($loan_id) || empty($bank_id) || empty($money)) {
                exit($this->returnBack('99994'));
            }
            $loanInfo = User_loan::findOne($loan_id);
            $bankInfo = User_bank::findOne($bank_id);

            $o_coupon_list = (new Coupon_list())->getById($coupon_id);
            $coupon_val = 0;
            if (!empty($o_coupon_list)) {
                $coupon_val = $o_coupon_list->val;
            }
            $repay_amount_a = $this->getAccuracyAmount($money);
            $coupon_val_a = $this->getAccuracyAmount($coupon_val);
            $amount = bcadd($repay_amount_a, $coupon_val_a);
            $isDep = (new Loan_repay())->isDepositoryRepay($loanInfo, $userInfo, $bankInfo, $amount); //是否能体内还款
        }
        if ($isIdentify) {
            $returnData['type'] = '9';
            $returnData['desc'] = '很抱歉，由于您一亿元开户身份证与存管开户身份证不符，开户失败！暂不可发起借款！';
            $returnData['url'] = '';
            exit($this->returnBack('0000', $returnData));
        }
        if (empty($isAccount)) {
            $returnData['type'] = '4';
            $returnData['url'] = $url;
            exit($this->returnBack('0000', $returnData));
        }

        if ($isAccount && !empty($isAccount->card) && empty($isPassword)) {//已开户已绑卡未位置密码
            $returnData['type'] = '3';
            $returnData['desc'] = '本平台已接入银行存管体系，为保障您的资金安全，请马上设置交易密码';
            $returnData['url'] = Yii::$app->request->hostInfo . '/borrow/custody/list?user_id=' . $userInfo->user_id;
            exit($this->returnBack('0000', $returnData));
        }
        //设置密码
        if (empty($isPassword)) {
            $returnData['type'] = '3';
            $returnData['desc'] = '很抱歉，你未绑定存管银行卡无法发起借款，请立即绑定，绑卡前需先设置交易密码';
            $returnData['url'] = Yii::$app->request->hostInfo . '/borrow/custody/list?user_id=' . $userInfo->user_id;
            exit($this->returnBack('0000', $returnData));
        }
        //绑卡
        if ($isAccount && empty($isAccount->card)) {
            $returnData['url'] = Yii::$app->request->hostInfo . '/borrow/custody/list?user_id=' . $userInfo->user_id;
            //存管新绑卡方式，8页面跳转
            $returnData['type'] = '8';
            exit($this->returnBack('0000', $returnData));
        }

        //借款时授权
        $o_pay_account_extend = (new PayAccountExtend())->getByUserIdAndStep($userInfo->user_id, 6);
        if ($is_repay == 1) {
            if (empty($isfourinoneAuth) || !$o_pay_account_extend->getLegal(1)) {
                if ((empty($isfundAuth) || empty($isrepayAuth) || $isrepayAuth->isTimeOut() || $isfundAuth->isTimeOut())) {
                    $returnCustodyData['type'] = 10;
                    $returnCustodyData['desc'] = '由于您的操作授权已失效，请重新授权';
                    $returnCustodyData['url'] = Yii::$app->request->hostInfo . '/borrow/custody/list?user_id=' . $userInfo->user_id;
                    exit($this->returnBack('0000', $returnCustodyData));
                }
            }
        }
        //还款时授权
        //$isDep = true;//@todo
        if ($isDep && $is_repay == 2) {
            if (empty($isfourinoneAuth) || !$o_pay_account_extend->getLegal(2)) {
                if ((empty($isfundAuth) || empty($isrepayAuth) || $isrepayAuth->isTimeOut() || $isfundAuth->isTimeOut())) {
                    $array['redirect_url'] = Yii::$app->request->hostInfo . '/new/depositorynew/choice?user_id=' . $userInfo->user_id . '&from=app';
                    exit($this->returnBack('0000', $array));
                }
            }
        }
        exit($this->returnBack('0000', $returnData));
    }

    private function getIdentify($user_id) {
        $res = PayAccountError::find()->where(['user_id' => $user_id, 'type' => 1, 'res_code' => '1'])->one();
        $mark = false;
        if (!empty($res)) {
            $mark = true;
        }
        return $mark;
    }

    private function getAccuracyAmount($amount) {
        if (empty($amount) && $amount != 0) {
            return false;
        }
        $amount = floatval($amount);
        $amount = (int) round($amount * 100);
        return $amount;
    }

}
