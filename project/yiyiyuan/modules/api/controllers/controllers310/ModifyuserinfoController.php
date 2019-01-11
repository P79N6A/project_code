<?php

namespace app\modules\api\controllers\controllers310;

use app\commonapi\ImageHandler;
use app\commonapi\Keywords;
use app\models\news\Favorite_contacts;
use app\models\news\User;
use app\models\news\User_extend;
use app\models\news\User_password;
use app\modules\api\common\ApiController;
use Yii;

class ModifyuserinfoController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $type = Yii::$app->request->post('type');

        if (empty($version) || empty($user_id) || empty($type)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        if ($type == 1) {
            $favorite_contacts = Favorite_contacts::find()->where(['user_id' => $user_id])->one();
            $array['contacts_name'] = !empty($favorite_contacts->contacts_name) ? $favorite_contacts->contacts_name : '';
            $array['relation_common'] = !empty($favorite_contacts->relation_common) ? (string) $favorite_contacts->relation_common : '';
            $array['mobile'] = !empty($favorite_contacts->mobile) ? $favorite_contacts->mobile : '';
            $array['relatives_name'] = !empty($favorite_contacts->relatives_name) ? $favorite_contacts->relatives_name : '';
            $array['relation_family'] = !empty($favorite_contacts->relation_family) ? (string) $favorite_contacts->relation_family : '';
            $array['phone'] = !empty($favorite_contacts->phone) ? $favorite_contacts->phone : '';
            $array = $this->returnBack('0000', $array);
            echo $array;
            exit;
        } else if ($type == 2) {
            $user_info = User::find()->where(['user_id' => $user_id])->one();
            $array_income = array(
                '1' => '2000以下',
                '2' => '2000-2999',
                '3' => '3000-3999',
                '4' => '4000-4999',
                '5' => '5000以上',
            );
            if (!empty($user_info)) {
                $user_extend = User_extend::getUserExtend($user_id);
                $array['company'] = !empty($user_extend) ? (string) $user_extend->company : '';
                $array['position_id'] = (!empty($user_extend) && $user_extend->is_new == 1) ? (string) $user_extend->position : '';
                $array['profession'] = (!empty($user_extend) && $user_extend->is_new == 1) ? (string) $user_extend->profession : '';
                $array['income'] = !empty($user_extend) ? (string) array_search($user_extend->income, $array_income) : '';
                $array = $this->returnBack('0000', $array);
                echo $array;
                exit;
            } else {
                $array = $this->returnBack('10001');
                echo $array;
                exit;
            }
        } else {//个人资料信息
            $user_info = User::find()->where(['user_id' => $user_id])->one();
            if (!empty($user_info)) {
                $passModel = new User_password();
                $pass = $passModel->getUserPassword($user_id);
                $path = !empty($pass) ? ImageHandler::getUrl($pass->iden_url) : '';
                if (!empty($pass) && !empty($pass->iden_url) && @fopen($path, 'r')) {
                    $array['ocr'] = '1';
                } else {
                    if ($user_info->status == 3 || $user_info->status == 2) {
                        $array['ocr'] = '1';
                    } else {
                        $array['ocr'] = '0';
                    }
                }
                $array_income = array(
                    '1' => '2000以下',
                    '2' => '2000-2999',
                    '3' => '3000-3999',
                    '4' => '4000-4999',
                    '5' => '5000以上',
                );
                $user_extend = User_extend::getUserExtend($user_id);
                $array['realname'] = !empty($user_info->realname) ? $user_info->realname : '';
                $array['identity'] = !empty($user_info->identity) ? $user_info->identity : '';
                $array['email'] = !empty($user_extend->email) ? $user_extend->email : '';
                $array['company'] = !empty($user_extend) ? (string) $user_extend->company : '';
                $array['profession'] = (!empty($user_extend) && $user_extend->is_new == 1) ? $user_extend->profession : '';
                $array['income'] = !empty($user_extend) ? (string) array_search($user_extend->income, $array_income) : '';
                $array = $this->returnBack('0000', $array);
                echo $array;
                exit;
            } else {
                $array = $this->returnBack('10001');
                echo $array;
                exit;
            }
        }
    }
}
