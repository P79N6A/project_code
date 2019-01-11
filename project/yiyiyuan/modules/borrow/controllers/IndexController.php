<?php

namespace app\modules\borrow\controllers;

use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\ErrorCode;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Common as Common2;
use app\models\news\Coupon_list;
use app\models\news\Coupon_use;
use app\models\news\Insurance;
use app\models\news\Juxinli;
use app\models\news\No_repeat;
use app\models\news\Payaccount;
use app\models\news\Push_yxl;
use app\models\news\ScanTimes;
use app\models\news\TemQuota;
use app\models\news\User_password;
use app\commonapi\ImageHandler;
use app\models\news\Term;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_credit;
use app\models\news\User_label;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\User_rate;
use app\models\news\User_remit_list;
use app\models\news\White_list;
use app\models\service\GoodsService;
use app\models\service\UserloanService;
use Yii;
use yii\web\Response;

class IndexController extends BorrowController {

    public function behaviors() {
        return [];
    }

    /**
     * 首页 310,未登录的
     * @return type
     */
    public function actionIndex() {
        $this->getView()->title = "信用借款";
        $this->layout = 'loan';
        $user = $this->getUser();

        $utm_source = $this->get('utm_source', '');
        $utm_medium = $this->get('utm_medium', '');
        $utm_campaign = $this->get('utm_campaign', '');
        $utm_content = $this->get('utm_content', '');
        $utm_term = $this->get('utm_term', '');

        if (!empty($user)) {
            return $this->redirect('/borrow/loan?utm_source='.$utm_source.'&utm_medium='.$utm_medium.'&utm_campaign='.$utm_campaign.'&utm_content='.$utm_content.'&utm_term='.$utm_term);
        }
        return $this->render('index');
    }
}
