<?php
namespace app\modules\api\controllers\controllers312;

use app\commonapi\Logger;
use app\modules\api\common\ApiController;
use Yii;

class CrashController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $postData = Yii::$app->request->post();
        $user_id = Yii::$app->request->post('_user_id', '');
        Logger::dayLog("api/crash", $user_id, $postData);
        exit($this->returnBack('0000'));
    }
}
