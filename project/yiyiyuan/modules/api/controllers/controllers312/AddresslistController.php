<?php
namespace app\modules\api\controllers\controllers312;

use app\models\own\Address_list;
use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class AddresslistController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');

        if (empty($version) || empty($user_id)) {
            exit($this->returnBack('99994'));
        }

        $fuser = User::findOne($user_id);
        if (empty($fuser)) {
            exit($this->returnBack('10001'));
        }
        $addressModel = new Address_list();
        $addresslist = $addressModel->findAllMobile($user_id);
        $array = $this->reback($addresslist);
        exit($this->returnBack('0000', $array));
    }

    private function reback($addresslist)
    {
        $array = [];
        if (empty($addresslist)) {
            $array['list'] = array();
        } else {
            foreach ($addresslist as $key => $val) {
                $array['list'][$key]['name'] = $val->name;
                $array['list'][$key]['mobile'] = $val->phone;
                $user = (new User())->getUserinfoByMobile($val->phone);
                $array['list'][$key]['user_id'] = !empty($user) ? $user->user_id : 0;
                $array['list'][$key]['head'] = !empty($user) && !empty($user->openid) ? (!empty($user->userwx) ? $user->userwx->head : '') : '';
            }
        }
        return $array;
    }
}
