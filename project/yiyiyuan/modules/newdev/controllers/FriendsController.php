<?php

/**
 * Created by PhpStorm.
 * User: wangyongqiang
 * Date: 2017/4/26
 * Time: 15:56
 */

namespace app\modules\newdev\controllers;

use app\commonapi\Common;
use app\models\news\Friends;
use app\models\news\User;
use app\models\own\Address_list;

class FriendsController extends NewdevController {

    public $layout = '_data';
    public $enableCsrfValidation = false;

    public function actionFirst() {
        $this->getView()->title = "朋友圈";
        $openid = $this->getVal('openid');
        $user_id = User::find()->select('user_id')->where(['openid' => $openid])->one();
        /*         * *************记录访问日志beigin******************* */
        $ip = Common::get_client_ip();
        $result_log = Common::saveLog('circle', 'first_friend', $ip, 'weixin', $user_id['user_id']);
        /*         * *************记录访问日志end******************* */
        $friend = Friends::find()->select('fuser_id')->where(['user_id' => $user_id['user_id'], 'type' => 1])->all();
        $fuser_id = Common::ArrayToString($friend, 'fuser_id');
        $fuser_id = explode(',', $fuser_id);
        $fuser = User::find()->select(['openid', 'user_id', 'realname', 'mobile', 'school', 'company'])->where(['IN', 'user_id', $fuser_id])->all();
        $jsinfo = $this->getWxParam();
        return $this->render('first', [
                    'fuser' => $fuser,
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionSecond() {
        $this->getView()->title = "朋友圈";
        $openid = $this->getVal('openid');
        $user_id = User::find()->select('user_id')->where(['openid' => $openid])->one();
        /*         * *************记录访问日志beigin******************* */
        $ip = Common::get_client_ip();
        $result_log = Common::saveLog('circle', 'second_friend', $ip, 'weixin', $user_id['user_id']);
        /*         * *************记录访问日志end******************* */
        $friend = Friends::find()->select('fuser_id')->where(['user_id' => $user_id['user_id'], 'type' => 2])->all();
        $fuser_id = Common::ArrayToString($friend, 'fuser_id');
        $fuser_id = explode(',', $fuser_id);
        $fuser = User::find()->select(['openid', 'user_id', 'realname', 'mobile', 'school', 'company'])->where(['IN', 'user_id', $fuser_id])->all();
//        print_r($fuser);exit;
        $jsinfo = $this->getWxParam();
        return $this->render('second', [
                    'fuser' => $fuser,
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionPhonecontact() {
        $this->getView()->title = "朋友圈";
        $openid = $this->getVal('openid');
        $user = User::find()->select('user_id')->where(['openid' => $openid])->one();
        /*         * *************记录访问日志beigin******************* */
        $ip = Common::get_client_ip();
        $result_log = Common::saveLog('circle', 'phone_contact', $ip, 'weixin', $user['user_id']);
        /*         * *************记录访问日志end******************* */
        $oAddressModel = new Address_list();
        $friend = $oAddressModel->getAddressList($user['user_id']);
        foreach ($friend as $key => $val) {
            $users = (new User())->getUserinfoByMobile($val->phone);
            if (!empty($users) && !empty($users->openid)) {
                $friend[$key]->head = !empty($users->userwx) ? $users->userwx->head : '';
            } else {
                $friend[$key]->head = '';
            }
        }
        $jsinfo = $this->getWxParam();
        return $this->render('phonecontact', [
                    'friend' => $friend,
                    'jsinfo' => $jsinfo,
        ]);
    }

}
