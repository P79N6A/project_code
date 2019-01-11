<?php

namespace app\modules\api\controllers\controllers310;

use app\modules\api\common\ApiController;
use app\commonapi\Common;
use Yii;

class IsshowController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $array['is_show']  = '0';  // 0 不显示 1  显示
        $array = $this->returnBack('0000', $array);
        echo $array;
        exit;
    }

}
