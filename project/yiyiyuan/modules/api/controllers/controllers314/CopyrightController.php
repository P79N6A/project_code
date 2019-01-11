<?php
namespace app\modules\api\controllers\controllers314;

use app\modules\api\common\ApiController;
use Yii;

class CopyrightController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $array['copyright'] = 'Copyright©小小黛朵（北京）科技有限公司';
        exit($this->returnBack('0000', $array));
    }

}
