<?php
namespace app\modules\api\controllers\controllers311;

use app\commonapi\Logger;
use app\models\news\Address;
use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class UploadaddressController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $latitude = Yii::$app->request->post('latitude');
        $longitude = Yii::$app->request->post('longitude');
        $address = Yii::$app->request->post('address');
        $come_from = Yii::$app->request->post('come_from');

        if (empty($version) || empty($user_id) || empty($latitude) || empty($longitude) || empty($address) || empty($come_from)) {
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
        $addressModel = new Address();
        $result = $addressModel->addAddress($user_id, $latitude, $longitude, $address, $come_from);
        if ($result) {
            $array = $this->returnBack('0000');
            echo $array;
            exit;
        } else {
            $array = $this->returnBack('99999');
            echo $array;
            exit;
        }
    }

}
