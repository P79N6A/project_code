<?php
namespace app\modules\sevenday\controllers;

use Yii;

class AgreeloanController extends SevendayController {

    public $layout = false;

    public function behaviors() {
        return [];
    }

    /**
     * 借款协议
     * @return string
     * @author 王新龙
     * @date 2018/8/3 9:56
     */
    public function actionLoan() {
        $this->getView()->title = '借款协议';
        return $this->render('loan', [
            'csrf' => $this->getCsrf()
        ]);
    }

    /**
     * 注册协议
     * @return string
     * @author 王新龙
     * @date 2018/8/3 20:12
     */
    public function actionRegister() {
        $this->getView()->title = '注册协议';
        return $this->render('register', [
            'csrf' => $this->getCsrf()
        ]);
    }
}
