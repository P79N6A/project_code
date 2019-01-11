<?php

namespace app\modules\borrow\controllers;

use app\models\news\User;
use yii;
use app\commonapi\ImageHandler;
use app\models\news\Propose;

class ProposeController extends BorrowController {
    public $layout = false;

    public function behaviors() {
        return [];
    }

    public function actionIndex() {
        if ($this->isPost()) {
            $user_id = $this->post('user_id', '');
            $content = $this->post('content', '');
            $picture = $this->post('picture', '');
            $userObj = (new User())->getById($user_id);
            if (empty($userObj)) {
                return $this->jsonOut(1001, '非法访问&缺失参数');
            }

            if (!$content) {
                return $this->jsonOut(1001, '内容不能为空');
            }
            $data = [
                'content' => $content,
                'picture' => $picture,
                'user_id' => $userObj->user_id
            ];

            $proposeObj = Propose::find()->where($data)->one();
            if ($proposeObj) {
                return $this->jsonOut(1002, '两次提交内容相同');
            }

            $res = (new Propose())->addPropose($data);
            if (!$res) {
                return $this->jsonOut(1003, '提交失败');
            }
            return $this->jsonOut(200, '提交成功');
        } else {
            $user_id = $this->get('user_id', '');
            if (empty($user_id)) {
                exit('非法访问&缺失参数');
            }
            $userObj = (new User())->getById($user_id);
            if (empty($userObj)) {
                exit('无该用户');
            }
            //提交成功后回调地址
            $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/borrow/propose';
            $imageHandlerModel = new ImageHandler();
            return $this->render('index', [
                'img_domain_url' => $imageHandlerModel->img_domain_url,
                'img_upload_url' => $imageHandlerModel->img_upload_url,
                'redirect_url' => $redirect_url,
                'jsinfo' => $this->getWxParam(),
                'csrf' => $this->getCsrf(),
                'user_id' => $user_id,
                'encrypt' => ImageHandler::encryptKey($userObj->user_id, 'h5'),
            ]);
        }
    }
}
