<?php
namespace app\modules\api\controllers\controllers314;

use Yii;
use app\models\news\User;
use app\modules\api\common\ApiController;
use app\commonapi\Http;

class CheckidentityController extends ApiController {
    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $mobile = Yii::$app->request->post('mobile');
        $identity = Yii::$app->request->post('identity');
        $type = Yii::$app->request->post('type', 1);//1：完全验证    2：后6位验证

        if (empty($version) || empty($mobile) || empty($identity) || empty($type)) {
            exit($this->returnBack('99994'));
        }

        switch ($type) {
            case 1:
                $this->complete($mobile, $identity);
                break;
            case 2:
                $this->portion($mobile, $identity);
                break;
            default:
                $this->complete($mobile, $identity);
                break;
        }
    }

    /**
     * 身份证后6位验证
     * @param $mobile
     * @param $identity
     * @author 王新龙
     * @date 2018/8/21 16:20
     */
    private function portion($mobile, $identity) {
        $o_user = (new User())->getUserinfoByMobile($mobile);
        if (empty($o_user)) {
            exit($this->returnBack('10001'));
        }
        if (empty($o_user->identity)) {
            exit($this->returnBack('10006'));
        }
        if (strtolower(substr($o_user->identity, -6)) == strtolower($identity)) {
            exit($this->returnBack('0000'));
        } else {
            exit($this->returnBack('10006'));
        }
    }

    /**
     * 身份证完全验证
     * @param $mobile
     * @param $identity
     * @author 王新龙
     * @date 2018/8/21 16:19
     */
    private function complete($mobile, $identity) {
        if (!Http::checkIdenCard($identity)) {
            exit($this->returnBack('10009'));
        }

        $o_user = (new User())->getUserinfoByMobile($mobile);
        if (empty($o_user)) {
            exit($this->returnBack('10001'));
        }
        if (strtolower($o_user->identity) == strtolower($identity)) {
            exit($this->returnBack('0000'));
        } else {
            exit($this->returnBack('10006'));
        }
    }
}