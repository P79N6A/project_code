<?php
namespace app\modules\api\controllers\controllers310;

use Yii;
use app\modules\api\common\ApiController;

class GeturlController extends ApiController{

	public $enableCsrfValidation = false;

    public function actionIndex() {
        $array['data'] = 1;//0:预生产，1：生产
        $array = $this->returnBack('0000');
        echo $array;
        exit;
    }

}