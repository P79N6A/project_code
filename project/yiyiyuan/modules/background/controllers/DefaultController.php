<?php

namespace app\modules\background\controllers;

use Yii;
use app\commands\SubController;
use app\models\dev\Userwx;
use app\models\dev\Webunion_profit_detail;
use app\models\dev\Webunion_user_list;
use app\models\dev\User;
use app\models\dev\User_bank;
use app\models\dev\Account_settlement;
use app\models\dev\Webunion_feedback;
use app\commonapi\Common;
use app\commonapi\Http;

class DefaultController extends SubController {

    public $returnUrl            = '/background/default/index';
    public $layout               = 'index';
    public $enableCsrfValidation = false;

    private function getUser() {
        return Yii::$app->newDev->identity;
    }

    public function actionIndex() {
        $user    = $this->getUser();
        $open_id = $this->getVal('openid');
        $show    = 0;

        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $userwx = Userwx::find()->where(['openid' => $open_id])->one();

        $user_id = $user->user_id;
//更新一下用户表  加了2个字段 跳转登陆加一个url
        if ($user->is_webunion != 'yes') {
            $dat = date('Y-m-d H:i:s', time());
            $sql = "update " . User::tableName() . " set webunion_confirm_time='$dat' , is_webunion='yes'  where user_id= '$user_id'";
            Yii::$app->db->createCommand($sql)->execute();
        }
        $bobao  = Account_settlement::find()->where(['type' => 4, 'status' => 'SUCCESS'])->orderBy('create_time desc')->limit(20)->all();
        $shouyi = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andWhere(['in', 'status', [0, 4]])->andFilterWhere(['>=', 'create_time', date('Y-m-d')])->all();
        if (empty($shouyi) || !isset($shouyi)) {
            $shouyitotal = 0.00;
        } else {
            $shouyitotal = 0.00;
            foreach ($shouyi as $v) {
                $shouyitotal += $v['profit_amount'];
            }
        }
        $shouyitotal = number_format($shouyitotal, 2, ".", "");
        $time        = time();
        $shareUrl    = Yii::$app->request->hostInfo . "/background/default/spread1?from_code=" . $user->invite_code . "&u=" . $user_id . "&t=" . $time . "&s=" . md5($time . $user_id);
//        $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";

        $jsinfo                 = $this->getWxParam();
        $this->getView()->title = "赚钱妖怪";
        return $this->render('index', [
                    'userwx'      => $userwx,
                    'bobao'       => $bobao,
                    'shouyitotal' => $shouyitotal,
                    'user'        => $user,
                    'shareUrl'    => $shareUrl,
                    'jsinfo'      => $jsinfo,
                    'show'        => $show,
        ]);
    }

    //常见问题
    public function actionQuestion() {
        $user    = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $jsinfo                 = $this->getWxParam();
        $this->layout           = "index_n";
        $this->getView()->title = "常见问题";
        return $this->render('question', [
                    'jsinfo'    => $jsinfo,
                    'returnUrl' => $this->returnUrl
        ]);
    }

    //佣金介绍
    public function actionCommission() {
        $user    = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $jsinfo                 = $this->getWxParam();
        $this->layout           = "index_n";
        $this->getView()->title = "佣金介绍";
        return $this->render('commission', [
                    'jsinfo'    => $jsinfo,
                    'returnUrl' => $this->returnUrl
        ]);
    }

    //联系我们
    public function actionContact() {
        $user    = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $jsinfo                 = $this->getWxParam();
        $this->layout           = "index_n";
        $this->getView()->title = "联系我们";
        return $this->render('contact', [
                    'jsinfo'    => $jsinfo,
                    'returnUrl' => $this->returnUrl
        ]);
    }

    //意见反馈
    public function actionOpinion() {
        $user    = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $jsinfo                 = $this->getWxParam();
        $this->layout           = "index_n";
        $this->getView()->title = "意见反馈";
        return $this->render('opinion', [
                    'jsinfo'    => $jsinfo,
                    'returnUrl' => $this->returnUrl
        ]);
    }

    //异步将意见反馈插入数据库
    public function actionMethod() {
        $user    = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            $resultArr = array('ret' => '0', 'url' => '/new/loan');
            echo json_encode($resultArr);
            exit;
        }
        $user_id = !empty($user->user_id) ? $user->user_id : 0;

        $content                 = Yii::$app->request->post('content');
        $time                    = date('Y-m-d H:i:s');
        $webfb                   = new Webunion_feedback();
        $webfb->user_id          = $user_id;
        $webfb->content          = $content;
        $webfb->create_time      = $time;
        $webfb->last_modify_time = $time;
        if ($webfb->save()) {
            $resultArr = array('ret' => '1', 'url' => '/new/loan');
            echo json_encode($resultArr);
            exit;
        } else {
            $resultArr = array('ret' => '0', 'url' => '/new/loan');
            echo json_encode($resultArr);
            exit;
        }
    }

    //我要推广
    public function actionSpread() {
        $userinfo = $this->getUser();
        $open_id  = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        if (empty($userinfo) && !isset($userinfo)) {
            $invite_code = '';
        } else {
            $invite_code = $userinfo->invite_code;
        }
        $loanuserinfo           = Userwx::find()->where(['openid' => $open_id])->asarray()->one();
        $time                   = time();
        $Url                    = urlencode(Yii::$app->request->hostInfo . "/background/default/spread1?from_code=" . $userinfo['invite_code'] . "&u=" . $userinfo->user_id . "&t=" . $time . "&s=" . md5($time . $userinfo->user_id));
        $shareUrl               = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
        $this->getView()->title = "推广";
        $jsinfo                 = $this->getWxParam();
        $this->layout           = "index_n";
        return $this->render('spread', [
                    'invite_code'  => $invite_code,
                    'shareUrl'     => $shareUrl,
                    'loanuserinfo' => $loanuserinfo,
                    'jsinfo'       => $jsinfo,
                    'returnUrl'    => $this->returnUrl
        ]);
    }

    public function actionSpread1() {
//获取借款记录的ID
        $from_code = Yii::$app->request->get('from_code');
//获取时间
        $t         = intval($_GET['t']);
        $s         = $_GET['s'];
        $userinfo  = $this->getUser();
        $open_id   = $this->getVal('openid');
        if (empty($open_id) || empty($userinfo)) {
            $url = urlencode(Yii::$app->params['app_url'] . "/new/reg?from_code=" . $from_code . "&url=/background/webunion/index");
            $red = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . $url . '&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect';
            return $this->redirect($red);
        }
        if (empty($userinfo) && !isset($userinfo)) {
            $invite_code = '';
        } else {
            $invite_code = $userinfo->invite_code;
        }
        $loanuserinfo           = Userwx::find()->where(['openid' => $open_id])->asarray()->one();
        $time                   = time();
        $shareUrl               = Yii::$app->request->hostInfo . "/background/default/spread1?from_code=" . $userinfo['invite_code'] . "&u=" . $userinfo->user_id . "&t=" . $time . "&s=" . md5($time . $userinfo->user_id);
//        $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
        $this->getView()->title = "推广";
        $jsinfo                 = $this->getWxParam();
        $this->layout           = "index_n";
        return $this->render('spread1', [
                    'invite_code'  => $invite_code,
                    'shareUrl'     => $shareUrl,
                    'loanuserinfo' => $loanuserinfo,
                    'jsinfo'       => $jsinfo,
                    'returnUrl'    => $this->returnUrl
        ]);
    }

    //个人信息
    public function actionInformation() {
        $userinfo = $this->getUser();
        $open_id  = $this->getVal('openid');
        if (empty($open_id) || empty($userinfo)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $userwx = Userwx::find()->where(['openid' => $open_id])->one();
        if (empty($userwx) && !isset($userwx)) {
            $heads = '/images/bigFace.png';
        } else {
            $heads = $userwx->head;
        }
        $user_bank              = User_bank::find()->where(['user_id' => $userinfo->user_id])->all();
        $jsinfo                 = $this->getWxParam();
        $this->layout           = "index_n";
        $this->getView()->title = "个人信息";
        return $this->render('information', [
                    'userwx'    => $userwx,
                    'userinfo'  => $userinfo,
                    'user_bank' => $user_bank,
                    'jsinfo'    => $jsinfo,
                    'returnUrl' => $this->returnUrl
        ]);
    }

}
