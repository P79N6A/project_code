<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\news\User_loan;

class InpayController extends NewdevController
{

    public function behaviors()
    {
        return [];
    }

    public function actionIndex()
    {
        $user_id = $this->get('user_id');
        $urlkey = $user_id.'urlkey';
        if(!$user_id || !$urlkey){
            exit('网络错误');
        }
        $url = $this->getRedis($urlkey);
        return $this->render('index', [
            'url' => $url,
        ]);
    }


}
