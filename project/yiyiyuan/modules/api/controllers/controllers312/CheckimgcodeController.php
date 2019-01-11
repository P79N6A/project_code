<?php
namespace app\modules\api\controllers\controllers312;

use app\commonapi\Logger;
use Yii;
use app\modules\api\common\ApiController;

class CheckimgcodeController extends ApiController {
    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $mobile = Yii::$app->request->post('mobile');
        $code = Yii::$app->request->post('code');
        if (empty($version) || empty($mobile) || empty($code)) {
            exit($this->returnBack('99994'));
        }
        if (!preg_match("/^(1(([35678][0-9])|(47)))\d{8}$/", $mobile)) {
            exit($this->returnBack('10008'));
        }
        $code_char = Yii::$app->redis->get('code_char_'.$mobile);
        if (strtolower($code) != strtolower($code_char)) {
            exit($this->returnBack('10235'));
        }
        exit($this->returnBack('0000'));
    }
}
