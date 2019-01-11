<?php

namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\common\Logger;
use app\commonapi\Apihttp;
use app\commonapi\Crypt3Des;
use app\models\news\Loan_repay;
use app\models\news\User;
use app\models\news\User_loan;
use Yii;

class RepaylistController extends NewdevController {

    public function behaviors() {
        return [];
    }

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $encodeUserId = $this->get('user_id_store');
        $from = $this->get('from');
//        $urlDecodeUserId = urldecode($encodeUserId);
//        $api = new ApiClientCrypt();
        $user_id = $encodeUserId;
        $userInfo = User::findIdentity($user_id);
        if(!$user_id || !$userInfo){
            exit('非法请求!');
        }
        $userLoanInfo = User_loan::find()->where(['user_id'=>$user_id])->orderBy('create_time desc')->one();
        $hasRepayingLoan = null;
        $syLoanTime = 0;
        $hasRepayIng = 0;//还款确认中
        if($userLoanInfo && in_array($userLoanInfo->status,[9,11,12,13]) && $userLoanInfo->loanextend && $userLoanInfo->loanextend->status == 'SUCCESS'){
            $hasRepayingLoan = $userLoanInfo;
            $syLoanTime = strtotime($userLoanInfo->end_date) - time();
            if($userLoanInfo->status == 11){
                $hasRepayIng = 1;
            }
            $repayIngInfo = Loan_repay::find()->where(['loan_id'=>$userLoanInfo->loan_id,'status'=>-1])->one();
            if($repayIngInfo){
                $hasRepayIng = 1;
            }
        }
        $hasIousing = null;
        $syIousTime = 0;
        $iousResult = (new Apihttp())->getUseriousinfo(['mobile' => $userInfo->mobile]);
        if (empty($iousResult)) {
            Logger::dayLog('app/getUseriousinfo', '获取用户白条信息失败', $userInfo->user_id, $iousResult);
        }elseif (!empty($iousResult)){
            $syIousTime = strtotime($iousResult['end_time']) - time();
        }
        $this->getView()->title = "账单列表";
        return $this->render('index', [
            'hasRepayingLoan' => $hasRepayingLoan,
            'hasIousing' => $iousResult,
            'syLoanTime' => $syLoanTime,
            'syIousTime' => $syIousTime,
            'hasRepayIng' => $hasRepayIng,
            'from' => $from,
            'user_id' => $user_id,
            'mobile' => $userInfo->mobile,
            'userLoanInfo' => $userLoanInfo,
        ]);
    }

}
