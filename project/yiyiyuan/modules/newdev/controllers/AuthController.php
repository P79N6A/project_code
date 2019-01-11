<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\news\Statistics;

use Yii;

class AuthController extends NewdevController {

    public function behaviors() {
        return [];
    }

    public function init() {
        $this->layout = "mall";
    }

    public function actionJump() {
        $userToken = $this->get('userToken');
        $url = Yii::$app->params['youxin_url'] . 'dev/index?userToken=' . $userToken;
        return $this->redirect($url);
    }

    public function actionHowtoauth() {
        $this->getView()->title = "如何认证";
        $type = $this->get('type');
        return $this->render('howtoauth', ['type' => $type]);
    }

    public function actionToauthone() {
        $this->getView()->title = "如何认证";
        return $this->render('toauthone');
    }

    public function actionToauthtwo() {
        $this->getView()->title = "如何认证";
        return $this->render('toauthtwo');
    }

    public function actionHowtoauthwx() {
        $this->getView()->title = "如何认证";
        return $this->render('toauththree');
    }

    public function actionToauththree() {
        $this->getView()->title = "如何认证";
        return $this->render('toauththree');
    }

    /**
     * @return \yii\web\Response
     * 导流跳转url
     */
    public function actionGuide() {
        $user_id = $this->get('user_id');
        if (!is_numeric($user_id)) {
            return false;
        }
        $userInfo = \app\models\news\User::findOne($user_id);
        if (empty($userInfo)) {
            return false;
        }

        //统计
        $info = $_SERVER;
        $ip = Common::get_client_ip();
        $ip_explode = explode(',',$ip);
        $ip = $ip_explode[0];
        $model = new Statistics();
        $model->user_id = $user_id;
        $model->loan_id = 0;
        $model->from = 'sms_api';
        $model->remoteip = isset($ip) ? $ip : 0;
        $agent = isset($info['HTTP_USER_AGENT'])?mb_substr($info['HTTP_USER_AGENT'], 0, 256, 'utf-8'):'';
        $model->user_agent = $agent;
        $model->create_time = date('Y-m-d H:i:s');
        $model->type = 1409;
        Logger::dayLog('statistics',$model);
        $result = $model->save();
        Logger::dayLog('statistics',$result);

        $url = 'http://dc.zhirongyaoshi.com?channel=banner';
        $jumpUrl = $url . '&phone=' . $userInfo->mobile;
        \app\commonapi\Logger::dayLog('guide', $jumpUrl);
//        sleep(1);
        return $this->redirect($jumpUrl);
    }

}
