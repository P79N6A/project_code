<?php

namespace app\modules\renew\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Apidepository;
use app\commonapi\Bank;
use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\Crypt3Des;
use app\commonapi\ErrorCode;
use app\commonapi\Keywords;
use app\models\news\Cg_remit;
use app\models\news\Common as Common2;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\service\UserloanService;
use Yii;

class LoanController extends RenewbaseController {

    public function actionIndex() {
        $this->layout = "showloan";
        $this->getView()->title = "借款";
        $userInfo = $this->getUser();
        $loan_id = (new User_loan())->getHaveinLoan($userInfo->user_id);
        if (!$loan_id) {
            exit('借款不存在');
        }
        //Yii::$app->redis->del($loan_id);
        $user = $this->getUser();
        $loanInfo = User_loan::find()->where(['loan_id' => $loan_id, 'user_id' => $user->user_id])->one();
        if (!$loanInfo) {
            exit('借款不存在');
        }
        if ($loanInfo->status == 8) {
            return $this->redirect('/new/loanrecord/creditdetails?loan_id=' . $loanInfo->loan_id);
        }
        $jsinfo = $this->getWxParam();
        $userLoanService = new UserloanService();
        $info = $userLoanService->getLoanDetaile($loan_id);
        if ($info['rsp_code'] != '0000') {
            exit($this->getErrorMsg($info['rsp_code']));
        }
        $info['user_info'] = $user;
        $info['jsinfo'] = $jsinfo;
        $info['loan_coupon'] = $loanInfo->couponUse;
        $info['loan_id'] = $loanInfo->loan_id;
        $info['shareUrl'] = Yii::$app->request->hostInfo . "/dev/share/likestat?t=" . time() . "&d=" . $loan_id . "&s=" . md5(time() . $loan_id);
        $info['csrf'] = $this->getCsrf();
        $info['encodeUserId'] = $user->user_id;

        return $this->render('showloan', $info);
    }

}
