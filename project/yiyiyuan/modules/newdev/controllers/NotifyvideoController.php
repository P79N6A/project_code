<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Apihttp;
use app\commonapi\Crypt3Des;
use app\commonapi\ImageHandler;
use app\commonapi\Logger;
use app\models\news\Information_logs;
use app\models\news\User;
use app\models\news\User_password;
use app\models\news\Video_auth;
use Yii;
use yii\helpers\ArrayHelper;

class NotifyvideoController extends NewdevController {

    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    public function actionIndex(){
//        $data='{"res_data":"Ur2O4jl7lSlbfrXpxSVx1Dx7dmochLhyIvwRQNzF0SEuryWqUziAjW9BguzPPLuKIyIi0SlRAButc95605T0PIjTyRMX81K4CXTxUgJS+wPQH78DlOn2DuB6uU06+gd\/yYq8Ll3aiG1CNKlKAZw0BfypW2dSnt90\/I6uOXUN3NJ\/1SlwxfOmoIh\/ZEPZOQ9fo1C28898y0tH1rDwOrc\/NMqvv5hFKYfJ+Z66oTy7Cex5wQnlIXE56pZL+eY4HReDXNwwOjLkcXjXnD9Izm2Vg99cZEwfZ6nm","res_code":"0"}';
//        $data = json_decode($data,true);
        $isPost = Yii::$app->request->isPost;
        if ($isPost) {
            $data = $this->post();
            echo $this->postNotify($data);
        } else {
            $data = $this->get();
            $this->getNotify($data);
        }
    }

    public function getNotify($parr) {
        Logger::dayLog('notify/video', '视频同步结果----' . json_encode($parr));
        $videoModel = new Video_auth();
        $videoInfo  = $videoModel->getAuthByReqID($parr['requestid']);
        if (!$videoInfo || in_array($videoInfo->video_auth_status, [1, 2])) {
            exit;
        }
        if ($parr['code'] != 0) {
            $videoInfo->updateFail();
        } else {
            $videoInfo->updateMid();
        }
        $this->redirect('/borrow/userauth/videowaiting');
    }

    public function postNotify($parr) {
        $key        = '579BEFGINPQUVZehilprstxy';
        $res        = Crypt3Des::decrypt($parr['res_data'], $key);
        $re         = json_decode($res, true);
        Logger::dayLog('notify/video', '视频异步结果----' . json_encode($res));
        $infoModel = new Information_logs();
        $videoModel = new Video_auth();
        $videoInfo  = $videoModel->getAuthByReqID($re['yirequestid']);
        if (!$videoInfo || in_array($videoInfo->video_auth_status, [1, 2])) {
            exit;
        }
        $userInfo     = User::findOne($videoInfo->user_id);
        $passed = ArrayHelper::getValue($re, 'passed');
        if ($parr['res_code'] != 0 || !$passed) {
            $date = [
                'return_code'       => $parr['res_code'],
                'return_msg'        => !empty($re['message']) ? $re['message'] : '',
                'video_auth_status' => 2,
                'liveness_score'    => $re['liveness_score'],
            ];
            $infoModel->save_idenlogs($userInfo, 2, $re, 1, 4);//纪录认证次数
            $res  = $videoInfo->updateAuth($date);
            if (!$res) {
                exit;
            }
        } else {
            $date = [
                'liveness_score'    => $re['liveness_score'],
                'image_url'         => $re['base64_image'],
                'video_auth_status' => 1,
            ];
            $res  = $videoInfo->updateAuth($date);
            if (!$res) {
                exit;
            }
            $password     = User_password::find()->where(['user_id' => $videoInfo->user_id])->one();
            $userInfo->update_user(array('pic_identity' => $re['base64_image'], 'pic_up_time' => date('Y-m-d H:i:s')));
            $password->update_password(array('pic_url' => $re['base64_image'],));
            $iden_url     = (new ImageHandler())->img_domain_url.$password->iden_url;
            $identity_url = ImageHandler::getUrl($re['base64_image']);
            //调用人脸识别接口
            $postData     = array(
                'identity'     => $userInfo->identity,
                'pic_identity' => $iden_url,
                'identity_url' => $identity_url
            );
            $openApi      = new Apihttp;
            $result       = $openApi->faceValid($postData);
            if ($result['res_code'] != '0000') {
                $infoModel->save_idenlogs($userInfo, 2, $re, 1, 4);//纪录认证次数
                exit;
            }
            $password->update_password(array('score' => $result['res_msg']['score']));
            Logger::dayLog('notify/face', '调用人脸识别接口----' . json_encode($res));
            if ($result['res_msg']['score'] >= 60 && $userInfo->status != 5) {
                $infoModel->save_idenlogs($userInfo, 2, $re, 1, 0);
                //改为认证成功
                $videoInfo->updateSuccess();
                $result = $userInfo->updateUserStatus(3);
                if (!$result) {
                    exit;
                }
            }else{
                $infoModel->save_idenlogs($userInfo, 2, $re, 1, 4);
            }
        }
        return 'SUCCESS';
    }

}
