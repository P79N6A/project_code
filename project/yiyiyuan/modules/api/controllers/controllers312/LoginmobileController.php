<?php
namespace app\modules\api\controllers\controllers312;

use app\models\news\User_password;
use Yii;
use app\models\news\User;
use app\modules\api\common\ApiController;
use app\commonapi\Http;

class LoginmobileController extends ApiController {
    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $mobile = Yii::$app->request->post('mobile');

        if (empty($version) || empty($mobile)) {
            exit($this->returnBack('99994'));
        }
        if (!preg_match("/^(1(([35678][0-9])|(47)))\d{8}$/", $mobile)) {
            exit($this->returnBack('10008'));
        }
        //type 1：输入密码   2：输入验证码
        $array = ['type' => 2];
        $o_user = (new User())->getUserinfoByMobile($mobile);
        if (empty($o_user)) {
            exit($this->returnBack('0000', $array));
        }
        $o_user_password = (new User_password())->getUserPassword($o_user->user_id);
        if (empty($o_user_password) || empty($o_user_password->login_password)) {
            exit($this->returnBack('0000', $array));
        }
        $array = ['type' => 1];
        exit($this->returnBack('0000', $array));
    }
}