<?php

namespace app\modules\api\controllers\controllers314;

use app\commonapi\Keywords;
use app\modules\api\common\ApiController;
use Yii;

class WhitelistController extends ApiController
{
    public $enableCsrfValidation = FALSE;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        if (empty($version)) {
            exit($this->returnBack('99994'));
        }
        $array['whitelist'] = Keywords::listWhiteList();
        exit($this->returnBack('0000', $array));
    }
}
