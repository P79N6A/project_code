<?php

namespace app\modules\borrow\controllers;

use Yii;

class WeixinnumController extends BorrowController {
    public function behaviors() {
        return [];
    }
    
    public function actionIndex(){
        $this->layout = 'weixinnum/index';
        $this->getView()->title = '玩转公众号';
        $user = $this->getUser();
        return $this->render('index',[
            'user_id' => $user->user_id
        ]);
    }
}

