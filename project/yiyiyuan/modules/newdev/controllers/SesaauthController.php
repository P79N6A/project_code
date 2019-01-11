<?php

namespace app\modules\newdev\controllers;

class SesaauthController extends NewdevController {

    public function behaviors() {
        return [];
    }

    public function actionIndex() {
        $this->getView()->title = "芝麻信用授权协议";
        echo "芝麻信用授权协议";die;
        $this->layout = 'agreement';

        return $this->render('index');
    }

}
