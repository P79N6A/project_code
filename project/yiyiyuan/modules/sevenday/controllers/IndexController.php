<?php

namespace app\modules\sevenday\controllers;

use app\models\day\User_bank_guide;
use app\models\day\User_credit_guide;
use app\models\day\User_guide;
use app\models\day\User_loan_guide;
use Yii;

class IndexController extends SevendayController {

    public function behaviors() {
        parent::behaviors();
        return [];
    }

    /**
     * 获取额度页
     * @return string
     * @author 王新龙
     * @date 2018/8/2 20:22
     */
    public function actionIndex() {
        $user = $this->getUser();
        $ip = \app\commonapi\Common::get_client_ip();
        \app\commonapi\Logger::dayLog('sevenday/index', 'index', $ip,$user);
        if (!empty($user)) {
            return $this->redirect('/day/loan');
        }
        $this->getView()->title = '获取额度';
        return $this->render('index');
    }

}
