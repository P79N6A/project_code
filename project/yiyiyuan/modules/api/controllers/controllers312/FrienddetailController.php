<?php
namespace app\modules\api\controllers\controllers312;

use app\models\news\Friends;
use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class FrienddetailController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $fuser_id = Yii::$app->request->post('fuser_id');
        $user_id = Yii::$app->request->post('user_id');

        if (empty($version) || empty($fuser_id) || empty($user_id)) {
            exit($this->returnBack('99994'));
        }
        $fuser = User::findOne($fuser_id);
        if (empty($fuser)) {
            exit($this->returnBack('10001'));
        }
        $friendModel = new Friends();
        if (isset($fuser_id)) {
            $friends = $friendModel->getFriendsRelation($user_id, $fuser_id);
        }
        $wx = $fuser->userwx;
        $array = $this->reback($fuser, $friends, $wx);
        exit($this->returnBack('0000', $array));
    }

    private function reback($fuser, $friends, $wx)
    {
        $array['name'] = $fuser->realname;
        $array['level'] = !empty($friends->type) ? $friends->type : '';
        $array['amount'] = 0;
        $array['headurl'] = !empty($wx) ? $wx->head : '';
        $array['company'] = !empty($fuser->company) ? $fuser->company : '';
        $array['mobile'] = $fuser->mobile;
        return $array;

    }
}
