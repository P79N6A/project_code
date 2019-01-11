<?php
namespace app\modules\api\controllers\controllers314;

use app\models\news\Contacts_flows;
use app\models\news\Favorite_contacts;
use app\modules\api\common\ApiController;
use Yii;

class FavoritecontactsController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $contacts_name = Yii::$app->request->post('contacts_name');
        $relation_common = Yii::$app->request->post('relation_common');
        $mobile = Yii::$app->request->post('mobile');
        $relatives_name = Yii::$app->request->post('relatives_name');
        $relation_family = Yii::$app->request->post('relation_family');
        $phone = Yii::$app->request->post('phone');

        if (empty($version) || empty($user_id) || empty($contacts_name) || empty($mobile) || empty($relatives_name) || empty($phone) || empty($relation_common) || empty($relation_family)) {
            exit($this->returnBack('99994'));
        }
        if (!preg_match("/^((1(([35678][0-9])|(47)))\d{8})|((0\d{2,3})\-?\d{7,8}(\-?\d{4})?)$/", $mobile)) {
            exit($this->returnBack('10008'));
        }
        if (!preg_match("/^((1(([35678][0-9])|(47)))\d{8})|((0\d{2,3})\-?\d{7,8}(\-?\d{4})?)$/", $phone)) {
            exit($this->returnBack('10008'));
        }

        if($mobile==$phone){
            exit($this->returnBack('10232'));
        }

        $favorite_contacts = new Favorite_contacts();
        $contacts_flows = new Contacts_flows();
        //新加联系人记录
        $condition = array(
            'user_id' => $user_id,
            'contacts_name' => $contacts_name,
            'relation_common' => $relation_common,
            'mobile' => $mobile,
            'relatives_name' => $relatives_name,
            'relation_family' => $relation_family,
            'phone' => $phone
        );
        $user_favorite_contacts = Favorite_contacts::find()->where(['user_id' => $user_id])->one();
        if (!empty($user_favorite_contacts)) {
            $result = $user_favorite_contacts->update_favoriteContacts($condition);
            if ($result) {
                //新加一条联系人记录
                $ret = $contacts_flows->save_contactsFlows($condition);
                if ($ret) {
                    exit($this->returnBack('0000'));
                } else {
                    exit($this->returnBack('10072'));
                }
            } else {
                exit($this->returnBack('10072'));
            }
        } else {
            //添加联系人记录
            $result = $favorite_contacts->save_favoriteContacts($condition);
            if ($result) {
                $ret = $contacts_flows->save_contactsFlows($condition);
                if ($ret) {
                    exit($this->returnBack('0000'));
                } else {
                    exit($this->returnBack('10072'));
                }
            } else {
                exit($this->returnBack('10072'));
            }
        }
    }
}
