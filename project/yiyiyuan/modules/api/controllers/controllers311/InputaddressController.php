<?php
namespace app\modules\api\controllers\controllers311;

use app\models\own\Address_list;
use app\modules\api\common\ApiController;
use Yii;

class InputaddressController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $list = Yii::$app->request->post('list');
        $user_id = Yii::$app->request->post('user_id');

        if (empty($version) || empty($user_id) || empty($list)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $mobiles = json_decode($list);
        $adressModel = new Address_list;
        $total = $adressModel -> saveMobiles($user_id, $mobiles);
        $array = $this->returnBack('0000');
        echo $array;
        exit;
    }

}
