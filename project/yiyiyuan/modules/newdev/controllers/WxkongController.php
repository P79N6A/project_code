<?php

namespace app\modules\newdev\controllers;

use app\commands\SubController;
use Yii;

class WxkongController extends NewdevController {

    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }
    //用户授权
    public function actionIndex(){
        $u=Yii::$app->request->get('u');
        $appid=Yii::$app->params['AppID'];
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $u . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
        return $this->redirect($url);
    }

}
