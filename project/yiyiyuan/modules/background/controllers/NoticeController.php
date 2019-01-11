<?php

namespace app\modules\background\controllers;

use Yii;
use app\commands\SubController;
use app\models\dev\User;
use app\models\dev\Webunion_notice;

class NoticeController extends SubController {

    public $layout = "index_n";

    private function getUser() {
        return Yii::$app->newDev->identity;
    }

    public function actionIndex() {

        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $notice = Webunion_notice::find()->orderBy('create_time desc')->all();
        $jsinfo = $this->getWxParam();
        $returnUrl = '/background/default/index';
        $this->getView()->title = '消息公告';
        return $this->render('index', [
                    'notice' => $notice,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

}
