<?php

namespace app\modules\api\controllers\controllers312;

use app\commonapi\Apidepository;
use app\modules\api\common\ApiController;
use app\commonapi\Common;
use Yii;

class NeedbindcardController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $array['need_bind']  = 2;//1:需要绑卡，2：不需要绑卡
        exit($this->returnBack('0000', $array));
    }
}
