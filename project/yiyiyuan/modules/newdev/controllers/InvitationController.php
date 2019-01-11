<?php

namespace app\modules\newdev\controllers;

use app\models\news\User;
use app\models\news\User_wx;
use app\models\news\Common;
use Yii;

class InvitationController extends NewdevController {

    public $layout = 'inv';
    static $_appid = 'wx476bb3649401c450';
    static $_appSecret = 'a19d2451136f6084048385b93f0625f9';

    public function actionDistribute() {
        $user = $this->getUser();
        $userinfo = User::findOne($user->user_id);
        $order = $userinfo->getPerfectOrder($userinfo->user_id, 4, 11);
        $orderInfo = (new Common())->create3Des(json_encode($order, true));
        if ($order['nextPage']) {
            $nextPage = $order['nextPage'] . '?orderinfo=' . urlencode($orderInfo);
            return $this->redirect($nextPage);
        }
        $this->redirect('new/invitation');
    }

    public function actionIndex() {
        $user = $this->getUser();
        $userinfo = User::findOne($user->user_id);
        if (!$userinfo->openid) {
            $url = Yii::$app->request->hostInfo . '/new/reg';
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . $url . '&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect');
        }
        //认证数量
        $auth_count['count'] = $userinfo->getAuthusers()->count();
        //查询认证过我的用户
        $auth_list = $userinfo->getAuthusers()->all();
        $loanuserinfo = User_wx::find()->where(['openid' => $userinfo->openid])->asarray()->one();
        $Url = urlencode(Yii::$app->request->hostInfo . "/dev/invitation/cash?userid=" . $userinfo['user_id']);
        // $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
        //跳转至空白页面进行授权
        $shareUrl=Yii::$app->params['app_url']."/new/wxkong/index?u=".$Url;
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "邀请认证";
        return $this->render('index', [
                    'auth_count' => $auth_count,
                    'userinfo' => $userinfo,
                    'shareUrl' => $shareUrl,
                    'loanuserinfo' => $loanuserinfo,
                    'user_exist' => 'yes',
                    'auth_list' => $auth_list,
                    'jsinfo' => $jsinfo
        ]);
    }

}
