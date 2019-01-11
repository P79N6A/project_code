<?php

namespace app\modules\api\controllers\controllers312;

use app\commonapi\ImageHandler;
use app\models\news\Banner;
use app\modules\api\common\ApiController;
use Yii;

class WhitelistController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');

        if (empty($version)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $array['whitelist'] = array('13439660605', '18500310315', '18500597522', '15910690412', '18610291548');

        $array = $this->returnBack('0000',$array);
        echo $array;
        exit;
    }
}
