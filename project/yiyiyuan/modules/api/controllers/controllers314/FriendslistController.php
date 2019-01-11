<?php

namespace app\modules\api\controllers\controllers314;

use app\models\news\Friends;
use app\models\news\User;
use app\modules\api\common\ApiController;
use app\commonapi\Common;
use Yii;

class FriendslistController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $type = Yii::$app->request->post('type');
        $user_id = Yii::$app->request->post('user_id');

        if (empty($version) || empty($type) || empty($user_id)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        $user = User::findOne($user_id);
        if (empty($user)) {
            $array = $this->returnBack('10001');
            echo $array;
            exit;
        }
        $friendModel = new Friends();
        $friends = $friendModel->getFriends($user_id, $type);
        switch ($type) {
            case 1:
                $result = $this->one($friends);
                break;
            case 2:
                $result = $this->two($friends);
                break;
            default :
        }
        $array['list'] = $result;
        $array = $this->returnBack('0000', $array);
        echo $array;
        exit;
    }

    private function one($friends) {
        $result = array();
        if (!empty($friends)) {
            foreach ($friends as $key => $val) {
                $fuser = User::findOne($val->fuser_id);
                if (empty($fuser)) {
                    continue;
                }
                $result[$key]['user_id'] = $fuser->user_id;
                $result[$key]['name'] = $fuser->realname;
//                $result[$key]['school'] = !empty($fuser->school) ? $fuser->school : '';
                $result[$key]['company'] = !empty($fuser->company) ? $fuser->company : '';
                if (!empty($fuser->openid)) {
                    $wx = $fuser->userwx;
                    $result[$key]['headurl'] = !empty($wx) ? $wx->head : '';
                } else {
                    $result[$key]['headurl'] = '';
                }
            }
        }
        return $result;
    }

    private function two($friends) {
        $result = array();
        if (!empty($friends)) {
            $key = 0;
            foreach ($friends as $val) {
                $fuser = User::findOne($val->fuser_id);
                if (empty($fuser)) {
                    continue;
                }
                $result[$key]['user_id'] = $fuser->user_id;
                $result[$key]['company'] = (!empty($fuser->extend) && !empty($fuser->extend->company)) ? $fuser->extend->company : '';
                $result[$key]['name'] = $fuser->realname ? $fuser->realname : '';
                if (!empty($fuser->openid)) {
                    $wx = $fuser->userwx;
                    $result[$key]['headurl'] = !empty($wx) ? $wx->head : '';
                } else {
                    $result[$key]['headurl'] = '';
                }
                $key++;
            }
        }
        return $result;
    }
}
