<?php
namespace app\modules\api\controllers\controllers314;

use app\models\news\Payaccount;
use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class DepositorynewopenController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');

        if (empty($version) || empty($user_id)) {
            exit($this->returnBack('99994'));
        }
        $userInfo = User::findOne($user_id);
        if (empty($userInfo)) {
            $array['status'] = '2';
            exit($this->returnBack('10001', $array));
        }

        //判断用户是否存管开户
        $payAccount = new Payaccount();
        $isAccount = $payAccount->getPaysuccessByUserId($userInfo->user_id, 2, 1);
        if ($isAccount) {
            $array['url'] = '';
            exit($this->returnBack('0000', $array));
        }
//        $url =
//        $array['url'] = Yii::$app->request->hostInfo . '/new/depositorynew/newopen?user_id=' . $user_id;
        $array['url'] = Yii::$app->request->hostInfo . '/borrow/custody/list?user_id=' . $user_id;
        exit($this->returnBack('0000', $array));
    }
}
