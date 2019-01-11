<?php

namespace app\modules\api\controllers\controllers312;

use app\models\news\Areas;
use app\modules\api\common\ApiController;
use Yii;

class GetarealistController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');

        if (empty($version)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $area_list = Areas::getAllAreas();
        $list = json_decode($area_list);
        if (!empty($list)) {
            $array['list'] = $list;
            $array = $this->returnBack('0000', $array);
            echo $array;
            exit;
        } else {
            $array = $this->returnBack('99999');
            echo $array;
            exit;
        }
    }
}
